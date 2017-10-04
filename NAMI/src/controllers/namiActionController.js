
var router = require('express').Router();
var namiManager = namiRequire('NamiManager');

var originateAllower = (function() {
    // todo - Virer tout ca et fix côté php en différenciant les peerStatus qui sont jugés equivalents atm

    // Si on garde cette solution de merde, faire un array pour chaque confId_peerId
    // Surtout rester rapide pas plus de 10ms d'execution

    var lastOriginate = new Date() / 1000;

    return function () {
        var oldOriginateTime = lastOriginate;
        lastOriginate = new Date() / 1000;

        // 60ms < mtn - ancienOriginate
        //return 60 < lastOriginate - oldOriginateTime;

        // Bon enfait ca return des secondes pas de millisecondes.
        // On va faire avec temps écoulé > à 200ms
        return 0.2 < lastOriginate - oldOriginateTime;
    };
})();

router.post('/sip/reload', function (req, res, next) {
    namiManager.sipReload(function (response) {
        res.send(response);
    });
});

router.post('/originate/:conferenceId/:peerId', function (req, res, next) {
    if (! originateAllower()) {
        //console.log('Cant');
        return res.send({
            ok: true,
            canceled: true
        });
    }
    //console.log('Can');

    namiManager.originateCall(
        req.params.conferenceId,
        req.params.peerId,
        function (response) {
            res.send(response);
        }
    );
});

router.post('/conference/:conferenceId/kick/:channelId', function (req, res, next) {
    namiManager.kickFromConference(
        req.params.conferenceId,
        decodeURIComponent(req.params.channelId),
        function (response) {
            res.send(response);
            //console.log('Pour kick de channel', decodeURIComponent(req.params.channelId), 'ca renvoie', response);
        }
    );
});

module.exports = router;