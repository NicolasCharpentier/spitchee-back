module.exports = (function () {

    /*
     var confPersister = conferencesRequire('conferencesPersister');
     confPersister.registerAsteriskDirPath(__dirname);
     confPersister.persist(confContainer.getConference('123')).then(function (m) {
     console.log('PERSISTED DANS MAIN', m);
     confContainer.debug();
     }).catch(function (err) {
     console.error(err);
     });
     */

    /* LAST TESTS

     var confPersister = conferencesRequire('conferencesPersister');
     confPersister.registerAsteriskDirPath(__dirname);
     //confPersister.test();
     confPersister.persist(
     confContainer.getConference('123')
     ).then(function (ok) {
     console.log('Success persist: ', ok);
     confContainer.debug();
     }).catch(function (err) {
     console.log('Err persist:', err);
     });

     setTimeout(function () {
     confPersister.remove(confContainer.getConference('123'))
     .then(function (val) {
     console.log('SUccess rm: ', val);
     confContainer.debug();
     }).catch(function (err) {
     console.log('Fail rm:', err);
     });
     }, 1000);
     */

    console.log('Tests non codés pour le persister mais mqt');
})();