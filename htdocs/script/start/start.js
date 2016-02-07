
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
