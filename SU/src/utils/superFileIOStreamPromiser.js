var fs = require('fs');
var Q = require('q');
var winston = require('winston');

module.exports = (function () {
    var files = {};
    
    function operate(path) {
        //files[path].isOpened = true;
        
        var action = files[path].queue.shift();
        
        if (action === undefined) {
            files[path].isOpened = false;
            return;
        }
        
        if (action.operation === 'appendLine') {
            operateAppendLine(path, action);
        } else if (action.operation === 'removeLine') {
            operateRemoveLine(path, action);
        } else if (action.operation === 'write') {
            operateWrite(path, action);
        } else if (action.operation === 'delete') {
            operateDelete(path, action);
            if (files[path].queue.length) {
                winston.log('warn', 'Ca a del fichier avec d\'autres actions après [' + path + ']');
            }
            files[path].queue = [];
        } else {
            throw Error('Operation inconnue: ' + action.operation);
        }
    }
    
    function operateAppendLine(path, action) {
        if (action.data[action.data.length] != '\n') {
            action.data += '\n';
        }
        
        console.log('Ca va append');
        
        fs.appendFile(path, action.data, function (err) {
            if (err)
                action.promise.reject(err);
            else
                action.promise.resolve('OK');

            operate(path);
        });
    }

    function operateRemoveLine(path, action) {
        fs.readFile(path, function (err, data) {
            if (err) {
                action.promise.reject(err);
                operate(path);
                return;
            }
            
            data = data.toString();
            
            var pos = data.indexOf(action.data + '\n');
            if (pos > -1) {
                data = data.substring(0, pos) + data.substring(pos + action.data.length + 1);
                
                fs.writeFile(path, data, function (err) {
                    if (err)
                        action.promise.reject(err);
                    else
                        action.promise.resolve('removePart ok');

                    operate(path);
                });
            } else {
                action.promise.reject('Err: toRm pas trouvé');
                operate(path);
            }
        });
    }

    function operateWrite(path, action) {
        fs.writeFile(path, action.data, function (err) {
            if (err)
                action.promise.reject(err);
            else
                action.promise.resolve('write ok');

            operate(path);
        });
    }

    function operateDelete(path, action) {
        fs.exists(path, function (exists) {
            if (! exists) {
                winston.log('warn', 'Demande de RM un fichier non existant [' + path + ']');
                action.promise.resolve('rm half ok');
                operate(path);
                return;
            }
            fs.unlink(path, function (err) {
                if (err)
                    action.promise.reject('rm failed');
                else
                    action.promise.resolve('rm ok');

                operate(path);
            });
        });
    }
    
    return {
        queue: function (path, operation, data) {
            var defered = Q.defer();
            
            if ('undefined' === typeof files[path]) {
                files[path] = {
                    isOpened: false,
                    queue: []
                }
            }
            
            files[path].queue.push({
                operation: operation,
                data: data,
                promise: defered
            });
            
            if (! files[path].isOpened) {
                files[path].isOpened = true;
                //process.nextTick(function () {
                    operate(path); 
                //});
            }
            
            return defered.promise;
        },
        topOfTheWorld: function (backWhenGucciWasTheShitToRock) {
            var wallah = Q.defer();
            fs.mkdir(backWhenGucciWasTheShitToRock, function (err) {
                if (err)
                    wallah.reject(err);
                else
                    wallah.resolve('oklm');
            });
            return wallah.promise;
        },
        debug: function () {
            console.log(JSON.stringify(files, null, 4));
        }
    }
})();