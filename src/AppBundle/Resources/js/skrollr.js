var skrollr = require('skrollr');

    'use strict';
    var s = skrollr.init({
        forceHeight: false,
        smoothScrolling: true,
    });
    skrollr.menu.init(s, {animate: true, easing: 'sqrt', duration: function(currentTop, targetTop) {
        return Math.abs(currentTop - targetTop) * 0.3;}
    });

