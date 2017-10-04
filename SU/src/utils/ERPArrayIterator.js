module.exports = function (item) {
    var index = 0;
    var list = item;

    return {
        first: function () {
            this.reset();
            return this.current();
        },
        current: function() {
            return list[index];
        },
        next: function () {
            return list[++index];
        },
        hasNext: function () {
            return index < list.length;
        },
        reset: function () {
            index = 0;
            return this;
        },
        get: function (key, defaultValue) {
            if (list[key] !== undefined) {
                return list[key];
            } else {
                if (defaultValue !== undefined)
                    return defaultValue;
                else
                    throw Error("Key " + key + " n'existe pas");
            }
        },
        each: function (callback) {
            for (var item = this.first(); this.hasNext(); item = this.next()) {
                if (callback(index, item) === false)
                    return this;
            }
            return this;
        },
        hasValue: function (value) {
            /*
             if (typeof val === 'object') {
             // Solution pour des objets simples, avec les keys dans le meme ordres
             // http://stackoverflow.com/questions/1068834/object-comparison-in-javascript
             var found = false;

             this.each(function (index, value) {
             if (JSON.stringify(value) === JSON.stringify(val))
             found = true;
             });
             }
             */
            var found = false;
            var toFind = typeof value === 'object' ? JSON.stringify(value) : value;

            this.each(function (idx, val) {
                if (found)
                    return;
                if (typeof value === 'object') {
                    if (JSON.stringify(val) === toFind)
                        found = true;
                    return;
                }
                if (toFind === val)
                    found = true;
            });

            return found;
        },
        pushObjIfDoesntExists: function (obj) {
            if (! this.hasValue(obj)) {
                list.push(obj);
            }

            return this;
        },
        getArray: function () {
            return list;
        },
        setValue: function (key, value) {
            list[key] = value;
            return this;
        },
        push: function (val) {
            list.push(val);
            return this;
        },
        spliceIndex: function (key) {
            list.splice(key, 1);
            return this;
        },
        getLast: function () {
            return list.length ? list[list.length -1] : null;
        },
        removeValueObject: function (object) {
            var foundIndex = -1;

            this.each(function (idx, val) {
                if (foundIndex > -1)
                    return;
                if (JSON.stringify(val) === JSON.stringify(object))
                    foundIndex = idx;
            });

            this.spliceIndex(foundIndex);
            return this;
        }
    };
};