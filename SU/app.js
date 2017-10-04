var express = require('express');
var logger = require('morgan');
var bodyParser = require('body-parser');

var app = express();

app.use(logger('dev'));
app.use(bodyParser.json());
app.use(bodyParser.urlencoded({ extended: false }));

var devin = false;
for (i = 0; i < process.argv.length; i++) {
    if (process.argv[i] === 'dev')
        devin = 'dev';
}

global.mode = devin || 'prod';

console.log('Mode:', global.mode);

global.utilsRequire = function (path) {
    return require(__dirname + '/src/utils/' + path);
};

global.controllersRequire = function (path) {
    return require(__dirname + '/src/controllers/' + path);
};

global.conferencesRequire = function (path) {
    return require(__dirname + '/src/conferences/' + path);
};

global.testsRequire = function (name) {
    return require(__dirname + '/src/tests/' + name + 'Test');
};

utilsRequire('routesBuilder')(app, {
    conferenceController: '/conferences'
});

app.post('/', function (req, res) {
    res.send({
        ok: true
    });
});

var winston = require('winston');
winston.add(winston.transports.File, {filename: 'SU.log'});

// { emerg: 0, alert: 1, crit: 2, error: 3, warning: 4, notice: 5, info: 6, debug: 7 }

conferencesRequire('conferencesPersister').registerAsteriskDirPath(
    //global.mode === 'prod' ? '/etc/asterisk' : __dirname
    '/etc/asterisk'
);

//testsRequire('superFileIOStreamPromiser');
//testsRequire('conferenceContainer');
//testsRequire('conferencePersister');



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
