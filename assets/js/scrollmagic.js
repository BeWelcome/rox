import TweenMax from 'TweenMax';
import ScrollMagic from 'ScrollMagic';
import 'animation.gsap';
import 'debug.addIndicators';

var controller = new ScrollMagic.Controller();
new ScrollMagic.Scene({triggerElement: "#trigger-1", duration: 600 })
    .setClassToggle("#scrollmagic-animation-1", "parallax--fixed-active")
    .addTo(controller);

new ScrollMagic.Scene({triggerElement: "#trigger-2", duration: 600 })
    .setClassToggle("#scrollmagic-animation-2", "parallax--fixed-active")
    .addTo(controller);

new ScrollMagic.Scene({triggerElement: "#trigger-3" })
    .setClassToggle("#scrollmagic-animation-3", "parallax--fixed-active")
    .addTo(controller);
