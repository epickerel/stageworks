/*
 * MooTools Core library heavily modified to rename all functions not to clash
 * with jQuery, Prototype/Scriptaculous or any other javascript framework.
 * 
 * Also unneeded functions not invovled in the code paths for the carousel have
 * been removed.
 *
 * Tested only for the code paths involved in the carousel's usage of MooTools.
 */

/*
Script: Core.js
        MooTools - My Object Oriented JavaScript Tools.

License:
        MIT-style license.

Copyright:
        Copyright (c) 2006-2008 [Valerio Proietti](http://mad4milk.net/).

Code & Documentation:
        [The MooTools production team](http://mootools.net/developers/).

Inspiration:
        - Class implementation inspired by [Base.js](http://dean.edwards.name/weblog/2006/03/base/) Copyright (c) 2006 Dean Edwards, [GNU Lesser General Public License](http://opensource.org/licenses/lgpl-license.php)
        - Some functionality inspired by [Prototype.js](http://prototypejs.org) Copyright (c) 2005-2007 Sam Stephenson, [MIT License](http://opensource.org/licenses/mit-license.php)
*/

var MooNative = function(options){
        options = options || {};
        var name = options.name;
        var legacy = options.legacy;
        var protect = options.protect;
        var methods = options.implement;
        var generics = options.generics;
        var initialize = options.initialize;
        var afterImplement = options.afterImplement || function(){};
        var object = initialize || legacy;
        generics = generics !== false;

        object.constructor = MooNative;
        object.$family = {name: 'native'};
        if (legacy && initialize) object.prototype = legacy.prototype;
        object.prototype.constructor = object;

        if (name){
                var family = name.toLowerCase();
                object.prototype.$family = {name: family};
                MooNative.typize(object, family);
        }

        var add = function(obj, name, method, force){
                if (!protect || force || !obj.prototype[name]) obj.prototype[name] = method;
                if (generics) MooNative.genericize(obj, name, protect);
                afterImplement.call(obj, name, method);
                return obj;
        };

        object.alias = function(a1, a2, a3){
                if (typeof a1 == 'string'){
                        if ((a1 = this.prototype[a1])) return add(this, a2, a1, a3);
                }
                for (var a in a1) this.alias(a, a1[a], a2);
                return this;
        };

        object.implement = function(a1, a2, a3){
                if (typeof a1 == 'string') return add(this, a1, a2, a3);
                for (var p in a1) add(this, p, a1[p], a2);
                return this;
        };

        if (methods) object.implement(methods);

        return object;
};

MooNative.genericize = function(object, property, check){
        if ((!check || !object[property]) && typeof object.prototype[property] == 'function') object[property] = function(){
                var args = Array.prototype.slice.call(arguments);
                return object.prototype[property].apply(args.shift(), args);
        };
};

MooNative.implement = function(objects, properties){
        for (var i = 0, l = objects.length; i < l; i++) objects[i].implement(properties);
};

MooNative.typize = function(object, family){
        if (!object.type) object.type = function(item){
                return ($mooType(item) === family);
        };
};

(function(){
        var natives = {'Array': Array, 'Date': Date, 'Function': Function, 'Number': Number, 'RegExp': RegExp, 'String': String};
        for (var n in natives) new MooNative({name: n, initialize: natives[n], protect: true});

        var types = {'boolean': Boolean, 'native': MooNative, 'object': Object};
        for (var t in types) MooNative.typize(types[t], t);

        var generics = {
                'Array': ["concat", "indexOf", "join", "lastIndexOf", "pop", "push", "reverse", "shift", "slice", "sort", "splice", "toString", "unshift", "valueOf"],
                'String': ["charAt", "charCodeAt", "concat", "indexOf", "lastIndexOf", "match", "replace", "search", "slice", "split", "substr", "substring", "toLowerCase", "toUpperCase", "valueOf"]
        };
        for (var g in generics){
                for (var i = generics[g].length; i--;) MooNative.genericize(window[g], generics[g][i], true);
        };
})();

var MooHash = new MooNative({

        name: 'Hash',

        initialize: function(object){
                if ($mooType(object) == 'hash') object = $mooUnlink(object.getClean());
                for (var key in object) this[key] = object[key];
                return this;
        }

});

MooHash.implement({

        mooForEach: function(fn, bind){
                for (var key in this){
                        if (this.hasOwnProperty(key)) fn.call(bind, this[key], key, this);
                }
        }

});

MooHash.alias('mooForEach', 'mooEach');

Array.implement({

        mooForEach: function(fn, bind){
                for (var i = 0, l = this.length; i < l; i++) fn.call(bind, this[i], i, this);
        }

});

Array.alias('mooForEach', 'mooEach');

function $mooA(iterable){
        if (iterable.item){
                var array = [];
                for (var i = 0, l = iterable.length; i < l; i++) array[i] = iterable[i];
                return array;
        }
        return Array.prototype.slice.call(iterable);
};

function $mooArguments(i){
        return function(){
                return arguments[i];
        };
};

function $mooChk(obj){
        return !!(obj || obj === 0);
};

function $mooClear(timer){
        clearTimeout(timer);
        clearInterval(timer);
        return null;
};

function $mooEmpty(){};

function $mooExtend(original, extended){
        for (var key in (extended || {})) original[key] = extended[key];
        return original;
};

function $mooLambda(value){
        return (typeof value == 'function') ? value : function(){
                return value;
        };
};

function $mooMerge(){
        var mix = {};
        for (var i = 0, l = arguments.length; i < l; i++){
                var object = arguments[i];
                if ($mooType(object) != 'object') continue;
                for (var key in object){
                        var op = object[key], mp = mix[key];
                        mix[key] = (mp && $mooType(op) == 'object' && $mooType(mp) == 'object') ? $mooMerge(mp, op) : $mooUnlink(op);
                }
        }
        return mix;
};

function $mooPick(){
        for (var i = 0, l = arguments.length; i < l; i++){
                if (arguments[i] != undefined) return arguments[i];
        }
        return null;
};

function $mooSplat(obj){
        var type = $mooType(obj);
        return (type) ? ((type != 'array' && type != 'arguments') ? [obj] : obj) : [];
};

var $mooTime = Date.now || function(){
        return +new Date;
};

function $mooType(obj){
        if (obj == undefined) return false;
        if (obj.$family) return (obj.$family.name == 'number' && !isFinite(obj)) ? false : obj.$family.name;
        if (obj.nodeName){
                switch (obj.nodeType){
                        case 1: return 'element';
                        case 3: return (/\S/).test(obj.nodeValue) ? 'textnode' : 'whitespace';
                }
        } else if (typeof obj.length == 'number'){
                if (obj.callee) return 'arguments';
                else if (obj.item) return 'collection';
        }
        return typeof obj;
};

function $mooUnlink(object){
        var unlinked;
        switch ($mooType(object)){
                case 'object':
                        unlinked = {};
                        for (var p in object) unlinked[p] = $mooUnlink(object[p]);
                break;
                case 'hash':
                        unlinked = new MooHash(object);
                break;
                case 'array':
                        unlinked = [];
                        for (var i = 0, l = object.length; i < l; i++) unlinked[i] = $mooUnlink(object[i]);
                break;
                default: return object;
        }
        return unlinked;
};

var Browser = $mooMerge({

        Engine: {name: 'unknown', version: 0},

        Platform: {name: (window.orientation != undefined) ? 'ipod' : (navigator.platform.match(/mac|win|linux/i) || ['other'])[0].toLowerCase()},

        Features: {xpath: !!(document.evaluate), air: !!(window.runtime), query: !!(document.querySelector)},

        Plugins: {},

        Engines: {

                presto: function(){
                        return (!window.opera) ? false : ((arguments.callee.caller) ? 960 : ((document.getElementsByClassName) ? 950 : 925));
                },

                trident: function(){
                        return (!window.ActiveXObject) ? false : ((window.XMLHttpRequest) ? 5 : 4);
                },

                webkit: function(){
                        return (navigator.taintEnabled) ? false : ((Browser.Features.xpath) ? ((Browser.Features.query) ? 525 : 420) : 419);
                },

                gecko: function(){
                        return (document.getBoxObjectFor == undefined) ? false : ((document.getElementsByClassName) ? 19 : 18);
                }

        }

}, Browser || {});

MooNative.UID = 1;

var $mooUid = (Browser.Engine.trident) ? function(item){
        return (item.uid || (item.uid = [MooNative.UID++]))[0];
} : function(item){
        return item.uid || (item.uid = MooNative.UID++);
};

var MooWindow = new MooNative({

        name: 'Window',

        legacy: (Browser.Engine.trident) ? null: window.Window,

        initialize: function(win){
                $mooUid(win);
                if (!win.Element){
                        win.Element = $mooEmpty;
                        if (Browser.Engine.webkit) win.document.createElement("iframe"); //fixes safari 2
                        win.Element.prototype = (Browser.Engine.webkit) ? window["[[DOMElement.prototype]]"] : {};
                }
                win.document.window = win;
                return $mooExtend(win, MooWindow.Prototype);
        },

        afterImplement: function(property, value){
                window[property] = MooWindow.Prototype[property] = value;
        }

});

MooWindow.Prototype = {$family: {name: 'window'}};

new MooWindow(window);

var MooDocument = new MooNative({

        name: 'Document',

        legacy: (Browser.Engine.trident) ? null: window.Document,

        initialize: function(doc){
                $mooUid(doc);
                doc.head = doc.getElementsByTagName('head')[0];
                doc.html = doc.getElementsByTagName('html')[0];
                if (Browser.Engine.trident && Browser.Engine.version <= 4) $try(function(){
                        doc.execCommand("BackgroundImageCache", false, true);
                });
                if (Browser.Engine.trident) doc.window.attachEvent('onunload', function() {
                        doc.window.detachEvent('onunload', arguments.callee);
                        doc.head = doc.html = doc.window = null;
                });
                return $mooExtend(doc, MooDocument.Prototype);
        },

        afterImplement: function(property, value){
                document[property] = MooDocument.Prototype[property] = value;
        }

});

MooDocument.Prototype = {$family: {name: 'document'}};

new MooDocument(document);

Array.implement({

        mooIndexOf: function(item, from){
                var len = this.length;
                for (var i = (from < 0) ? Math.max(0, len + from) : from || 0; i < len; i++){
                        if (this[i] === item) return i;
                }
                return -1;
        },

        mooMap: function(fn, bind){
                var results = [];
                for (var i = 0, l = this.length; i < l; i++) results[i] = fn.call(bind, this[i], i, this);
                return results;
        },

        mooAssociate: function(keys){
                var obj = {}, length = Math.min(this.length, keys.length);
                for (var i = 0; i < length; i++) obj[keys[i]] = this[i];
                return obj;
        },

        mooContains: function(item, from){
                return this.mooIndexOf(item, from) != -1;
        },

        mooExtend: function(array){
                for (var i = 0, j = array.length; i < j; i++) this.push(array[i]);
                return this;
        },


        mooInclude: function(item){
                if (!this.mooContains(item)) this.push(item);
                return this;
        },

        mooEmpty: function(){
                this.length = 0;
                return this;
        },

        mooFlatten: function(){
                var array = [];
                for (var i = 0, l = this.length; i < l; i++){
                        var type = $mooType(this[i]);
                        if (!type) continue;
                        array = array.concat((type == 'array' || type == 'collection' || type == 'arguments') ? Array.mooFlatten(this[i]) : this[i]);
                }
                return array;
        }

});

Function.implement({

        mooCreate: function(options){
                var self = this;
                options = options || {};
                return function(event){
                        var args = options.arguments;
                        args = (args != undefined) ? $mooSplat(args) : Array.slice(arguments, (options.event) ? 1 : 0);
                        if (options.event) args = [event || window.event].mooExtend(args);
                        var returns = function(){
                                return self.apply(options.bind || null, args);
                        };
                        if (options.delay) return setTimeout(returns, options.delay);
                        if (options.periodical) return setInterval(returns, options.periodical);
                        if (options.attempt) return $try(returns);
                        return returns();
                };
        },

        mooExtend: function(properties){
                for (var property in properties) this[property] = properties[property];
                return this;
        },

        mooRun: function(args, bind){
                return this.apply(bind, $mooSplat(args));
        },

        mooBind: function(bind, args){
                return this.mooCreate({bind: bind, arguments: args});
        },

        periodical: function(periodical, bind, args){
                return this.mooCreate({bind: bind, arguments: args, periodical: periodical})();
        }

});

Number.implement({

        mooLimit: function(min, max){
                return Math.min(max, Math.max(min, this));
        },

        mooRound: function(precision){
                precision = Math.pow(10, precision || 0);
                return Math.round(this * precision) / precision;
        },

        mooTimes: function(fn, bind){
                for (var i = 0; i < this; i++) fn.call(bind, i, this);
        },

        mooToFloat: function(){
                return parseFloat(this);
        },

        mooToInt: function(base){
                return parseInt(this, base || 10);
        }

});

Number.alias('mooTimes', 'mooEach');

(function(math){
        var methods = {};
        math.mooEach(function(name){
                if (!Number[name]) methods[name] = function(){
                        return Math[name].apply(null, [this].concat($mooA(arguments)));
                };
        });
        Number.implement(methods);
})(['abs', 'acos', 'asin', 'atan', 'atan2', 'ceil', 'cos', 'exp', 'floor', 'log', 'max', 'min', 'pow', 'sin', 'sqrt', 'tan']);

String.implement({

        mooTest: function(regex, params){
                return ((typeof regex == 'string') ? new RegExp(regex, params) : regex).test(this);
        },

        mooContains: function(string, separator){
                return (separator) ? (separator + this + separator).indexOf(separator + string + separator) > -1 : this.indexOf(string) > -1;
        },

        mooTrim: function(){
                return this.replace(/^\s+|\s+$/g, '');
        },

        mooClean: function(){
                return this.replace(/\s+/g, ' ').mooTrim();
        },

        mooCamelCase: function(){
                return this.replace(/-\D/g, function(match){
                        return match.charAt(1).toUpperCase();
                });
        },

        mooHyphenate: function(){
                return this.replace(/[A-Z]/g, function(match){
                        return ('-' + match.charAt(0).toLowerCase());
                });
        }

});

MooHash.implement({

        mooExtend: function(properties){
                MooHash.mooEach(properties, function(value, key){
                        MooHash.mooSet(this, key, value);
                }, this);
                return this;
        },

        mooGet: function(key){
                return (this.hasOwnProperty(key)) ? this[key] : null;
        },

        mooSet: function(key, value){
                if (!this[key] || this.hasOwnProperty(key)) this[key] = value;
                return this;
        }

});

var MooEvent = new MooNative({

        name: 'Event',

        initialize: function(event, win){
                win = win || window;
                var doc = win.document;
                event = event || win.event;
                if (event.$extended) return event;
                this.$extended = true;
                var type = event.type;
                var target = event.target || event.srcElement;
                while (target && target.nodeType == 3) target = target.parentNode;

                if (type.mooTest(/key/)){
                        var code = event.which || event.keyCode;
                        var key = MooEvent.mooKeys.keyOf(code);
                        if (type == 'keydown'){
                                var fKey = code - 111;
                                if (fKey > 0 && fKey < 13) key = 'f' + fKey;
                        }
                        key = key || String.fromCharCode(code).toLowerCase();
                } else if (type.match(/(click|mouse|menu)/i)){
                        doc = (!doc.compatMode || doc.compatMode == 'CSS1Compat') ? doc.html : doc.body;
                        var page = {
                                x: event.pageX || event.clientX + doc.scrollLeft,
                                y: event.pageY || event.clientY + doc.scrollTop
                        };
                        var client = {
                                x: (event.pageX) ? event.pageX - win.pageXOffset : event.clientX,
                                y: (event.pageY) ? event.pageY - win.pageYOffset : event.clientY
                        };
                        if (type.match(/DOMMouseScroll|mousewheel/)){
                                var wheel = (event.wheelDelta) ? event.wheelDelta / 120 : -(event.detail || 0) / 3;
                        }
                        var rightClick = (event.which == 3) || (event.button == 2);
                        var related = null;
                        if (type.match(/over|out/)){
                                switch (type){
                                        case 'mouseover': related = event.relatedTarget || event.fromElement; break;
                                        case 'mouseout': related = event.relatedTarget || event.toElement;
                                }
                                if (!(function(){
                                        while (related && related.nodeType == 3) related = related.parentNode;
                                        return true;
                                }).mooCreate({attempt: Browser.Engine.gecko})()) related = false;
                        }
                }

                return $mooExtend(this, {
                        event: event,
                        type: type,

                        page: page,
                        client: client,
                        rightClick: rightClick,

                        wheel: wheel,

                        relatedTarget: related,
                        target: target,

                        code: code,
                        key: key,

                        shift: event.shiftKey,
                        control: event.ctrlKey,
                        alt: event.altKey,
                        meta: event.metaKey
                });
        }

});

MooEvent.mooKeys = new MooHash({
        'enter': 13,
        'up': 38,
        'down': 40,
        'left': 37,
        'right': 39,
        'esc': 27,
        'space': 32,
        'backspace': 8,
        'tab': 9,
        'delete': 46
});

MooEvent.implement({

        mooStop: function(){
                return this.mooStopPropagation().mooPreventDefault();
        },

        mooStopPropagation: function(){
                if (this.event.stopPropagation) this.event.stopPropagation();
                else this.event.cancelBubble = true;
                return this;
        },

        mooPreventDefault: function(){
                if (this.event.preventDefault) this.event.preventDefault();
                else this.event.returnValue = false;
                return this;
        }

});


var MooClass = new MooNative({

        name: 'Class',

        initialize: function(properties){
                properties = properties || {};
                var klass = function(){
                        for (var key in this){
                                if ($mooType(this[key]) != 'function') this[key] = $mooUnlink(this[key]);
                        }
                        this.constructor = klass;
                        if (MooClass.prototyping) return this;
                        var instance = (this.initialize) ? this.initialize.apply(this, arguments) : this;
                        if (this.options && this.options.initialize) this.options.initialize.call(this);
                        return instance;
                };

                for (var mutator in MooClass.Mutators){
                        if (!properties[mutator]) continue;
                        properties = MooClass.Mutators[mutator](properties, properties[mutator]);
                        delete properties[mutator];
                }

                $mooExtend(klass, this);
                klass.constructor = MooClass;
                klass.prototype = properties;
                return klass;
        }

});

MooClass.Mutators = {

        Extends: function(self, klass){
                MooClass.prototyping = klass.prototype;
                var subclass = new klass;
                delete subclass.parent;
                subclass = MooClass.inherit(subclass, self);
                delete MooClass.prototyping;
                return subclass;
        },

        Implements: function(self, klasses){
                $mooSplat(klasses).mooEach(function(klass){
                        MooClass.prototying = klass;
                        $mooExtend(self, ($mooType(klass) == 'class') ? new klass : klass);
                        delete MooClass.prototyping;
                });
                return self;
        }

};

MooClass.mooExtend({

        inherit: function(object, properties){
                var caller = arguments.callee.caller;
                for (var key in properties){
                        var override = properties[key];
                        var previous = object[key];
                        var type = $mooType(override);
                        if (previous && type == 'function'){
                                if (override != previous){
                                        if (caller){
                                                override.__parent = previous;
                                                object[key] = override;
                                        } else {
                                                MooClass.override(object, key, override);
                                        }
                                }
                        } else if(type == 'object'){
                                object[key] = $mooMerge(previous, override);
                        } else {
                                object[key] = override;
                        }
                }

                if (caller) object.parent = function(){
                        return arguments.callee.caller.__parent.apply(this, arguments);
                };

                return object;
        },

        override: function(object, name, method){
                var parent = MooClass.prototyping;
                if (parent && object[name] != parent[name]) parent = null;
                var override = function(){
                        var previous = this.parent;
                        this.parent = parent ? parent[name] : object[name];
                        var value = method.apply(this, arguments);
                        this.parent = previous;
                        return value;
                };
                object[name] = override;
        }

});

MooClass.implement({

        implement: function(){
                var proto = this.prototype;
                $each(arguments, function(properties){
                        Class.inherit(proto, properties);
                });
                return this;
        }

});

var MooChain = new MooClass({

        $chain: [],

        chain: function(){
                this.$chain.mooExtend(Array.mooFlatten(arguments));
                return this;
        },

        callChain: function(){
                return (this.$chain.length) ? this.$chain.shift().apply(this, arguments) : false;
        },

        clearChain: function(){
                this.$chain.mooEmpty();
                return this;
        }

});

var MooEvents = new MooClass({

        $mooEvents: {},

        mooAddEvent: function(type, fn, internal){
                type = MooEvents.removeOn(type);
                if (fn != $mooEmpty){
                        this.$mooEvents[type] = this.$mooEvents[type] || [];
                        this.$mooEvents[type].mooInclude(fn);
                        if (internal) fn.internal = true;
                }
                return this;
        },

        mooAddEvents: function(events){
                for (var type in events) this.mooAddEvent(type, events[type]);
                return this;
        },

        mooFireEvent: function(type, args, delay){
                type = MooEvents.removeOn(type);
                if (!this.$mooEvents || !this.$mooEvents[type]) return this;
                this.$mooEvents[type].mooEach(function(fn){
                        fn.mooCreate({'bind': this, 'delay': delay, 'arguments': args})();
                }, this);
                return this;
        },

        mooRemoveEvent: function(type, fn){
                type = MooEvents.removeOn(type);
                if (!this.$mooEvents[type]) return this;
                if (!fn.internal) this.$mooEvents[type].erase(fn);
                return this;
        },

        mooRemoveEvents: function(events){
                if ($mooType(events) == 'object'){
                        for (var type in events) this.mooRemoveEvent(type, events[type]);
                        return this;
                }
                if (events) events = MooEvents.removeOn(events);
                for (var type in this.$mooEvents){
                        if (events && events != type) continue;
                        var fns = this.$mooEvents[type];
                        for (var i = fns.length; i--; i) this.mooRemoveEvent(type, fns[i]);
                }
                return this;
        }

});

MooEvents.removeOn = function(string){
        return string.replace(/^on([A-Z])/, function(full, first) {
                return first.toLowerCase();
        });
};

var MooOptions = new MooClass({

        setOptions: function(){
                this.options = $mooMerge.mooRun([this.options].mooExtend(arguments));
                if (!this.mooAddEvent) return this;
                for (var option in this.options){
                        if ($mooType(this.options[option]) != 'function' || !(/^on[A-Z]/).test(option)) continue;
                        this.mooAddEvent(option, this.options[option]);
                        delete this.options[option];
                }
                return this;
        }

});

var MooElement = new MooNative({

        name: 'Element',

        legacy: window.Element,

        initialize: function(tag, props){
                var konstructor = MooElement.Constructors.mooGet(tag);
                if (konstructor) return konstructor(props);
                if (typeof tag == 'string') return document.newElement(tag, props);
                return $moo(tag).mooSet(props);
        },

        afterImplement: function(key, value){
                MooElement.Prototype[key] = value;
                if (Array[key]) return;
                MooElements.implement(key, function(){
                        var items = [], elements = true;
                        for (var i = 0, j = this.length; i < j; i++){
                                var returns = this[i][key].apply(this[i], arguments);
                                items.push(returns);
                                if (elements) elements = ($mooType(returns) == 'element');
                        }
                        return (elements) ? new MooElements(items) : items;
                });
        }

});

MooElement.Prototype = {$family: {name: 'element'}};

MooElement.Constructors = new MooHash;

var MooElements = new MooNative({

        initialize: function(elements, options){
                options = $mooExtend({ddup: true, cash: true}, options);
                elements = elements || [];
                if (options.ddup || options.cash){
                        var uniques = {}, returned = [];
                        for (var i = 0, l = elements.length; i < l; i++){
                                var el = $moo.element(elements[i], !options.cash);
                                if (options.ddup){
                                        if (uniques[el.uid]) continue;
                                        uniques[el.uid] = true;
                                }
                                returned.push(el);
                        }
                        elements = returned;
                }
                return (options.cash) ? $mooExtend(elements, this) : elements;
        }

});

MooElements.implement({

        mooFilter: function(filter, bind){
                if (!filter) return this;
                return new MooElements(Array.filter(this, (typeof filter == 'string') ? function(item){
                        return item.match(filter);
                } : filter, bind));
        }

});

MooDocument.implement({

        mooGetWindow: function(){
                return this.window;
        }

});


MooWindow.implement({

        $moo: function(el, nocash){
                if (el && el.$family && el.uid) return el;
                var type = $mooType(el);
                return ($moo[type]) ? $moo[type](el, nocash, this.document) : null;
        },

        $$moo: function(selector){
                if (arguments.length == 1 && typeof selector == 'string') return this.document.mooGetElements(selector);
                var elements = [];
                var args = Array.mooFlatten(arguments);
                for (var i = 0, l = args.length; i < l; i++){
                        var item = args[i];
                        switch ($mooType(item)){
                                case 'element': elements.push(item); break;
                                case 'string': elements.mooExtend(this.document.mooGetElements(item, true));
                        }
                }
                return new MooElements(elements);
        },

        mooGetDocument: function(){
                return this.document;
        },

        mooGetWindow: function(){
                return this;
        }

});

$moo.string = function(id, nocash, doc){
        id = doc.getElementById(id);
        return (id) ? $moo.element(id, nocash) : null;
};

$moo.element = function(el, nocash){
        $mooUid(el);
        if (!nocash && !el.$family && !(/^object|embed$/i).test(el.tagName)){
                var proto = MooElement.Prototype;
                for (var p in proto) el[p] = proto[p];
        };
        return el;
};

MooNative.implement([MooElement, MooDocument], {

        mooGetElement: function(selector, nocash){
                return $moo(this.mooGetElements(selector, true)[0] || null, nocash);
        },

        mooGetElements: function(tags, nocash){
                tags = tags.split(',');
                var elements = [];
                var ddup = (tags.length > 1);
                tags.mooEach(function(tag){
                        var partial = this.getElementsByTagName(tag.mooTrim());
                        (ddup) ? elements.mooExtend(partial) : elements = partial;
                }, this);
                return new MooElements(elements, {ddup: ddup, cash: !nocash});
        }

});

(function(){

var collected = {}, storage = {};

var mooGet = function(uid){
        return (storage[uid] || (storage[uid] = {}));
};

var attributes = {
        'html': 'innerHTML',
        'class': 'className',
        'for': 'htmlFor',
        'text': (Browser.Engine.trident || (Browser.Engine.webkit && Browser.Engine.version < 420)) ? 'innerText' : 'textContent'
};
var bools = ['compact', 'nowrap', 'ismap', 'declare', 'noshade', 'checked', 'disabled', 'readonly', 'multiple', 'selected', 'noresize', 'defer'];
var camels = ['value', 'accessKey', 'cellPadding', 'cellSpacing', 'colSpan', 'frameBorder', 'maxLength', 'readOnly', 'rowSpan', 'tabIndex', 'useMap'];

MooHash.mooExtend(attributes, bools.mooAssociate(bools));
MooHash.mooExtend(attributes, camels.mooAssociate(camels.mooMap(String.toLowerCase)));

MooElement.implement({

        mooSet: function(prop, value){
                switch ($mooType(prop)){
                        case 'object':
                                for (var p in prop) this.mooSet(p, prop[p]);
                                break;
                        case 'string':
                                var property = MooElement.Properties.mooGet(prop);
                                (property && property.mooSet) ? property.mooSet.apply(this, Array.slice(arguments, 1)) : this.mooSetProperty(prop, value);
                }
                return this;
        },

        mooGet: function(prop){
                var property = MooElement.Properties.mooGet(prop);
                return (property && property.mooGet) ? property.mooGet.apply(this, Array.slice(arguments, 1)) : this.mooGetProperty(prop);
        },

        mooSetProperty: function(attribute, value){
                var key = attributes[attribute];
                if (value == undefined) return this.removeProperty(attribute);
                if (key && bools[attribute]) value = !!value;
                (key) ? this[key] = value : this.setAttribute(attribute, '' + value);
                return this;
        },

        mooGetProperty: function(attribute){
                var key = attributes[attribute];
                var value = (key) ? this[key] : this.getAttribute(attribute, 2);
                return (bools[attribute]) ? !!value : (key) ? value : value || null;
        },

        mooHasClass: function(className){
                return this.className.mooContains(className, ' ');
        },

        mooAddClass: function(className){
                if (!this.mooHasClass(className)) this.className = (this.className + ' ' + className).mooClean();
                return this;
        },

        mooRemoveClass: function(className){
                this.className = this.className.replace(new RegExp('(^|\\s)' + className + '(?:\\s|$)'), '$1');
                return this;
        },

        mooGetWindow: function(){
                return this.ownerDocument.window;
        },

        mooGetDocument: function(){
                return this.ownerDocument;
        },

        mooGetComputedStyle: function(property){
                if (this.currentStyle) return this.currentStyle[property.mooCamelCase()];
                var computed = this.mooGetDocument().defaultView.getComputedStyle(this, null);
                return (computed) ? computed.getPropertyValue([property.mooHyphenate()]) : null;
        },

        mooHasChild: function(el){
                el = $moo(el, true);
                if (!el) return false;
                if (Browser.Engine.webkit && Browser.Engine.version < 420) return $mooA(this.getElementsByTagName(el.tagName)).mooContains(el);
                return (this.contains) ? (this != el && this.contains(el)) : !!(this.compareDocumentPosition(el) & 16);
        }

});

MooNative.implement([MooElement, MooWindow, MooDocument], {

        mooAddListener: function(type, fn){
                if (type == 'unload'){
                        var old = fn, self = this;
                        fn = function(){
                                self.removeListener('unload', fn);
                                old();
                        };
                } else {
                        collected[this.uid] = this;
                }
                if (this.addEventListener) this.addEventListener(type, fn, false);
                else this.attachEvent('on' + type, fn);
                return this;
        },

        mooRetrieve: function(property, dflt){
                var storage = mooGet(this.uid), prop = storage[property];
                if (dflt != undefined && prop == undefined) prop = storage[property] = dflt;
                return $mooPick(prop);
        },

        mooStore: function(property, value){
                var storage = mooGet(this.uid);
                storage[property] = value;
                return this;
        },

        mooEliminate: function(property){
                var storage = mooGet(this.uid);
                delete storage[property];
                return this;
        }

});

})();

MooElement.Properties = new MooHash;

MooNative.implement([MooElement, MooWindow, MooDocument], {

        mooAddEvent: function(type, fn){
                var events = this.mooRetrieve('events', {});
                events[type] = events[type] || {'keys': [], 'values': []};
                if (events[type].keys.mooContains(fn)) return this;
                events[type].keys.push(fn);
                var realType = type, custom = MooElement.MooEvents.mooGet(type), condition = fn, self = this;
                if (custom){
                        if (custom.onAdd) custom.onAdd.call(this, fn);
                        if (custom.condition){
                                condition = function(event){
                                        if (custom.condition.call(this, event)) return fn.call(this, event);
                                        return true;
                                };
                        }
                        realType = custom.base || realType;
                }
                var defn = function(){
                        return fn.call(self);
                };
                var nativeEvent = MooElement.NativeEvents[realType];
                if (nativeEvent){
                        if (nativeEvent == 2){
                                defn = function(event){
                                        event = new MooEvent(event, self.mooGetWindow());
                                        if (condition.call(self, event) === false) event.mooStop();
                                };
                        }
                        this.mooAddListener(realType, defn);
                }
                events[type].values.push(defn);
                return this;
        }

});

MooElement.NativeEvents = {
        click: 2, dblclick: 2, mouseup: 2, mousedown: 2, contextmenu: 2, //mouse buttons
        mousewheel: 2, DOMMouseScroll: 2, //mouse wheel
        mouseover: 2, mouseout: 2, mousemove: 2, selectstart: 2, selectend: 2, //mouse movement
        keydown: 2, keypress: 2, keyup: 2, //keyboard
        focus: 2, blur: 2, change: 2, reset: 2, select: 2, submit: 2, //form elements
        load: 1, unload: 1, beforeunload: 2, resize: 1, move: 1, DOMContentLoaded: 1, readystatechange: 1, //window
        error: 1, abort: 1, scroll: 1 //misc
};

(function(){

var $mooCheck = function(event){
        var related = event.relatedTarget;
        if (related == undefined) return true;
        if (related === false) return false;
        return ($mooType(this) != 'document' && related != this && related.prefix != 'xul' && !this.mooHasChild(related));
};

MooElement.MooEvents = new MooHash({

        mouseenter: {
                base: 'mouseover',
                condition: $mooCheck
        },

        mouseleave: {
                base: 'mouseout',
                condition: $mooCheck
        },

        mousewheel: {
                base: (Browser.Engine.gecko) ? 'DOMMouseScroll' : 'mousewheel'
        }

});

})();

MooElement.Properties.opacity = {

        mooSet: function(opacity, novisibility){
                if (!novisibility){
                        if (opacity == 0){
                                if (this.style.visibility != 'hidden') this.style.visibility = 'hidden';
                        } else {
                                if (this.style.visibility != 'visible') this.style.visibility = 'visible';
                        }
                }
                if (!this.currentStyle || !this.currentStyle.hasLayout) this.style.zoom = 1;
                if (Browser.Engine.trident) this.style.filter = (opacity == 1) ? '' : 'alpha(opacity=' + opacity * 100 + ')';
                this.style.opacity = opacity;
                this.mooStore('opacity', opacity);
        },

        mooGet: function(){
                return this.mooRetrieve('opacity', 1);
        }

};

MooElement.implement({

        mooSetStyle: function(property, value){
                switch (property){
                        case 'opacity': return this.mooSet('opacity', parseFloat(value));
                        case 'float': property = (Browser.Engine.trident) ? 'styleFloat' : 'cssFloat';
                }
                property = property.mooCamelCase();
                if ($mooType(value) != 'string'){
                        var map = (MooElement.Styles.mooGet(property) || '@').split(' ');
                        value = $mooSplat(value).mooMap(function(val, i){
                                if (!map[i]) return '';
                                return ($mooType(val) == 'number') ? map[i].replace('@', Math.round(val)) : val;
                        }).join(' ');
                } else if (value == String(Number(value))){
                        value = Math.round(value);
                }
                this.style[property] = value;
                return this;
        },

        mooGetStyle: function(property){
                switch (property){
                        case 'opacity': return this.mooGet('opacity');
                        case 'float': property = (Browser.Engine.trident) ? 'styleFloat' : 'cssFloat';
                }
                property = property.mooCamelCase();
                var result = this.style[property];
                if (!$mooChk(result)){
                        result = [];
                        for (var style in MooElement.ShortStyles){
                                if (property != style) continue;
                                for (var s in MooElement.ShortStyles[style]) result.push(this.mooGetStyle(s));
                                return result.join(' ');
                        }
                        result = this.mooGetComputedStyle(property);
                }
                if (result){
                        result = String(result);
                        var color = result.match(/rgba?\([\d\s,]+\)/);
                        if (color) result = result.replace(color[0], color[0].rgbToHex());
                }
                if (Browser.Engine.presto || (Browser.Engine.trident && !$mooChk(parseInt(result)))){
                        if (property.mooTest(/^(height|width)$/)){
                                var values = (property == 'width') ? ['left', 'right'] : ['top', 'bottom'], size = 0;
                                values.each(function(value){
                                        size += this.mooGetStyle('border-' + value + '-width').mooToInt() + this.mooGetStyle('padding-' + value).mooToInt();
                                }, this);
                                return this['offset' + property.capitalize()] - size + 'px';
                        }
                        if ((Browser.Engine.presto) && String(result).mooTest('px')) return result;
                        if (property.mooTest(/(border(.+)Width|margin|padding)/)) return '0px';
                }
                return result;
        }

});

MooElement.Styles = new MooHash({
        left: '@px', top: '@px', bottom: '@px', right: '@px',
        width: '@px', height: '@px', maxWidth: '@px', maxHeight: '@px', minWidth: '@px', minHeight: '@px',
        backgroundColor: 'rgb(@, @, @)', backgroundPosition: '@px @px', color: 'rgb(@, @, @)',
        fontSize: '@px', letterSpacing: '@px', lineHeight: '@px', clip: 'rect(@px @px @px @px)',
        margin: '@px @px @px @px', padding: '@px @px @px @px', border: '@px @ rgb(@, @, @) @px @ rgb(@, @, @) @px @ rgb(@, @, @)',
        borderWidth: '@px @px @px @px', borderStyle: '@ @ @ @', borderColor: 'rgb(@, @, @) rgb(@, @, @) rgb(@, @, @) rgb(@, @, @)',
        zIndex: '@', 'zoom': '@', fontWeight: '@', textIndent: '@px', opacity: '@'
});

MooNative.implement([MooDocument, MooElement], {

        mooGetElements: function(expression, nocash){
                expression = expression.split(',');
                var items, local = {};
                for (var i = 0, l = expression.length; i < l; i++){
                        var selector = expression[i], elements = MooSelectors.Utils.search(this, selector, local);
                        if (i != 0 && elements.item) elements = $mooA(elements);
                        items = (i == 0) ? elements : (items.item) ? $mooA(items).concat(elements) : items.concat(elements);
                }
                return new MooElements(items, {ddup: (expression.length > 1), cash: !nocash});
        }

});

var MooSelectors = {Cache: {nth: {}, parsed: {}}};

MooSelectors.RegExps = {
        id: (/#([\w-]+)/),
        tag: (/^(\w+|\*)/),
        quick: (/^(\w+|\*)$/),
        splitter: (/\s*([+>~\s])\s*([a-zA-Z#.*:\[])/g),
        combined: (/\.([\w-]+)|\[(\w+)(?:([!*^$~|]?=)(["']?)([^\4]*?)\4)?\]|:([\w-]+)(?:\(["']?(.*?)?["']?\)|$)/g)
};

MooSelectors.Utils = {

        chk: function(item, uniques){
                if (!uniques) return true;
                var uid = $mooUid(item);
                if (!uniques[uid]) return uniques[uid] = true;
                return false;
        },

        parseNthArgument: function(argument){
                if (MooSelectors.Cache.nth[argument]) return MooSelectors.Cache.nth[argument];
                var parsed = argument.match(/^([+-]?\d*)?([a-z]+)?([+-]?\d*)?$/);
                if (!parsed) return false;
                var inta = parseInt(parsed[1]);
                var a = (inta || inta === 0) ? inta : 1;
                var special = parsed[2] || false;
                var b = parseInt(parsed[3]) || 0;
                if (a != 0){
                        b--;
                        while (b < 1) b += a;
                        while (b >= a) b -= a;
                } else {
                        a = b;
                        special = 'index';
                }
                switch (special){
                        case 'n': parsed = {a: a, b: b, special: 'n'}; break;
                        case 'odd': parsed = {a: 2, b: 0, special: 'n'}; break;
                        case 'even': parsed = {a: 2, b: 1, special: 'n'}; break;
                        case 'first': parsed = {a: 0, special: 'index'}; break;
                        case 'last': parsed = {special: 'last-child'}; break;
                        case 'only': parsed = {special: 'only-child'}; break;
                        default: parsed = {a: (a - 1), special: 'index'};
                }

                return MooSelectors.Cache.nth[argument] = parsed;
        },

        parseSelector: function(selector){
                if (MooSelectors.Cache.parsed[selector]) return MooSelectors.Cache.parsed[selector];
                var m, parsed = {classes: [], pseudos: [], attributes: []};
                while ((m = MooSelectors.RegExps.combined.exec(selector))){
                        var cn = m[1], an = m[2], ao = m[3], av = m[5], pn = m[6], pa = m[7];
                        if (cn){
                                parsed.classes.push(cn);
                        } else if (pn){
                                var parser = MooSelectors.Pseudo.mooGet(pn);
                                if (parser) parsed.pseudos.push({parser: parser, argument: pa});
                                else parsed.attributes.push({name: pn, operator: '=', value: pa});
                        } else if (an){
                                parsed.attributes.push({name: an, operator: ao, value: av});
                        }
                }
                if (!parsed.classes.length) delete parsed.classes;
                if (!parsed.attributes.length) delete parsed.attributes;
                if (!parsed.pseudos.length) delete parsed.pseudos;
                if (!parsed.classes && !parsed.attributes && !parsed.pseudos) parsed = null;
                return MooSelectors.Cache.parsed[selector] = parsed;
        },

        parseTagAndID: function(selector){
                var tag = selector.match(MooSelectors.RegExps.tag);
                var id = selector.match(MooSelectors.RegExps.id);
                return [(tag) ? tag[1] : '*', (id) ? id[1] : false];
        },

        filter: function(item, parsed, local){
                var i;
                if (parsed.classes){
                        for (i = parsed.classes.length; i--; i){
                                var cn = parsed.classes[i];
                                if (!MooSelectors.Filters.byClass(item, cn)) return false;
                        }
                }
                if (parsed.attributes){
                        for (i = parsed.attributes.length; i--; i){
                                var att = parsed.attributes[i];
                                if (!MooSelectors.Filters.byAttribute(item, att.name, att.operator, att.value)) return false;
                        }
                }
                if (parsed.pseudos){
                        for (i = parsed.pseudos.length; i--; i){
                                var psd = parsed.pseudos[i];
                                if (!MooSelectors.Filters.byPseudo(item, psd.parser, psd.argument, local)) return false;
                        }
                }
                return true;
        },

        getByTagAndID: function(ctx, tag, id){
                if (id){
                        var item = (ctx.getElementById) ? ctx.getElementById(id, true) : MooElement.getElementById(ctx, id, true);
                        return (item && MooSelectors.Filters.byTag(item, tag)) ? [item] : [];
                } else {
                        return ctx.getElementsByTagName(tag);
                }
        },

        search: function(self, expression, local){
                var splitters = [];

                var selectors = expression.mooTrim().replace(MooSelectors.RegExps.splitter, function(m0, m1, m2){
                        splitters.push(m1);
                        return ':)' + m2;
                }).split(':)');

                var items, filtered, item;

                for (var i = 0, l = selectors.length; i < l; i++){

                        var selector = selectors[i];

                        if (i == 0 && MooSelectors.RegExps.quick.test(selector)){
                                items = self.getElementsByTagName(selector);
                                continue;
                        }

                        var splitter = splitters[i - 1];

                        var tagid = MooSelectors.Utils.parseTagAndID(selector);
                        var tag = tagid[0], id = tagid[1];

                        if (i == 0){
                                items = MooSelectors.Utils.getByTagAndID(self, tag, id);
                        } else {
                                var uniques = {}, found = [];
                                for (var j = 0, k = items.length; j < k; j++) found = MooSelectors.Getters[splitter](found, items[j], tag, id, uniques);
                                items = found;
                        }

                        var parsed = MooSelectors.Utils.parseSelector(selector);

                        if (parsed){
                                filtered = [];
                                for (var m = 0, n = items.length; m < n; m++){
                                        item = items[m];
                                        if (MooSelectors.Utils.filter(item, parsed, local)) filtered.push(item);
                                }
                                items = filtered;
                        }

                }

                return items;

        }

};

MooSelectors.Getters = {

        ' ': function(found, self, tag, id, uniques){
                var items = MooSelectors.Utils.getByTagAndID(self, tag, id);
                for (var i = 0, l = items.length; i < l; i++){
                        var item = items[i];
                        if (MooSelectors.Utils.chk(item, uniques)) found.push(item);
                }
                return found;
        },

        '>': function(found, self, tag, id, uniques){
                var children = MooSelectors.Utils.getByTagAndID(self, tag, id);
                for (var i = 0, l = children.length; i < l; i++){
                        var child = children[i];
                        if (child.parentNode == self && MooSelectors.Utils.chk(child, uniques)) found.push(child);
                }
                return found;
        },

        '+': function(found, self, tag, id, uniques){
                while ((self = self.nextSibling)){
                        if (self.nodeType == 1){
                                if (MooSelectors.Utils.chk(self, uniques) && MooSelectors.Filters.byTag(self, tag) && MooSelectors.Filters.byID(self, id)) found.push(self);
                                break;
                        }
                }
                return found;
        },

        '~': function(found, self, tag, id, uniques){
                while ((self = self.nextSibling)){
                        if (self.nodeType == 1){
                                if (!MooSelectors.Utils.chk(self, uniques)) break;
                                if (MooSelectors.Filters.byTag(self, tag) && MooSelectors.Filters.byID(self, id)) found.push(self);
                        }
                }
                return found;
        }

};

MooSelectors.Filters = {

        byTag: function(self, tag){
                return (tag == '*' || (self.tagName && self.tagName.toLowerCase() == tag));
        },

        byID: function(self, id){
                return (!id || (self.id && self.id == id));
        },

        byClass: function(self, klass){
                return (self.className && self.className.mooContains(klass, ' '));
        },

        byPseudo: function(self, parser, argument, local){
                return parser.call(self, argument, local);
        },

        byAttribute: function(self, name, operator, value){
                var result = MooElement.prototype.mooGetProperty.call(self, name);
                if (!result) return (operator == '!=');
                if (!operator || value == undefined) return true;
                switch (operator){
                        case '=': return (result == value);
                        case '*=': return (result.mooContains(value));
                        case '^=': return (result.substr(0, value.length) == value);
                        case '$=': return (result.substr(result.length - value.length) == value);
                        case '!=': return (result != value);
                        case '~=': return result.mooContains(value, ' ');
                        case '|=': return result.mooContains(value, '-');
                }
                return false;
        }

};

MooSelectors.Pseudo = new MooHash({

        // w3c pseudo MooSelectors

        checked: function(){
                return this.checked;
        },

        empty: function(){
                return !(this.innerText || this.textContent || '').length;
        },

        not: function(selector){
                return !MooElement.match(this, selector);
        },

        mooContains: function(text){
                return (this.innerText || this.textContent || '').mooContains(text);
        },

        'first-child': function(){
                return MooSelectors.Pseudo.index.call(this, 0);
        },

        'last-child': function(){
                var element = this;
                while ((element = element.nextSibling)){
                        if (element.nodeType == 1) return false;
                }
                return true;
        },

        'only-child': function(){
                var prev = this;
                while ((prev = prev.previousSibling)){
                        if (prev.nodeType == 1) return false;
                }
                var next = this;
                while ((next = next.nextSibling)){
                        if (next.nodeType == 1) return false;
                }
                return true;
        },

        'nth-child': function(argument, local){
                argument = (argument == undefined) ? 'n' : argument;
                var parsed = MooSelectors.Utils.parseNthArgument(argument);
                if (parsed.special != 'n') return MooSelectors.Pseudo[parsed.special].call(this, parsed.a, local);
                var count = 0;
                local.positions = local.positions || {};
                var uid = $mooUid(this);
                if (!local.positions[uid]){
                        var self = this;
                        while ((self = self.previousSibling)){
                                if (self.nodeType != 1) continue;
                                count ++;
                                var position = local.positions[$mooUid(self)];
                                if (position != undefined){
                                        count = position + count;
                                        break;
                                }
                        }
                        local.positions[uid] = count;
                }
                return (local.positions[uid] % parsed.a == parsed.b);
        },

        // custom pseudo selectors

        index: function(index){
                var element = this, count = 0;
                while ((element = element.previousSibling)){
                        if (element.nodeType == 1 && ++count > index) return false;
                }
                return (count == index);
        },

        even: function(argument, local){
                return MooSelectors.Pseudo['nth-child'].call(this, '2n+1', local);
        },

        odd: function(argument, local){
                return MooSelectors.Pseudo['nth-child'].call(this, '2n', local);
        }

});

var MooFx = new MooClass({

        Implements: [MooChain, MooEvents, MooOptions],

        options: {
                /*
                onStart: $mooEmpty,
                onCancel: $mooEmpty,
                onComplete: $mooEmpty,
                */
                fps: 50,
                unit: false,
                duration: 500,
                link: 'ignore'
        },

        initialize: function(options){
                this.subject = this.subject || this;
                this.setOptions(options);
                this.options.duration = MooFx.Durations[this.options.duration] || this.options.duration.mooToInt();
                var wait = this.options.wait;
                if (wait === false) this.options.link = 'cancel';
        },

        getTransition: function(){
                return function(p){
                        return -(Math.cos(Math.PI * p) - 1) / 2;
                };
        },

        step: function(){
                var time = $mooTime();
                if (time < this.time + this.options.duration){
                        var delta = this.transition((time - this.time) / this.options.duration);
                        this.mooSet(this.compute(this.from, this.to, delta));
                } else {
                        this.mooSet(this.compute(this.from, this.to, 1));
                        this.complete();
                }
        },

        mooSet: function(now){
                return now;
        },

        compute: function(from, to, delta){
                return MooFx.compute(from, to, delta);
        },

        check: function(caller){
                if (!this.timer) return true;
                switch (this.options.link){
                        case 'cancel': this.cancel(); return true;
                        case 'chain': this.chain(caller.mooBind(this, Array.slice(arguments, 1))); return false;
                }
                return false;
        },

        start: function(from, to){
                if (!this.check(arguments.callee, from, to)) return this;
                this.from = from;
                this.to = to;
                this.time = 0;
                this.transition = this.getTransition();
                this.startTimer();
                this.onStart();
                return this;
        },

        complete: function(){
                if (this.stopTimer()) this.onComplete();
                return this;
        },

        cancel: function(){
                if (this.stopTimer()) this.onCancel();
                return this;
        },

        onStart: function(){
                this.mooFireEvent('start', this.subject);
        },

        onComplete: function(){
                this.mooFireEvent('complete', this.subject);
                if (!this.callChain()) this.mooFireEvent('chainComplete', this.subject);
        },

        onCancel: function(){
                this.mooFireEvent('cancel', this.subject).clearChain();
        },

        pause: function(){
                this.stopTimer();
                return this;
        },

        resume: function(){
                this.startTimer();
                return this;
        },

        stopTimer: function(){
                if (!this.timer) return false;
                this.time = $mooTime() - this.time;
                this.timer = $mooClear(this.timer);
                return true;
        },

        startTimer: function(){
                if (this.timer) return false;
                this.time = $mooTime() - this.time;
                this.timer = this.step.periodical(Math.round(1000 / this.options.fps), this);
                return true;
        }

});

MooFx.compute = function(from, to, delta){
        return (to - from) * delta + from;
};

MooFx.Durations = {'short': 250, 'normal': 500, 'long': 1000};

MooFx.CSS = new MooClass({

        Extends: MooFx,

        //prepares the base from/to object

        prepare: function(element, property, values){
                values = $mooSplat(values);
                var values1 = values[1];
                if (!$mooChk(values1)){
                        values[1] = values[0];
                        values[0] = element.mooGetStyle(property);
                }
                var parsed = values.mooMap(this.parse);
                return {from: parsed[0], to: parsed[1]};
        },

        //parses a value into an array

        parse: function(value){
                value = $mooLambda(value)();
                value = (typeof value == 'string') ? value.split(' ') : $mooSplat(value);
                return value.mooMap(function(val){
                        val = String(val);
                        var found = false;
                        MooFx.CSS.Parsers.mooEach(function(parser, key){
                                if (found) return;
                                var parsed = parser.parse(val);
                                if ($mooChk(parsed)) found = {value: parsed, parser: parser};
                        });
                        found = found || {value: val, parser: MooFx.CSS.Parsers.String};
                        return found;
                });
        },
        //computes by a from and to prepared objects, using their parsers.

        compute: function(from, to, delta){
                var computed = [];
                (Math.min(from.length, to.length)).mooTimes(function(i){
                        computed.push({value: from[i].parser.compute(from[i].value, to[i].value, delta), parser: from[i].parser});
                });
                computed.$family = {name: 'fx:css:value'};
                return computed;
        },

        //serves the value as settable

        serve: function(value, unit){
                if ($mooType(value) != 'fx:css:value') value = this.parse(value);
                var returned = [];
                value.mooEach(function(bit){
                        returned = returned.concat(bit.parser.serve(bit.value, unit));
                });
                return returned;
        },

        //renders the change to an element

        render: function(element, property, value, unit){
                element.mooSetStyle(property, this.serve(value, unit));
        },

        //searches inside the page css to find the values for a selector

        search: function(selector){
                if (MooFx.CSS.Cache[selector]) return MooFx.CSS.Cache[selector];
                var to = {};
                Array.mooEach(document.styleSheets, function(sheet, j){
                        var href = sheet.href;
                        if (href && href.mooContains('://') && !href.mooContains(document.domain)) return;
                        var rules = sheet.rules || sheet.cssRules;
                        Array.mooEach(rules, function(rule, i){
                                if (!rule.style) return;
                                var selectorText = (rule.selectorText) ? rule.selectorText.replace(/^\w+/, function(m){
                                        return m.toLowerCase();
                                }) : null;
                                if (!selectorText || !selectorText.mooTest('^' + selector + '$')) return;
                                MooElement.Styles.mooEAch(function(value, style){
                                        if (!rule.style[style] || MooElement.ShortStyles[style]) return;
                                        value = String(rule.style[style]);
                                        to[style] = (value.mooTest(/^rgb/)) ? value.rgbToHex() : value;
                                });
                        });
                });
                return MooFx.CSS.Cache[selector] = to;
        }

});

MooFx.CSS.Cache = {};

MooFx.CSS.Parsers = new MooHash({

        Color: {
                parse: function(value){
                        if (value.match(/^#[0-9a-f]{3,6}$/i)) return value.hexToRgb(true);
                        return ((value = value.match(/(\d+),\s*(\d+),\s*(\d+)/))) ? [value[1], value[2], value[3]] : false;
                },
                compute: function(from, to, delta){
                        return from.mooMap(function(value, i){
                                return Math.round(MooFx.compute(from[i], to[i], delta));
                        });
                },
                serve: function(value){
                        return value.mooMap(Number);
                }
        },

        Number: {
                parse: parseFloat,
                compute: MooFx.compute,
                serve: function(value, unit){
                        return (unit) ? value + unit : value;
                }
        },

        String: {
                parse: $mooLambda(false),
                compute: $mooArguments(1),
                serve: $mooArguments(0)
        }

});


MooFx.Morph = new MooClass({

        Extends: MooFx.CSS,

        initialize: function(element, options){
                this.element = this.subject = $moo(element);
                this.parent(options);
        },

        mooSet: function(now){
                if (typeof now == 'string') now = this.search(now);
                for (var p in now) this.render(this.element, p, now[p], this.options.unit);
                return this;
        },

        compute: function(from, to, delta){
                var now = {};
                for (var p in from) now[p] = this.parent(from[p], to[p], delta);
                return now;
        },

        start: function(properties){
                if (!this.check(arguments.callee, properties)) return this;
                if (typeof properties == 'string') properties = this.search(properties);
                var from = {}, to = {};
                for (var p in properties){
                        var parsed = this.prepare(this.element, p, properties[p]);
                        from[p] = parsed.from;
                        to[p] = parsed.to;
                }
                return this.parent(from, to);
        }

});

MooElement.Properties.morph = {

        mooSet: function(options){
                var morph = this.mooRetrieve('morph');
                if (morph) morph.cancel();
                return this.mooEliminate('morph').mooStore('morph:options', $mooExtend({link: 'cancel'}, options));
        },

        mooGet: function(options){
                if (options || !this.mooRetrieve('morph')){
                        if (options || !this.mooRetrieve('morph:options')) this.mooSet('morph', options);
                        this.mooStore('morph', new MooFx.Morph(this, this.mooRetrieve('morph:options')));
                }
                return this.mooRetrieve('morph');
        }

};

MooElement.implement({

        mooMorph: function(props){
                this.mooGet('morph').start(props);
                return this;
        }

});
