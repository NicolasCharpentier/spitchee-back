
var router = require('express').Router();

router.get('/', function (req, res, next) {
    res.send('okok'); 
});

module.exports = router;