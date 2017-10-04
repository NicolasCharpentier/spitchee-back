
module.exports = (function () {
    var confA = {
        id: 'confA',
        users: [{
            id: '123',
            secret: 'mdp'
        }, {
            id: '124',
            secret: 'mdp'
        }]
    };

    // Création/Pushage
    var confContainer = conferencesRequire('conferencesContainer')
        .safelyPushConfUsers(confA.id, confA.users);
    var cloneConfA = confContainer.getConference(confA.id);

    if (confA.id === cloneConfA.id &&
        JSON.stringify(confA.users) === JSON.stringify(cloneConfA.users)) {
        console.log('Succès conference container test CREATION');
    } else {
        console.log('Echec conference container test CREATION.');
        console.log('Pour data donnée', confA, '\n', 'On recoit', cloneConfA);
    }


    // Removation/Deletage
    confContainer.removeConference(confA.id);
    cloneConfA = confContainer.getConference(confA.id);

    if (cloneConfA === null) {
        console.log('Succès confernnce container test DELETATION');
    } else {
        console.log('Echec conference container test DELETATION');
        console.log('Conference supposée delete, mais on recoit', cloneConfA);
    }
})();