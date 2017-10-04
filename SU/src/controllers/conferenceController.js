
// todo rename conf controller

var router = require('express').Router();
var confPersister = conferencesRequire('conferencesPersister');
var confContainer = conferencesRequire('conferencesContainer');

router.post('/add', function (req, res, next) {
    confContainer.safelyPushConfUsers(req.body.conferenceId, req.body.user);
    
    confPersister.persist(
        confContainer.getConference(req.body.conferenceId)
    ).then(function (resp) {
        res.send({
            ok: true
        });
    }).catch(function (err) {
        res.send({
            ok: false
        });
    });
});

router.post('/remove', function (req, res, next) {
    
});

router.get('/', function (req, res, next) {
    return res.send('oklm');
});

module.exports = router;