var late_loader = {
    queueFunction: function(func){
        Event.observe(window, 'load', function(e){
            if (typeof func == 'function')
            {
                func(e);
            }
        });
    },
    queueNamedFunction: function(func_name){
        Event.observe(window, 'load', function(e){
            if (func_name.length && func_name.length > 0)
            {
                window[func_name](e);
            }
        });
    },
    queueObjectMethod: function(obj, method){
        Event.observe(window, 'load', function(e){
            if (obj.length && method.length && obj.length > 0 && method.length > 0)
            {
                window[obj][method](e);
            }
        });
    }
};
