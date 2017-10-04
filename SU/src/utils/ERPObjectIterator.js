
var IteratorObj = function(Obj) {
    var data = Obj,
        keys = Object.keys(data),
        index = 0,
        length = keys.length,
        customErr = '';

    return {
        first: function () { //
            this.reset();
            return this.next();
        },
        next: function() { //
            var element;
            if (!this.hasNext()) {
                return null;
            }
            element = data[keys[index]];
            index++;
            return element;
        },
        hasNext: function() { //
            return index <= length;
        },
        reset: function() { //
            index = 0;
        },
        getValue: function (key, defaultValue) {
            if (data[key] !== undefined) {
                return data[key];
            } else {
                if (defaultValue !== undefined)
                    return defaultValue;
                else
                    throw Error(customErr + "Key " + key + " doesn't exist");
            }
        },
        getOrCreate: function (key, create) {
            if ('undefined' === typeof data[key]) {
                data[key] = create;
            }

            return data[key];
        },
        getNestedValue: function (keysASC) {
            var _data = data;
            var ite = -1;

            while (++ite < keysASC.length) {
                _data = _data[keysASC[ite]];
                if ('undefined' === typeof _data) {
                    throw Error(customErr + keysASC[ite] + ' n\'existe pas (depth ' + ite + ')');
                }
            }

            return _data;
        },
        setNestedValue: function (firstKey, secondKey, value, createLasKey_opt, onlySetWhenUndefined_opt) {
            if (typeof data[firstKey] === 'undefined')
                throw Error(customErr + 'Première key (' + firstKey + ') n\'existe pas');

            if (typeof data[firstKey][secondKey] === 'undefined') {
                if (createLasKey_opt !== true)
                    throw Error(customErr + 'Seconde key (' + secondKey + ') n\'existe pas');
            } else if (onlySetWhenUndefined_opt)
                return;

            data[firstKey][secondKey] = value;
        },
        hasKey: function (key) {
            return typeof data[key] !== 'undefined';
        },
        setValue: function (key, value, onlySetWhenUndefined_opt) {
            if (typeof data[key] === 'undefined') {
                keys.push(key);
            } else if (onlySetWhenUndefined_opt)
                return;

            data[key] = value;
        },
        current: function() { //
            return data[keys[index]];
        },
        each: function (callback) {
            /* Au cas où on remove tout ce qui est index de keys
             for (var item = this.first(); this.hasNext(); item = this.next()) {
             callback(item);
             }
             */
            for (var ite in data) {
                if (data.hasOwnProperty(ite)) {
                    callback(ite, data[ite]);
                }
            }
        },
        display: function (intro) {
            console.log(typeof intro !== 'undefined' ? intro : '',
                JSON.stringify(data, null, 4));
        },
        getObj: function () {
            return data;
        },
        setCustomErr: function (err) {
            customErr = err + ': ';
            return this;
        },
        resetCustomErr: function () {
            customErr = '';
            return this;
        },
        unset: function (key) {
            if (! this.hasKey(key)) {
                throw Error(customErr + 'Ca demande de unset une key non defined');
            }
            delete data[key];
        }
    }
};

module.exports = IteratorObj;