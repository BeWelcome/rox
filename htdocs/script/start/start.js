$(document).ready(function() {
    if(!(/Android|iPhone|iPad|iPod|BlackBerry|Windows Phone/i).test(navigator.userAgent || navigator.vendor || window.opera)){
       skrollr.init({
            forceHeight: false,
            smoothScrolling: true,
        });
        var s = skrollr.init();
        skrollr.menu.init(s, {animate: true, easing: 'sqrt', duration: function(currentTop, targetTop) {
            return Math.abs(currentTop - targetTop) * 0.3;},
        })
    }

    // Enable tooltips for the language dropdown
    $(function () {
        $('[data-toggle="tooltip"]').tooltip()
    })

    // AnySlider
    $('.slider').anyslider({
                interval: 2000,
                speed:700,
    })
});