"use strict";

var h = {
    makeArray:function (collection) {
        return Array.prototype.slice.call(collection);
    },

    extract: function (arr) {
        var result={};
        var self=this;

        self.forIn(arr, function (arr1) {
            self.forIn(arr1, function (item, key) {
                result[key] = item;
            });
        });
        
        return result;
    },

    showObj: function (obj, key) {
        var self=this;
        
        var echo =function(obj) {
            self.forIn(obj, function (item, key) {
                document.write(key+": "+item+"<br>");
            });

            document.write('<hr>');
        }

        echo.call(null, ((!key) ? obj : (function () {
            var result = {};

            self.forEach(Object.getOwnPropertyNames(obj), function (key) {
                result[key] = obj[key];
            });

            return result;
        }).call()));
    },

    summObjects: function () {
        var self = this;
        var result = {};

        self.forEach(arguments, function (arr) {
            self.forIn(arr, function (item, key) {
                if (result[key] === undefined) result[key] = item;
            });
        });

        return result;
    },

    forEach: function (arr, callBack) {
        Array.prototype.forEach.call(arr, callBack);
    },

    forIn: function (obj, callBack) {
        for (var key in obj) {
            callBack(obj[key], key, obj);
        }
    },

    createClass: function (data) {
        var parent = data.parent || function () { };
        var constructor = (Object.getOwnPropertyNames(data).indexOf('constructor') != -1) ? data.constructor : parent;
        var staticProperties = data.static || {};
        var parentPrototype = (Object.getOwnPropertyNames(data).indexOf('prototype') != -1) ? data.prototype : parent.prototype;

        constructor.prototype = Object.create(parentPrototype);

        ['prototype', 'constructor', 'parent', 'static'].forEach(function (key) {
            delete data[key];
        });

        this.forIn(data, function (property, key) {
            constructor.prototype[key] = property;
        });

        this.forIn(this.summObjects(staticProperties, parent), function (property, key) {
            constructor[key] = property;
        });

        this.forIn({
            constructor: constructor,
            _parent: parent,
            _extends: function (context, _arguments) { this._parent.apply(context, _arguments); },
        },
        function (property, key) {
            Object.defineProperty(constructor.prototype, key, { enumerable: false, value: property });
        });

        this.forIn(data, function (property, key) {
            constructor.prototype[key] = property;
        });

        return constructor;
    },

    createObj: function (data, _arguments) {
        return this.newObjByApply(this.createClass(data), _arguments);
    },
    
    newObjByApply: function (constructor, _arguments) {
                var _arguments = _arguments || [];

                               var f = function () { };
            f.prototype = constructor.prototype;
            var obj = new f();
                                        Object.defineProperty(obj, 'constructor', {enumerable: false, value: constructor});
                                    constructor.apply(obj, _arguments);

                                    return obj;
            },

};