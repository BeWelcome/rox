import TweenMax from 'TweenMax';
import ScrollMagic from 'ScrollMagic';
import 'animation.gsap';
import 'debug.addIndicators';

var controller = new ScrollMagic.Controller();
new ScrollMagic.Scene({triggerElement: "#trigger-fade-1", duration: 600 })
    .setClassToggle("#fade-animation-1", "parallax--fixed-abovethefold")
    .addTo(controller);

new ScrollMagic.Scene({triggerElement: "#trigger-fade-1", offset: 600, duration: 300})
    .setClassToggle("#fade-animation-1", "parallax--fixed-leave")
    .addTo(controller);

new ScrollMagic.Scene({triggerElement: "#trigger-fade-2", offset: 100, duration: 500 })
    .setClassToggle("#fade-animation-2", "parallax--fixed-enter")
    .addTo(controller);

new ScrollMagic.Scene({triggerElement: "#trigger-fade-2", offset: 600, duration: 400  })
    .setClassToggle("#fade-animation-2", "parallax--fixed-leave")
    .addTo(controller);

new ScrollMagic.Scene({triggerElement: "#trigger-fade-2", offset: 100, duration: 500 })
    .setClassToggle("#fade-animation-2", "parallax--fixed-active")
    .addTo(controller);

new ScrollMagic.Scene({triggerElement: "#trigger-fade-3", triggerHook: 0.2 })
    .setClassToggle("#fade-animation-3", "parallax--fixed-enter")
    .addTo(controller);

new ScrollMagic.Scene({triggerElement: "#trigger-icon-animation-1", triggerHook: 1 })
    .setClassToggle("#icon-animation-1", "icon-animation-start")
    .addTo(controller);

new ScrollMagic.Scene({triggerElement: "#trigger-icon-animation-2", triggerHook: 1 })
    .setClassToggle("#icon-animation-2", "icon-animation-start")
    .addTo(controller);


