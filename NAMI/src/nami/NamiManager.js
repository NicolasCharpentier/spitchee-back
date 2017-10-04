var namiLib = require('nami');
var request = require('request');

/*
var config = {
    host: global.mode === 'prod' ? '127.0.0.1' : '192.168.100.9',
    port: 5038,
    username: 'admin',
    secret: 'tlp'
};

var silexIp = global.mode === 'prod' ? '127.0.0.1' : '127.0.0.1:8000';
*/

var namiInstance = new (namiLib.Nami)(global.config.nami.asterisk);
//namiInstance.logger = {debug: function () {}, info: function (info) { console.info(info); }};

if (1 != global.config.nami.logDebug) {
    namiInstance.logger.debug = function () {};
}

function eventProcessing(event) {
    delete event.lines;
    delete event.EOL;
    
    //if (event.event === 'ConfbridgeJoin') {
    //    console.log('Ca va post', event);
    //}

    request.post('http://' + global.config.silex + '/api/internal/nami/event', {
        json: event
    }, function (err, resp, body) {
        if (null !== err) {
            console.log('Erreur au posting d\'event', err, event);
        } else {
            console.log('POST ' + event.event + ' > ' + resp.statusCode);
        }
    });
}

/*
    RAPPEL : D'un coté on a le processor d'event qui va les post sur silex
             D'un autre, on a des methodes pour les actions, qui sont vérifiées comme elles le définissent
                        faut leur donner une cb qui est enfait l'envoi de réponse à silex
                        C'est dans cette cb qu'on definit si on prends juste le field ajouté .ok qui definit si c valide
                        ou qu'on donne toute la rep
 */

module.exports = (function () {
    process.on('SIGINT', function () {
        namiInstance.close();
        process.exit();
    });

    namiInstance.open();

    namiInstance.on('namiEvent', function (e) {
        eventProcessing(e);
    });

    namiInstance.on('namiConnected', function () {
        console.log('Connection successplein');
    });

    function sendAction(action, callback, verify, type) {
        if (! namiInstance.connected) {
            namiInstance.on('namiConnected', function () {
                sendAction(action, callback, verify);
            });
        }

        try {
            namiInstance.send(action, function (response) {
                response.ok = typeof verify === 'function' ? verify(response) : verify;

                console.log('ACTION ' + type + ' > ' + (response.ok ? 'OK' : 'KO'));

                delete response.lines;
                delete response.EOL;

                callback(response);
            });
        } catch (error) {
            callback({
                ok: false,
                err: error
            });
        }
    }

    function sipReload(callback) {
        var action = new namiLib.Actions.Command();
        action.command = "sip reload";

        sendAction(action, callback, function (response) {
            return response.response === 'Follows';
        }, 'sipReload');
    }
    
    function originateCall(confId, peerId, callback) {
        var action = new namiLib.Actions.Originate();
        action.channel = 'SIP/' + peerId;
        action.data = confId;
        action.timeout = 30000;
        action.callerID = 'Metisse\'s king';
        action.application = 'ConfBridge';
        action.async = true;

        sendAction(action, callback, function (response) {
            return response.response === 'Success';
        }, 'originateCall');
    }

    function kickFromConference(confId, channelId, callback) {
        var action = new namiLib.Actions.ConfbridgeKick();
        action.Channel = channelId;
        action.Conference = confId;
        
        sendAction(action, callback, function (response) {
            return response.response === 'Success';
        }, 'kickFromConference');
    }
    
    // La meme que kick pour mute, avec ConfbridgeMute()


    return {
        sipReload: sipReload,
        originateCall: originateCall,
        kickFromConference: kickFromConference
    };
})();