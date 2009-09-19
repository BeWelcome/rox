var late_loader = {
    queueFunction: function(func, args){
        Event.observe(window, 'load', function(e){
            func(args);
        });
    },
    queueNamedFunction: function(func_name, args){
        Event.observe(window, 'load', function(e){
            if (func_name.length && func_name.length > 0)
            {
                window[func_name](args);
            }
        });
    },
    queueObjectMethod: function(obj, method, args){
        Event.observe(window, 'load', function(e){
            if (obj.length && method.length && obj.length > 0 && method.length > 0)
            {
                window[obj][method](args);
            }
        });
    }
};

var common = {
    makeExpandableLinks: function(){
        var observer = function(e){
                var e = e || window.event;
                var target = e.target || e.srcElement;
                if (target.parentNode.className == 'expandable')
                {
                    target.parentNode.className = 'expanded';
                }
                else
                {
                    target.parentNode.className = 'expandable';
                }
                Event.stop(e);
            };
        $$('li.expandable a.header').each(function(it){
            it.observe('click', observer);
        });
        $$('li.expanded a.header').each(function(it){
            it.observe('click', observer);
        });
    }
};
