

module.exports = function (app, routePrefixes) {

    var files = require('fs').readdirSync('src/controllers');

    for (var ite in files) {
        // useless ici mais webstorm râle
        if (files.hasOwnProperty(ite) && files[ite] !== '.svn') {
            var ctrlName = files[ite].replace(/[\.].*$/, '');
            var ctrlExported = controllersRequire(ctrlName);

            if (typeof routePrefixes[ctrlName] === 'undefined') {
                throw Error('Le prefix de route pour ' + ctrlName +
                    'n\'est pas configuré dans app.js');
            }
            
            app.use(
                routePrefixes[ctrlName],
                ctrlExported
            );
        }
    }
};