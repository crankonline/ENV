window.utils = {};

(function(u){

    var cache = {
        'default': 0
    };

    u.sequence = {
        next: function(name){
            if(name == null){
                name = 'default';
            }

            if(!(name in cache)){
                cache[name] = 0;
            }

            return ++cache[name];
        },

        get: function(name){
            if(name == null){
                name = 'default';
            }

            if(!(name in cache)){
                cache[name] = 0;
            }

            return cache[name];
        },

        set: function(name, value){
            cache[name] = value;
        }
    };

})(window.utils);

(function(u){

    var initializers = [];

    u.initializers = {
        push: function(fn){
            initializers.push(fn);
        },

        run: function(){
            var len = initializers.length,
                f, fn;

            for(f = 0; fn = initializers[f], f < len; fn(), f++);
        }
    };

    window.onload = function(){
        u.initializers.run();
    };

})(window.utils);

(function(u){
    u.string = {
        format: function(){
            var args       = Array.prototype.slice.call(arguments, 0),
                string     = args.shift(),
                intRegEx   = /[^\d]+/g,
                braceRegEx = /\{(\d+)\}/g,
                replaceFn  = function(i){
                    i = parseInt(i.replace(intRegEx, ''));

                    return String(args[i]);
                };

            return String(string).replace(braceRegEx, replaceFn);
        },

        escapeHTML: (function(){
            var map = {
                    '&': '&amp;',
                    '<': '&lt;',
                    '>': '&gt;',
                    '"': '&#34;',
                    "'": '&#39;'
                },
                replacerFn = function(c){
                    return map[c];
                };

                return function(s) {
                    return String(s).replace(/[&<>'"]/g, replacerFn);
                };
            })()
    };

    u.object = {
        isEqual: function(a, b){
            var p, av, bv;

            for(p in a){
                av = a[p];
                bv = b[p];

                avo = typeof av === 'object';
                bvo = typeof bv === 'object';

                if(!(avo && bvo && u.object.isEqual(av, bv))){
                    if((av !== bv)){
                        return false;
                    }
                }
            }

            return true;
        },

        length: function(o){
            var c = 0,
                p;

            for(p in o){
                c += o.hasOwnProperty(p);
            }

            return c;
        }
    };
})(window.utils);