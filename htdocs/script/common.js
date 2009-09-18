var late_loader = {
    queue: function(func){
        Event.observe(window, 'load', function(e){
            if (typeof func == 'function')
            {
                func(e);
            }
            else if (typeof func == 'string')
            {
                window[func](e);
            }
        });
    }
};


