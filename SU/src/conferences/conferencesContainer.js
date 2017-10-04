var ArrayIterator = utilsRequire('ERPArrayIterator');
var winston = require('winston');

/**
 * [ 
 *     {
 *          id: 123,
 *          users: [
 *              {id: 123, secret: 123}
 *          ]
 *     }
 * ]
 * 
 * @type {Function}
 */
module.exports = (function () {
    var container = new ArrayIterator([]);

    function getPosAndConference(conferenceId) {
        var conf = [-1, null];

        container.each(function (index, value) {
            if (value.id === conferenceId)
                conf = [index, value];
        });

        return conf;
    }

    function createConference(conferenceId) {
        container.push({
            id: conferenceId,
            isRegistered: false,
            users: []
        });

        return container.getLast();
    }

    function safelyAddUsersToConf(conference, users) {
        if (typeof users[0] === 'undefined') {
            users = [users];
        }
        
        new ArrayIterator(users).each(function (index, user) {
            if (! conferenceHasUser(conference, user)) {
                conference.users.push(user);
                return true;
            }
            winston.log('warn', '[safelyAddUsersToConf]: User ' + user.id + ' deja dans conference (no ' + conference.conf + ')');
        });
    }

    function conferenceHasUser(conference, user) {
        var has = false;
        
        new ArrayIterator(conference.users).each(function (index, val) {
            if (val.id === user.id) {
                has = true;
                return false;
            }
        });
        
        return has;
    }
    
    function removeConference(conferenceId) {
        var conf = getPosAndConference(conferenceId);
        
        if (conf[1]) {
            container.spliceIndex(conf[0]);
            return;
        }
        
        winston.log('warn', '[removeConference] : Demande de rm une conf non existante');
    }

    return {
        safelyPushConfUsers: function (conferenceId, users) {
            safelyAddUsersToConf(
                getPosAndConference(conferenceId)[1] || createConference(conferenceId),
                users
            );
            return this;
        },
        removeConference: function (conferenceId) {
            removeConference(conferenceId);
            return this;
        },
        getConference: function (conferenceId) {
            return getPosAndConference(conferenceId)[1];
        },
        debug: function () {
            console.log(JSON.stringify(container.getArray(), null ,4));
        }
    };
})();