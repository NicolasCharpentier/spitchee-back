
var SFIOSP = utilsRequire('superFileIOStreamPromiser');
var ArrayIterator = utilsRequire('ERPArrayIterator');
var sipConfPath = null;
var dynamicConfsDirPath = null;
var dynamicConfsPrePath = 'dynamic_sip';
var Q = require('q');
var winston = require('winston');

module.exports = (function () {
    var files = {};

    function writeConf(conference) {
        var deferred = Q.defer();
        
        Q(conference.isRegistered ? 42 : registerConf(conference))
            .then(function (val) {
                conference.isRegistered = true;
                
                SFIOSP.queue(
                    getConfPath(conference.id),
                    'write',
                    getSipUsersDefinition(conference.users)
                ).then(function (response) {
                    deferred.resolve(conference.id + ':' + val + ' | ' + response);
                }).catch(function (err) {
                    deferred.reject(conference.id + ':' + err);
                });
            }).catch(function (err) {
                deferred.reject(conference.id + ':' + err);
            });
        
        return deferred.promise;
    }

    function registerConf(conference) {
        return SFIOSP.queue(sipConfPath, 'appendLine', getConfIncludeLine(conference.id));
    }

    function removeConf(conference) {
        var deferred = Q.defer();

        if (! conference.isRegistered) {
            winston.log('warn', 'Demande de remove une conf non registered' + conference.id);
            deferred.resolve(conference.id + ': remove semi ok (non existant)');
            return deferred.promise;
        }
        
        SFIOSP.queue(sipConfPath, 'removeLine', getConfIncludeLine(conference.id))
            .then(function (ok) {
                  SFIOSP.queue(getConfPath(conference.id), 'delete')
                      .then(function (ok) {
                          conference.isRegistered = false;
                          deferred.resolve(conference.id + ':' + ok);
                      })
                      .catch(function (err) {
                          deferred.reject(conference.id + ':' + err);
                      });
            }).catch(function (err) {
                deferred.reject(conference.id + ':' + err);
            });
        
        return deferred.promise;
    }
    
    function getConfFileName(confId) {
        return 'conf_' + confId + '.conf'; 
    }
    
    function getConfPath(confId) {
        return dynamicConfsDirPath + '/' + getConfFileName(confId);
    }
    
    function getConfIncludeLine(confId) {
        return '#include "' + dynamicConfsPrePath + '/' + getConfFileName(confId) + '"';
    }

    function getSipUsersDefinition(users) {
        var definition = '';

        new ArrayIterator(users).each(function (osef, user) {
            definition += getSipUserDefinition(user);
        });

        return definition;
    }

    function getSipUserDefinition(user) {
        return '[' + user.id + ']\n' +
            'type=friend\n' +
            'secret=' + user.secret + '\n' +
            'username=' + user.id + '\n' +
            'dtmfmode=rfc2833\n' +
            'callerid="Temp user ' + user.id + '" <' + user.id + '>\n' +
            'host=dynamic\n' +
            'canreinvite=no\n' +
            'context=myphones\n' +
            'nat=yes\n' +
            'qualify=yes\n\n'
    }
    


    return {
        registerAsteriskDirPath: function (path) {
            sipConfPath = path + '/' + 'sip.conf';
            dynamicConfsDirPath = path + '/' + dynamicConfsPrePath;
            SFIOSP.topOfTheWorld(dynamicConfsDirPath).catch(function (err) {
                if (err.code != -17 && err.code != 'EEXIST') { // -17 existe deja
                    winston.log('error', 'Problème création sipConfDynDir [' + dynamicConfsDirPath + '] : ' + err);
                }
            });
        },
        persist: function (conf) {
            return writeConf(conf);
        },
        remove: function (conf) {
            return removeConf(conf);
        },
        test: function () {
            console.log('sipConfPath            ', sipConfPath);
            console.log('dynamicConfsDirPath    ', dynamicConfsDirPath);
            console.log('dynamicCOnfsPrePath    ', dynamicConfsPrePath, '\n');
            console.log('Filename               ', getConfFileName(123));
            console.log('IncludeLine            ', getConfIncludeLine(123));
            console.log('ConfPath               ', getConfPath(123));
        }
    }
})(); // todo le asterisk dir path ici