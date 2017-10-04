var express = require('express');
var logger = require('morgan');

var app = express();
app.set('view engine', null);
app.use(logger('dev'));

var devin = false;
for (i = 0; i < process.argv.length; i++) {
    if (process.argv[i] === 'dev')
        devin = 'dev';
}

global.mode = devin || 'prod';

console.log('Mode', global.mode);

global.config = (require('js-yaml').load(
    require('fs').readFileSync(__dirname + '/config/config.' + global.mode + '.yaml'))
);

global.utilsRequire = function (path) {
    return require(__dirname + '/src/utils/' + path);
};

global.controllersRequire = function (path) {
    return require(__dirname + '/src/controllers/' + path);
};

global.namiRequire = function (path) {
    return require(__dirname + '/src/nami/' + path);
};

utilsRequire('routesBuilder')(app, {
    testController: '/test',
    namiActionController: '/nami/action'
});

/*
(namiRequire('NamiManager')).sipReload(function (dac) {
    console.log('First', dac.ok);
});

setTimeout(function () {
  (namiRequire('NamiManager')).sipReload(function (dac) {
    console.log('Second', dac.ok);
  });
}, 5000);
*/

//new namiRequire('NamiManager').test();

/*
nami.send(namiLib.Actions.Status(), function (response) {
    console.log('RESP' + JSON.stringify(response, null ,4 ));
});
*/

/*
function standardSend(action) {
  nami.send(action, function (response) {
    console.log(' ---- Response: ' + util.inspect(response));
  });
}

nami.on('namiConnected', function (event) {
  action = new namiLib.Actions.Command();
  action.command = "sip reload";
  standardSend(action);  
});

nami.on('namiEventPeerStatus', function (event) {
  console.log('------- Peer Status' + JSON.stringify(event, null, 4));
});
*/

/*
 var log4js = require('log4js');
 log4js.configure({
 appenders: [
 //{ type: 'console' },
 { type: 'file', filename: 'logs/cheese.log', category: 'cheese' }
 ]
 });
*/


// catch 404 and forward to error handler
app.use(function(req, res, next) {
  var err = new Error('Not Found');
  err.status = 404;
  next(err);
});

// error handlers

// development error handler
// will print stacktrace
if (app.get('env') === 'development') {
  app.use(function(err, req, res, next) {
    res.status(err.status || 500);
      res.send(JSON.stringify(err, null, 4));
  });
}

// production error handler
// no stacktraces leaked to user
app.use(function(err, req, res, next) {
  res.status(err.status || 500);
    res.send(JSON.stringify(err, null, 4));
});


module.exports = app;
