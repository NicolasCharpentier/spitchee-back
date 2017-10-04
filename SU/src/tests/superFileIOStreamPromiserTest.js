module.exports = (function (params) {

    var SFIOSP = utilsRequire('superFileIOStreamPromiser');
    var nbOperations = 0;

    (function () {
        var baseParams = {
            appendLine: true,
            removeLine: true,
            write: true,
            delete: true,
            file: __dirname + '/SFIOSP_test_result.point'
        };

        if (! params) {
            params = baseParams;
            nbOperations = 4;
            return;
        }

        (new IteratorObj(baseParams)).each(function (key, value) {
            if (undefined === params[key]) {
                params[key] = value;
            }
            if (! value && key !== 'file') {
                nbOperations++;
            }
        });
    })();


    if (params.appendLine) {
        SFIOSP.queue(params.file, 'appendLine', 'Ligne appendée')
            .then(function (response) {
                console.log('Success: ', response);
            })
            .catch(function (err) {
                console.log('Err: ', err);
            });
    }

    if (params.removeLine) {
        SFIOSP.queue(params.file, 'removeLine', 'Ligne appendée')
            .then(function (response) {
                console.log('Success: ', response);
            })
            .catch(function (err) {
                console.log('Err: ', err);
            });
    }

    if (params.write) {
        SFIOSP.queue(params.file, 'write', 'Contenu')
            .then(function (response) {
                console.log('Success: ', response);
            })
            .catch(function (err) {
                console.log('Err: ', err);
            });
    }

    if (params.delete) {
        SFIOSP.queue(params.file, 'delete')
            .then(function (response) {
                console.log('Success: ', response);
            })
            .catch(function (err) {
                console.log('Err: ', err);
            });
    }

    console.log('Test SFIOP en cours. Attendre l\'output des ' + nbOperations +
        ' opérations asyncs puis check l\'etat/presence de "' + params.file +
        '" pcque flemme de coder cette partie');

})();


/* Olds tests : successfull
 for (var i = 0; i < 11; i++) {
 setTimeout(function (i) {
 SFIOSP.queue(__dirname + '/sip.conf',
 'appendLine',
 //'#include "dynamic_sip/conf_' + i, i)
 'A' + i)

 .then(function (response) {
 console.log('Success: ', response);
 })
 .catch(function (err) {
 console.log('Err: ', err);
 });
 //console.log('Fin.' + i);
 }, 1000 + (i * 10), i);
 }

 for (i = 0; i < 10; i++) {
 setTimeout(function (i) {
 SFIOSP.queue(__dirname + '/sip.conf',
 'removeLine',
 //'#include "dynamic_sip/conf_' + i, i)
 'A' + i)

 .then(function (response) {
 console.log('Success rm: ', response);
 })
 .catch(function (err) {
 console.log('Err: ', err);
 });
 console.log('Fin.' + i);
 }, 1000 + (i * 10), i);
 }

 SFIOSP.queue(__dirname + '/sip.conf', 'write', 'okkkkptdrfdp')
 .then(function (response) {
 console.log('SUccess write: ', response);
 })
 .catch(function (err) {
 console.log('Err write: ', err);
 });

 setTimeout(function () {
 SFIOSP.queue(__dirname + '/sip.conf', 'delete')
 .then(function (resp) {
 console.log('Success del: ', resp);
 })
 .catch(function (err) {
 console.log('err del:', err);
 });
 }, 2000);
 */