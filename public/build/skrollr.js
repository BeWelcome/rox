(window["webpackJsonp"] = window["webpackJsonp"] || []).push([["skrollr"],{

/***/ "./assets/js/skrollr.js":
/*!******************************!*\
  !*** ./assets/js/skrollr.js ***!
  \******************************/
/*! no exports provided */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var jarallax__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! jarallax */ "./node_modules/jarallax/index.js");
/* harmony import */ var jarallax__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(jarallax__WEBPACK_IMPORTED_MODULE_0__);


/***/ }),

/***/ "./node_modules/global/window.js":
/*!***************************************!*\
  !*** ./node_modules/global/window.js ***!
  \***************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

/* WEBPACK VAR INJECTION */(function(global) {var win;

if (typeof window !== "undefined") {
    win = window;
} else if (typeof global !== "undefined") {
    win = global;
} else if (typeof self !== "undefined"){
    win = self;
} else {
    win = {};
}

module.exports = win;

/* WEBPACK VAR INJECTION */}.call(this, __webpack_require__(/*! ./../webpack/buildin/global.js */ "./node_modules/webpack/buildin/global.js")))

/***/ }),

/***/ "./node_modules/jarallax/index.js":
/*!****************************************!*\
  !*** ./node_modules/jarallax/index.js ***!
  \****************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

const jarallax = __webpack_require__(/*! ./src/jarallax.esm */ "./node_modules/jarallax/src/jarallax.esm.js").default;
const jarallaxVideo = __webpack_require__(/*! ./src/jarallax-video.esm */ "./node_modules/jarallax/src/jarallax-video.esm.js").default;
const jarallaxElement = __webpack_require__(/*! ./src/jarallax-element.esm */ "./node_modules/jarallax/src/jarallax-element.esm.js").default;

module.exports = {
    jarallax,
    jarallaxElement() {
        return jarallaxElement(jarallax);
    },
    jarallaxVideo() {
        return jarallaxVideo(jarallax);
    },
};


/***/ }),

/***/ "./node_modules/jarallax/src/jarallax-element.esm.js":
/*!***********************************************************!*\
  !*** ./node_modules/jarallax/src/jarallax-element.esm.js ***!
  \***********************************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "default", function() { return jarallaxElement; });
/* harmony import */ var global__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! global */ "./node_modules/global/window.js");
/* harmony import */ var global__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(global__WEBPACK_IMPORTED_MODULE_0__);
/* eslint no-case-declarations: "off" */


function jarallaxElement(jarallax = global__WEBPACK_IMPORTED_MODULE_0___default.a.jarallax) {
    if (typeof jarallax === 'undefined') {
        return;
    }

    const Jarallax = jarallax.constructor;

    // redefine default methods
    [
        'initImg',
        'canInitParallax',
        'init',
        'destroy',
        'clipContainer',
        'coverImage',
        'isVisible',
        'onScroll',
        'onResize',
    ].forEach((key) => {
        const def = Jarallax.prototype[key];
        Jarallax.prototype[key] = function () {
            const self = this;
            const args = arguments || [];

            if (key === 'initImg' && self.$item.getAttribute('data-jarallax-element') !== null) {
                self.options.type = 'element';
                self.pureOptions.speed = self.$item.getAttribute('data-jarallax-element') || self.pureOptions.speed;
            }
            if (self.options.type !== 'element') {
                return def.apply(self, args);
            }

            self.pureOptions.threshold = self.$item.getAttribute('data-threshold') || '';

            switch (key) {
            case 'init':
                const speedArr = self.pureOptions.speed.split(' ');
                self.options.speed = self.pureOptions.speed || 0;
                self.options.speedY = speedArr[0] ? parseFloat(speedArr[0]) : 0;
                self.options.speedX = speedArr[1] ? parseFloat(speedArr[1]) : 0;

                const thresholdArr = self.pureOptions.threshold.split(' ');
                self.options.thresholdY = thresholdArr[0] ? parseFloat(thresholdArr[0]) : null;
                self.options.thresholdX = thresholdArr[1] ? parseFloat(thresholdArr[1]) : null;
                break;
            case 'onResize':
                const defTransform = self.css(self.$item, 'transform');
                self.css(self.$item, { transform: '' });
                const rect = self.$item.getBoundingClientRect();
                self.itemData = {
                    width: rect.width,
                    height: rect.height,
                    y: rect.top + self.getWindowData().y,
                    x: rect.left,
                };
                self.css(self.$item, { transform: defTransform });
                break;
            case 'onScroll':
                const wnd = self.getWindowData();
                const centerPercent = (wnd.y + wnd.height / 2 - self.itemData.y - self.itemData.height / 2) / (wnd.height / 2);
                const moveY = centerPercent * self.options.speedY;
                const moveX = centerPercent * self.options.speedX;
                let my = moveY;
                let mx = moveX;
                if (self.options.thresholdY !== null && moveY > self.options.thresholdY) my = 0;
                if (self.options.thresholdX !== null && moveX > self.options.thresholdX) mx = 0;
                self.css(self.$item, { transform: `translate3d(${mx}px,${my}px,0)` });
                break;
            case 'initImg':
            case 'isVisible':
            case 'clipContainer':
            case 'coverImage':
                return true;
            // no default
            }
            return def.apply(self, args);
        };
    });
}


/***/ }),

/***/ "./node_modules/jarallax/src/jarallax-video.esm.js":
/*!*********************************************************!*\
  !*** ./node_modules/jarallax/src/jarallax-video.esm.js ***!
  \*********************************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "default", function() { return jarallaxVideo; });
/* harmony import */ var video_worker__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! video-worker */ "./node_modules/video-worker/index.js");
/* harmony import */ var video_worker__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(video_worker__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var global__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! global */ "./node_modules/global/window.js");
/* harmony import */ var global__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(global__WEBPACK_IMPORTED_MODULE_1__);



function jarallaxVideo(jarallax = global__WEBPACK_IMPORTED_MODULE_1___default.a.jarallax) {
    if (typeof jarallax === 'undefined') {
        return;
    }

    const Jarallax = jarallax.constructor;

    // append video after init Jarallax
    const defInit = Jarallax.prototype.init;
    Jarallax.prototype.init = function () {
        const self = this;

        defInit.apply(self);

        if (self.video && !self.options.disableVideo()) {
            self.video.getVideo((video) => {
                const $parent = video.parentNode;
                self.css(video, {
                    position: self.image.position,
                    top: '0px',
                    left: '0px',
                    right: '0px',
                    bottom: '0px',
                    width: '100%',
                    height: '100%',
                    maxWidth: 'none',
                    maxHeight: 'none',
                    margin: 0,
                    zIndex: -1,
                });
                self.$video = video;
                self.image.$container.appendChild(video);

                // remove parent video element (created by VideoWorker)
                $parent.parentNode.removeChild($parent);
            });
        }
    };

    // cover video
    const defCoverImage = Jarallax.prototype.coverImage;
    Jarallax.prototype.coverImage = function () {
        const self = this;
        const imageData = defCoverImage.apply(self);
        const node = self.image.$item ? self.image.$item.nodeName : false;

        if (imageData && self.video && node && (node === 'IFRAME' || node === 'VIDEO')) {
            let h = imageData.image.height;
            let w = h * self.image.width / self.image.height;
            let ml = (imageData.container.width - w) / 2;
            let mt = imageData.image.marginTop;

            if (imageData.container.width > w) {
                w = imageData.container.width;
                h = w * self.image.height / self.image.width;
                ml = 0;
                mt += (imageData.image.height - h) / 2;
            }

            // add video height over than need to hide controls
            if (node === 'IFRAME') {
                h += 400;
                mt -= 200;
            }

            self.css(self.$video, {
                width: `${w}px`,
                marginLeft: `${ml}px`,
                height: `${h}px`,
                marginTop: `${mt}px`,
            });
        }

        return imageData;
    };

    // init video
    const defInitImg = Jarallax.prototype.initImg;
    Jarallax.prototype.initImg = function () {
        const self = this;
        const defaultResult = defInitImg.apply(self);

        if (!self.options.videoSrc) {
            self.options.videoSrc = self.$item.getAttribute('data-jarallax-video') || null;
        }

        if (self.options.videoSrc) {
            self.defaultInitImgResult = defaultResult;
            return true;
        }

        return defaultResult;
    };

    const defCanInitParallax = Jarallax.prototype.canInitParallax;
    Jarallax.prototype.canInitParallax = function () {
        const self = this;
        const defaultResult = defCanInitParallax.apply(self);

        if (!self.options.videoSrc) {
            return defaultResult;
        }

        const video = new video_worker__WEBPACK_IMPORTED_MODULE_0___default.a(self.options.videoSrc, {
            autoplay: true,
            loop: true,
            showContols: false,
            startTime: self.options.videoStartTime || 0,
            endTime: self.options.videoEndTime || 0,
            mute: self.options.videoVolume ? 0 : 1,
            volume: self.options.videoVolume || 0,
        });

        if (video.isValid()) {
            // if parallax will not be inited, we can add thumbnail on background.
            if (!defaultResult) {
                if (!self.defaultInitImgResult) {
                    video.getImageURL((url) => {
                        // save default user styles
                        const curStyle = self.$item.getAttribute('style');
                        if (curStyle) {
                            self.$item.setAttribute('data-jarallax-original-styles', curStyle);
                        }

                        // set new background
                        self.css(self.$item, {
                            'background-image': `url("${url}")`,
                            'background-position': 'center',
                            'background-size': 'cover',
                        });
                    });
                }

                // init video
            } else {
                video.on('ready', () => {
                    if (self.options.videoPlayOnlyVisible) {
                        const oldOnScroll = self.onScroll;
                        self.onScroll = function () {
                            oldOnScroll.apply(self);
                            if (self.isVisible()) {
                                video.play();
                            } else {
                                video.pause();
                            }
                        };
                    } else {
                        video.play();
                    }
                });

                video.on('started', () => {
                    self.image.$default_item = self.image.$item;
                    self.image.$item = self.$video;

                    // set video width and height
                    self.image.width = self.video.videoWidth || 1280;
                    self.image.height = self.video.videoHeight || 720;
                    self.options.imgWidth = self.image.width;
                    self.options.imgHeight = self.image.height;
                    self.coverImage();
                    self.clipContainer();
                    self.onScroll();

                    // hide image
                    if (self.image.$default_item) {
                        self.image.$default_item.style.display = 'none';
                    }
                });

                self.video = video;

                // set image if not exists
                if (!self.defaultInitImgResult) {
                    if (video.type !== 'local') {
                        video.getImageURL((url) => {
                            self.image.src = url;
                            self.init();
                        });

                        return false;
                    }

                    // set empty image on local video if not defined
                    self.image.src = 'data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7';
                    return true;
                }
            }
        }

        return defaultResult;
    };

    // Destroy video parallax
    const defDestroy = Jarallax.prototype.destroy;
    Jarallax.prototype.destroy = function () {
        const self = this;

        if (self.image.$default_item) {
            self.image.$item = self.image.$default_item;
            delete self.image.$default_item;
        }

        defDestroy.apply(self);
    };
}


/***/ }),

/***/ "./node_modules/jarallax/src/jarallax.esm.js":
/*!***************************************************!*\
  !*** ./node_modules/jarallax/src/jarallax.esm.js ***!
  \***************************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* WEBPACK VAR INJECTION */(function(global) {/* harmony import */ var lite_ready__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! lite-ready */ "./node_modules/lite-ready/liteready.js");
/* harmony import */ var lite_ready__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(lite_ready__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var rafl__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! rafl */ "./node_modules/rafl/index.js");
/* harmony import */ var rafl__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(rafl__WEBPACK_IMPORTED_MODULE_1__);
/* harmony import */ var global__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! global */ "./node_modules/global/window.js");
/* harmony import */ var global__WEBPACK_IMPORTED_MODULE_2___default = /*#__PURE__*/__webpack_require__.n(global__WEBPACK_IMPORTED_MODULE_2__);




const isIE = navigator.userAgent.indexOf('MSIE ') > -1 || navigator.userAgent.indexOf('Trident/') > -1 || navigator.userAgent.indexOf('Edge/') > -1;

const supportTransform = (() => {
    const prefixes = 'transform WebkitTransform MozTransform'.split(' ');
    const div = document.createElement('div');
    for (let i = 0; i < prefixes.length; i++) {
        if (div && div.style[prefixes[i]] !== undefined) {
            return prefixes[i];
        }
    }
    return false;
})();

// Window data
let wndW;
let wndH;
let wndY;
let forceResizeParallax = false;
let forceScrollParallax = false;
function updateWndVars(e) {
    wndW = global__WEBPACK_IMPORTED_MODULE_2__["window"].innerWidth || document.documentElement.clientWidth;
    wndH = global__WEBPACK_IMPORTED_MODULE_2__["window"].innerHeight || document.documentElement.clientHeight;
    if (typeof e === 'object' && (e.type === 'load' || e.type === 'dom-loaded')) {
        forceResizeParallax = true;
    }
}
updateWndVars();
global__WEBPACK_IMPORTED_MODULE_2__["window"].addEventListener('resize', updateWndVars);
global__WEBPACK_IMPORTED_MODULE_2__["window"].addEventListener('orientationchange', updateWndVars);
global__WEBPACK_IMPORTED_MODULE_2__["window"].addEventListener('load', updateWndVars);
lite_ready__WEBPACK_IMPORTED_MODULE_0___default()(() => {
    updateWndVars({
        type: 'dom-loaded',
    });
});

// list with all jarallax instances
// need to render all in one scroll/resize event
const jarallaxList = [];

// Animate if changed window size or scrolled page
let oldPageData = false;
function updateParallax() {
    if (!jarallaxList.length) {
        return;
    }

    if (global__WEBPACK_IMPORTED_MODULE_2__["window"].pageYOffset !== undefined) {
        wndY = global__WEBPACK_IMPORTED_MODULE_2__["window"].pageYOffset;
    } else {
        wndY = (document.documentElement || document.body.parentNode || document.body).scrollTop;
    }

    const isResized = forceResizeParallax || !oldPageData || oldPageData.width !== wndW || oldPageData.height !== wndH;
    const isScrolled = forceScrollParallax || isResized || !oldPageData || oldPageData.y !== wndY;

    forceResizeParallax = false;
    forceScrollParallax = false;

    if (isResized || isScrolled) {
        jarallaxList.forEach((item) => {
            if (isResized) {
                item.onResize();
            }
            if (isScrolled) {
                item.onScroll();
            }
        });

        oldPageData = {
            width: wndW,
            height: wndH,
            y: wndY,
        };
    }

    rafl__WEBPACK_IMPORTED_MODULE_1___default()(updateParallax);
}


// ResizeObserver
const resizeObserver = global.ResizeObserver ? new global.ResizeObserver((entry) => {
    if (entry && entry.length) {
        rafl__WEBPACK_IMPORTED_MODULE_1___default()(() => {
            entry.forEach((item) => {
                if (item.target && item.target.jarallax) {
                    if (!forceResizeParallax) {
                        item.target.jarallax.onResize();
                    }
                    forceScrollParallax = true;
                }
            });
        });
    }
}) : false;


let instanceID = 0;

// Jarallax class
class Jarallax {
    constructor(item, userOptions) {
        const self = this;

        self.instanceID = instanceID++;

        self.$item = item;

        self.defaults = {
            type: 'scroll', // type of parallax: scroll, scale, opacity, scale-opacity, scroll-opacity
            speed: 0.5, // supported value from -1 to 2
            imgSrc: null,
            imgElement: '.jarallax-img',
            imgSize: 'cover',
            imgPosition: '50% 50%',
            imgRepeat: 'no-repeat', // supported only for background, not for <img> tag
            keepImg: false, // keep <img> tag in it's default place
            elementInViewport: null,
            zIndex: -100,
            disableParallax: false,
            disableVideo: false,
            automaticResize: true, // use ResizeObserver to recalculate position and size of parallax image

            // video
            videoSrc: null,
            videoStartTime: 0,
            videoEndTime: 0,
            videoVolume: 0,
            videoPlayOnlyVisible: true,

            // events
            onScroll: null, // function(calculations) {}
            onInit: null, // function() {}
            onDestroy: null, // function() {}
            onCoverImage: null, // function() {}
        };

        // DEPRECATED: old data-options
        const deprecatedDataAttribute = self.$item.getAttribute('data-jarallax');
        const oldDataOptions = JSON.parse(deprecatedDataAttribute || '{}');
        if (deprecatedDataAttribute) {
            // eslint-disable-next-line no-console
            console.warn('Detected usage of deprecated data-jarallax JSON options, you should use pure data-attribute options. See info here - https://github.com/nk-o/jarallax/issues/53');
        }

        // prepare data-options
        const dataOptions = self.$item.dataset || {};
        const pureDataOptions = {};
        Object.keys(dataOptions).forEach((key) => {
            const loweCaseOption = key.substr(0, 1).toLowerCase() + key.substr(1);
            if (loweCaseOption && typeof self.defaults[loweCaseOption] !== 'undefined') {
                pureDataOptions[loweCaseOption] = dataOptions[key];
            }
        });

        self.options = self.extend({}, self.defaults, oldDataOptions, pureDataOptions, userOptions);
        self.pureOptions = self.extend({}, self.options);

        // prepare 'true' and 'false' strings to boolean
        Object.keys(self.options).forEach((key) => {
            if (self.options[key] === 'true') {
                self.options[key] = true;
            } else if (self.options[key] === 'false') {
                self.options[key] = false;
            }
        });

        // fix speed option [-1.0, 2.0]
        self.options.speed = Math.min(2, Math.max(-1, parseFloat(self.options.speed)));

        // deprecated noAndroid and noIos options
        if (self.options.noAndroid || self.options.noIos) {
            // eslint-disable-next-line no-console
            console.warn('Detected usage of deprecated noAndroid or noIos options, you should use disableParallax option. See info here - https://github.com/nk-o/jarallax/#disable-on-mobile-devices');

            // prepare fallback if disableParallax option is not used
            if (!self.options.disableParallax) {
                if (self.options.noIos && self.options.noAndroid) {
                    self.options.disableParallax = /iPad|iPhone|iPod|Android/;
                } else if (self.options.noIos) {
                    self.options.disableParallax = /iPad|iPhone|iPod/;
                } else if (self.options.noAndroid) {
                    self.options.disableParallax = /Android/;
                }
            }
        }

        // prepare disableParallax callback
        if (typeof self.options.disableParallax === 'string') {
            self.options.disableParallax = new RegExp(self.options.disableParallax);
        }
        if (self.options.disableParallax instanceof RegExp) {
            const disableParallaxRegexp = self.options.disableParallax;
            self.options.disableParallax = () => disableParallaxRegexp.test(navigator.userAgent);
        }
        if (typeof self.options.disableParallax !== 'function') {
            self.options.disableParallax = () => false;
        }

        // prepare disableVideo callback
        if (typeof self.options.disableVideo === 'string') {
            self.options.disableVideo = new RegExp(self.options.disableVideo);
        }
        if (self.options.disableVideo instanceof RegExp) {
            const disableVideoRegexp = self.options.disableVideo;
            self.options.disableVideo = () => disableVideoRegexp.test(navigator.userAgent);
        }
        if (typeof self.options.disableVideo !== 'function') {
            self.options.disableVideo = () => false;
        }

        // custom element to check if parallax in viewport
        let elementInVP = self.options.elementInViewport;
        // get first item from array
        if (elementInVP && typeof elementInVP === 'object' && typeof elementInVP.length !== 'undefined') {
            [elementInVP] = elementInVP;
        }
        // check if dom element
        if (!(elementInVP instanceof Element)) {
            elementInVP = null;
        }
        self.options.elementInViewport = elementInVP;

        self.image = {
            src: self.options.imgSrc || null,
            $container: null,
            useImgTag: false,

            // position fixed is needed for the most of browsers because absolute position have glitches
            // on MacOS with smooth scroll there is a huge lags with absolute position - https://github.com/nk-o/jarallax/issues/75
            // on mobile devices better scrolled with absolute position
            position: /iPad|iPhone|iPod|Android/.test(navigator.userAgent) ? 'absolute' : 'fixed',
        };

        if (self.initImg() && self.canInitParallax()) {
            self.init();
        }
    }

    // add styles to element
    css(el, styles) {
        if (typeof styles === 'string') {
            return global__WEBPACK_IMPORTED_MODULE_2__["window"].getComputedStyle(el).getPropertyValue(styles);
        }

        // add transform property with vendor prefix
        if (styles.transform && supportTransform) {
            styles[supportTransform] = styles.transform;
        }

        Object.keys(styles).forEach((key) => {
            el.style[key] = styles[key];
        });
        return el;
    }

    // Extend like jQuery.extend
    extend(out) {
        out = out || {};
        Object.keys(arguments).forEach((i) => {
            if (!arguments[i]) {
                return;
            }
            Object.keys(arguments[i]).forEach((key) => {
                out[key] = arguments[i][key];
            });
        });
        return out;
    }

    // get window size and scroll position. Useful for extensions
    getWindowData() {
        return {
            width: wndW,
            height: wndH,
            y: wndY,
        };
    }

    // Jarallax functions
    initImg() {
        const self = this;

        // find image element
        let $imgElement = self.options.imgElement;
        if ($imgElement && typeof $imgElement === 'string') {
            $imgElement = self.$item.querySelector($imgElement);
        }
        // check if dom element
        if (!($imgElement instanceof Element)) {
            $imgElement = null;
        }

        if ($imgElement) {
            if (self.options.keepImg) {
                self.image.$item = $imgElement.cloneNode(true);
            } else {
                self.image.$item = $imgElement;
                self.image.$itemParent = $imgElement.parentNode;
            }
            self.image.useImgTag = true;
        }

        // true if there is img tag
        if (self.image.$item) {
            return true;
        }

        // get image src
        if (self.image.src === null) {
            self.image.src = self.css(self.$item, 'background-image').replace(/^url\(['"]?/g, '').replace(/['"]?\)$/g, '');
        }
        return !(!self.image.src || self.image.src === 'none');
    }

    canInitParallax() {
        return supportTransform && !this.options.disableParallax();
    }

    init() {
        const self = this;
        const containerStyles = {
            position: 'absolute',
            top: 0,
            left: 0,
            width: '100%',
            height: '100%',
            overflow: 'hidden',
            pointerEvents: 'none',
        };
        let imageStyles = {};

        if (!self.options.keepImg) {
            // save default user styles
            const curStyle = self.$item.getAttribute('style');
            if (curStyle) {
                self.$item.setAttribute('data-jarallax-original-styles', curStyle);
            }
            if (self.image.useImgTag) {
                const curImgStyle = self.image.$item.getAttribute('style');
                if (curImgStyle) {
                    self.image.$item.setAttribute('data-jarallax-original-styles', curImgStyle);
                }
            }
        }

        // set relative position and z-index to the parent
        if (self.css(self.$item, 'position') === 'static') {
            self.css(self.$item, {
                position: 'relative',
            });
        }
        if (self.css(self.$item, 'z-index') === 'auto') {
            self.css(self.$item, {
                zIndex: 0,
            });
        }

        // container for parallax image
        self.image.$container = document.createElement('div');
        self.css(self.image.$container, containerStyles);
        self.css(self.image.$container, {
            'z-index': self.options.zIndex,
        });

        // fix for IE https://github.com/nk-o/jarallax/issues/110
        if (isIE) {
            self.css(self.image.$container, {
                opacity: 0.9999,
            });
        }

        self.image.$container.setAttribute('id', `jarallax-container-${self.instanceID}`);
        self.$item.appendChild(self.image.$container);

        // use img tag
        if (self.image.useImgTag) {
            imageStyles = self.extend({
                'object-fit': self.options.imgSize,
                'object-position': self.options.imgPosition,
                // support for plugin https://github.com/bfred-it/object-fit-images
                'font-family': `object-fit: ${self.options.imgSize}; object-position: ${self.options.imgPosition};`,
                'max-width': 'none',
            }, containerStyles, imageStyles);

        // use div with background image
        } else {
            self.image.$item = document.createElement('div');
            if (self.image.src) {
                imageStyles = self.extend({
                    'background-position': self.options.imgPosition,
                    'background-size': self.options.imgSize,
                    'background-repeat': self.options.imgRepeat,
                    'background-image': `url("${self.image.src}")`,
                }, containerStyles, imageStyles);
            }
        }

        if (self.options.type === 'opacity' || self.options.type === 'scale' || self.options.type === 'scale-opacity' || self.options.speed === 1) {
            self.image.position = 'absolute';
        }

        // check if one of parents have transform style (without this check, scroll transform will be inverted if used parallax with position fixed)
        // discussion - https://github.com/nk-o/jarallax/issues/9
        if (self.image.position === 'fixed') {
            let parentWithTransform = 0;
            let $itemParents = self.$item;
            while ($itemParents !== null && $itemParents !== document && parentWithTransform === 0) {
                const parentTransform = self.css($itemParents, '-webkit-transform') || self.css($itemParents, '-moz-transform') || self.css($itemParents, 'transform');
                if (parentTransform && parentTransform !== 'none') {
                    parentWithTransform = 1;
                    self.image.position = 'absolute';
                }
                $itemParents = $itemParents.parentNode;
            }
        }

        // add position to parallax block
        imageStyles.position = self.image.position;

        // insert parallax image
        self.css(self.image.$item, imageStyles);
        self.image.$container.appendChild(self.image.$item);

        // set initial position and size
        self.onResize();
        self.onScroll(true);

        // ResizeObserver
        if (self.options.automaticResize && resizeObserver) {
            resizeObserver.observe(self.$item);
        }

        // call onInit event
        if (self.options.onInit) {
            self.options.onInit.call(self);
        }

        // remove default user background
        if (self.css(self.$item, 'background-image') !== 'none') {
            self.css(self.$item, {
                'background-image': 'none',
            });
        }

        self.addToParallaxList();
    }

    // add to parallax instances list
    addToParallaxList() {
        jarallaxList.push(this);

        if (jarallaxList.length === 1) {
            updateParallax();
        }
    }

    // remove from parallax instances list
    removeFromParallaxList() {
        const self = this;

        jarallaxList.forEach((item, key) => {
            if (item.instanceID === self.instanceID) {
                jarallaxList.splice(key, 1);
            }
        });
    }

    destroy() {
        const self = this;

        self.removeFromParallaxList();

        // return styles on container as before jarallax init
        const originalStylesTag = self.$item.getAttribute('data-jarallax-original-styles');
        self.$item.removeAttribute('data-jarallax-original-styles');
        // null occurs if there is no style tag before jarallax init
        if (!originalStylesTag) {
            self.$item.removeAttribute('style');
        } else {
            self.$item.setAttribute('style', originalStylesTag);
        }

        if (self.image.useImgTag) {
            // return styles on img tag as before jarallax init
            const originalStylesImgTag = self.image.$item.getAttribute('data-jarallax-original-styles');
            self.image.$item.removeAttribute('data-jarallax-original-styles');
            // null occurs if there is no style tag before jarallax init
            if (!originalStylesImgTag) {
                self.image.$item.removeAttribute('style');
            } else {
                self.image.$item.setAttribute('style', originalStylesTag);
            }

            // move img tag to its default position
            if (self.image.$itemParent) {
                self.image.$itemParent.appendChild(self.image.$item);
            }
        }

        // remove additional dom elements
        if (self.$clipStyles) {
            self.$clipStyles.parentNode.removeChild(self.$clipStyles);
        }
        if (self.image.$container) {
            self.image.$container.parentNode.removeChild(self.image.$container);
        }

        // call onDestroy event
        if (self.options.onDestroy) {
            self.options.onDestroy.call(self);
        }

        // delete jarallax from item
        delete self.$item.jarallax;
    }

    // it will remove some image overlapping
    // overlapping occur due to an image position fixed inside absolute position element
    clipContainer() {
        // needed only when background in fixed position
        if (this.image.position !== 'fixed') {
            return;
        }

        const self = this;
        const rect = self.image.$container.getBoundingClientRect();
        const { width, height } = rect;

        if (!self.$clipStyles) {
            self.$clipStyles = document.createElement('style');
            self.$clipStyles.setAttribute('type', 'text/css');
            self.$clipStyles.setAttribute('id', `jarallax-clip-${self.instanceID}`);
            const head = document.head || document.getElementsByTagName('head')[0];
            head.appendChild(self.$clipStyles);
        }

        const styles = `#jarallax-container-${self.instanceID} {
           clip: rect(0 ${width}px ${height}px 0);
           clip: rect(0, ${width}px, ${height}px, 0);
        }`;

        // add clip styles inline (this method need for support IE8 and less browsers)
        if (self.$clipStyles.styleSheet) {
            self.$clipStyles.styleSheet.cssText = styles;
        } else {
            self.$clipStyles.innerHTML = styles;
        }
    }

    coverImage() {
        const self = this;

        const rect = self.image.$container.getBoundingClientRect();
        const contH = rect.height;
        const { speed } = self.options;
        const isScroll = self.options.type === 'scroll' || self.options.type === 'scroll-opacity';
        let scrollDist = 0;
        let resultH = contH;
        let resultMT = 0;

        // scroll parallax
        if (isScroll) {
            // scroll distance and height for image
            if (speed < 0) {
                scrollDist = speed * Math.max(contH, wndH);

                if (wndH < contH) {
                    scrollDist -= speed * (contH - wndH);
                }
            } else {
                scrollDist = speed * (contH + wndH);
            }

            // size for scroll parallax
            if (speed > 1) {
                resultH = Math.abs(scrollDist - wndH);
            } else if (speed < 0) {
                resultH = scrollDist / speed + Math.abs(scrollDist);
            } else {
                resultH += (wndH - contH) * (1 - speed);
            }

            scrollDist /= 2;
        }

        // store scroll distance
        self.parallaxScrollDistance = scrollDist;

        // vertical center
        if (isScroll) {
            resultMT = (wndH - resultH) / 2;
        } else {
            resultMT = (contH - resultH) / 2;
        }

        // apply result to item
        self.css(self.image.$item, {
            height: `${resultH}px`,
            marginTop: `${resultMT}px`,
            left: self.image.position === 'fixed' ? `${rect.left}px` : '0',
            width: `${rect.width}px`,
        });

        // call onCoverImage event
        if (self.options.onCoverImage) {
            self.options.onCoverImage.call(self);
        }

        // return some useful data. Used in the video cover function
        return {
            image: {
                height: resultH,
                marginTop: resultMT,
            },
            container: rect,
        };
    }

    isVisible() {
        return this.isElementInViewport || false;
    }

    onScroll(force) {
        const self = this;

        const rect = self.$item.getBoundingClientRect();
        const contT = rect.top;
        const contH = rect.height;
        const styles = {};

        // check if in viewport
        let viewportRect = rect;
        if (self.options.elementInViewport) {
            viewportRect = self.options.elementInViewport.getBoundingClientRect();
        }
        self.isElementInViewport = viewportRect.bottom >= 0
            && viewportRect.right >= 0
            && viewportRect.top <= wndH
            && viewportRect.left <= wndW;

        // stop calculations if item is not in viewport
        if (force ? false : !self.isElementInViewport) {
            return;
        }

        // calculate parallax helping variables
        const beforeTop = Math.max(0, contT);
        const beforeTopEnd = Math.max(0, contH + contT);
        const afterTop = Math.max(0, -contT);
        const beforeBottom = Math.max(0, contT + contH - wndH);
        const beforeBottomEnd = Math.max(0, contH - (contT + contH - wndH));
        const afterBottom = Math.max(0, -contT + wndH - contH);
        const fromViewportCenter = 1 - 2 * (wndH - contT) / (wndH + contH);

        // calculate on how percent of section is visible
        let visiblePercent = 1;
        if (contH < wndH) {
            visiblePercent = 1 - (afterTop || beforeBottom) / contH;
        } else if (beforeTopEnd <= wndH) {
            visiblePercent = beforeTopEnd / wndH;
        } else if (beforeBottomEnd <= wndH) {
            visiblePercent = beforeBottomEnd / wndH;
        }

        // opacity
        if (self.options.type === 'opacity' || self.options.type === 'scale-opacity' || self.options.type === 'scroll-opacity') {
            styles.transform = 'translate3d(0,0,0)';
            styles.opacity = visiblePercent;
        }

        // scale
        if (self.options.type === 'scale' || self.options.type === 'scale-opacity') {
            let scale = 1;
            if (self.options.speed < 0) {
                scale -= self.options.speed * visiblePercent;
            } else {
                scale += self.options.speed * (1 - visiblePercent);
            }
            styles.transform = `scale(${scale}) translate3d(0,0,0)`;
        }

        // scroll
        if (self.options.type === 'scroll' || self.options.type === 'scroll-opacity') {
            let positionY = self.parallaxScrollDistance * fromViewportCenter;

            // fix if parallax block in absolute position
            if (self.image.position === 'absolute') {
                positionY -= contT;
            }

            styles.transform = `translate3d(0,${positionY}px,0)`;
        }

        self.css(self.image.$item, styles);

        // call onScroll event
        if (self.options.onScroll) {
            self.options.onScroll.call(self, {
                section: rect,

                beforeTop,
                beforeTopEnd,
                afterTop,
                beforeBottom,
                beforeBottomEnd,
                afterBottom,

                visiblePercent,
                fromViewportCenter,
            });
        }
    }

    onResize() {
        this.coverImage();
        this.clipContainer();
    }
}


// global definition
const plugin = function (items) {
    // check for dom element
    // thanks: http://stackoverflow.com/questions/384286/javascript-isdom-how-do-you-check-if-a-javascript-object-is-a-dom-object
    if (typeof HTMLElement === 'object' ? items instanceof HTMLElement : items && typeof items === 'object' && items !== null && items.nodeType === 1 && typeof items.nodeName === 'string') {
        items = [items];
    }

    const options = arguments[1];
    const args = Array.prototype.slice.call(arguments, 2);
    const len = items.length;
    let k = 0;
    let ret;

    for (k; k < len; k++) {
        if (typeof options === 'object' || typeof options === 'undefined') {
            if (!items[k].jarallax) {
                items[k].jarallax = new Jarallax(items[k], options);
            }
        } else if (items[k].jarallax) {
            // eslint-disable-next-line prefer-spread
            ret = items[k].jarallax[options].apply(items[k].jarallax, args);
        }
        if (typeof ret !== 'undefined') {
            return ret;
        }
    }

    return items;
};
plugin.constructor = Jarallax;

/* harmony default export */ __webpack_exports__["default"] = (plugin);

/* WEBPACK VAR INJECTION */}.call(this, __webpack_require__(/*! ./../../webpack/buildin/global.js */ "./node_modules/webpack/buildin/global.js")))

/***/ }),

/***/ "./node_modules/lite-ready/liteready.js":
/*!**********************************************!*\
  !*** ./node_modules/lite-ready/liteready.js ***!
  \**********************************************/
/*! no static exports found */
/***/ (function(module, exports) {

module.exports = function (callback) {

	if (document.readyState === 'complete' || document.readyState === 'interactive') {
		// Already ready or interactive, execute callback
		callback.call();
	}
	else if (document.attachEvent) {
		// Old browsers
		document.attachEvent('onreadystatechange', function () {
			if (document.readyState === 'interactive')
				callback.call();
		});
	}
	else if (document.addEventListener) {
		// Modern browsers
		document.addEventListener('DOMContentLoaded', callback);
	}
}


/***/ }),

/***/ "./node_modules/rafl/index.js":
/*!************************************!*\
  !*** ./node_modules/rafl/index.js ***!
  \************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

var global = __webpack_require__(/*! global */ "./node_modules/global/window.js")

/**
 * `requestAnimationFrame()`
 */

var request = global.requestAnimationFrame
  || global.webkitRequestAnimationFrame
  || global.mozRequestAnimationFrame
  || fallback

var prev = +new Date
function fallback (fn) {
  var curr = +new Date
  var ms = Math.max(0, 16 - (curr - prev))
  var req = setTimeout(fn, ms)
  return prev = curr, req
}

/**
 * `cancelAnimationFrame()`
 */

var cancel = global.cancelAnimationFrame
  || global.webkitCancelAnimationFrame
  || global.mozCancelAnimationFrame
  || clearTimeout

if (Function.prototype.bind) {
  request = request.bind(global)
  cancel = cancel.bind(global)
}

exports = module.exports = request
exports.cancel = cancel


/***/ }),

/***/ "./node_modules/video-worker/index.js":
/*!********************************************!*\
  !*** ./node_modules/video-worker/index.js ***!
  \********************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

module.exports = __webpack_require__(/*! ./src/video-worker.esm */ "./node_modules/video-worker/src/video-worker.esm.js");


/***/ }),

/***/ "./node_modules/video-worker/src/video-worker.esm.js":
/*!***********************************************************!*\
  !*** ./node_modules/video-worker/src/video-worker.esm.js ***!
  \***********************************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "default", function() { return VideoWorker; });
// Deferred
// thanks http://stackoverflow.com/questions/18096715/implement-deferred-object-without-using-jquery
function Deferred() {
    this._done = [];
    this._fail = [];
}
Deferred.prototype = {
    execute(list, args) {
        let i = list.length;
        args = Array.prototype.slice.call(args);
        while (i--) {
            list[i].apply(null, args);
        }
    },
    resolve() {
        this.execute(this._done, arguments);
    },
    reject() {
        this.execute(this._fail, arguments);
    },
    done(callback) {
        this._done.push(callback);
    },
    fail(callback) {
        this._fail.push(callback);
    },
};

let ID = 0;
let YoutubeAPIadded = 0;
let VimeoAPIadded = 0;
let loadingYoutubePlayer = 0;
let loadingVimeoPlayer = 0;
const loadingYoutubeDefer = new Deferred();
const loadingVimeoDefer = new Deferred();

class VideoWorker {
    constructor(url, options) {
        const self = this;

        self.url = url;

        self.options_default = {
            autoplay: false,
            loop: false,
            mute: false,
            volume: 100,
            showContols: true,

            // start / end video time in seconds
            startTime: 0,
            endTime: 0,
        };

        self.options = self.extend({}, self.options_default, options);

        // check URL
        self.videoID = self.parseURL(url);

        // init
        if (self.videoID) {
            self.ID = ID++;
            self.loadAPI();
            self.init();
        }
    }

    // Extend like jQuery.extend
    extend(out) {
        out = out || {};
        Object.keys(arguments).forEach((i) => {
            if (!arguments[i]) {
                return;
            }
            Object.keys(arguments[i]).forEach((key) => {
                out[key] = arguments[i][key];
            });
        });
        return out;
    }

    parseURL(url) {
        // parse youtube ID
        function getYoutubeID(ytUrl) {
            // eslint-disable-next-line no-useless-escape
            const regExp = /.*(?:youtu.be\/|v\/|u\/\w\/|embed\/|watch\?v=)([^#\&\?]*).*/;
            const match = ytUrl.match(regExp);
            return match && match[1].length === 11 ? match[1] : false;
        }

        // parse vimeo ID
        function getVimeoID(vmUrl) {
            // eslint-disable-next-line no-useless-escape
            const regExp = /https?:\/\/(?:www\.|player\.)?vimeo.com\/(?:channels\/(?:\w+\/)?|groups\/([^\/]*)\/videos\/|album\/(\d+)\/video\/|video\/|)(\d+)(?:$|\/|\?)/;
            const match = vmUrl.match(regExp);
            return match && match[3] ? match[3] : false;
        }

        // parse local string
        function getLocalVideos(locUrl) {
            // eslint-disable-next-line no-useless-escape
            const videoFormats = locUrl.split(/,(?=mp4\:|webm\:|ogv\:|ogg\:)/);
            const result = {};
            let ready = 0;
            videoFormats.forEach((val) => {
                // eslint-disable-next-line no-useless-escape
                const match = val.match(/^(mp4|webm|ogv|ogg)\:(.*)/);
                if (match && match[1] && match[2]) {
                    // eslint-disable-next-line prefer-destructuring
                    result[match[1] === 'ogv' ? 'ogg' : match[1]] = match[2];
                    ready = 1;
                }
            });
            return ready ? result : false;
        }

        const Youtube = getYoutubeID(url);
        const Vimeo = getVimeoID(url);
        const Local = getLocalVideos(url);

        if (Youtube) {
            this.type = 'youtube';
            return Youtube;
        } else if (Vimeo) {
            this.type = 'vimeo';
            return Vimeo;
        } else if (Local) {
            this.type = 'local';
            return Local;
        }

        return false;
    }

    isValid() {
        return !!this.videoID;
    }

    // events
    on(name, callback) {
        this.userEventsList = this.userEventsList || [];

        // add new callback in events list
        (this.userEventsList[name] || (this.userEventsList[name] = [])).push(callback);
    }
    off(name, callback) {
        if (!this.userEventsList || !this.userEventsList[name]) {
            return;
        }

        if (!callback) {
            delete this.userEventsList[name];
        } else {
            this.userEventsList[name].forEach((val, key) => {
                if (val === callback) {
                    this.userEventsList[name][key] = false;
                }
            });
        }
    }
    fire(name) {
        const args = [].slice.call(arguments, 1);
        if (this.userEventsList && typeof this.userEventsList[name] !== 'undefined') {
            this.userEventsList[name].forEach((val) => {
                // call with all arguments
                if (val) {
                    val.apply(this, args);
                }
            });
        }
    }

    play(start) {
        const self = this;
        if (!self.player) {
            return;
        }

        if (self.type === 'youtube' && self.player.playVideo) {
            if (typeof start !== 'undefined') {
                self.player.seekTo(start || 0);
            }
            if (YT.PlayerState.PLAYING !== self.player.getPlayerState()) {
                self.player.playVideo();
            }
        }

        if (self.type === 'vimeo') {
            if (typeof start !== 'undefined') {
                self.player.setCurrentTime(start);
            }
            self.player.getPaused().then((paused) => {
                if (paused) {
                    self.player.play();
                }
            });
        }

        if (self.type === 'local') {
            if (typeof start !== 'undefined') {
                self.player.currentTime = start;
            }
            if (self.player.paused) {
                self.player.play();
            }
        }
    }

    pause() {
        const self = this;
        if (!self.player) {
            return;
        }

        if (self.type === 'youtube' && self.player.pauseVideo) {
            if (YT.PlayerState.PLAYING === self.player.getPlayerState()) {
                self.player.pauseVideo();
            }
        }

        if (self.type === 'vimeo') {
            self.player.getPaused().then((paused) => {
                if (!paused) {
                    self.player.pause();
                }
            });
        }

        if (self.type === 'local') {
            if (!self.player.paused) {
                self.player.pause();
            }
        }
    }

    mute() {
        const self = this;
        if (!self.player) {
            return;
        }

        if (self.type === 'youtube' && self.player.mute) {
            self.player.mute();
        }

        if (self.type === 'vimeo' && self.player.setVolume) {
            self.player.setVolume(0);
        }

        if (self.type === 'local') {
            self.$video.muted = true;
        }
    }

    unmute() {
        const self = this;
        if (!self.player) {
            return;
        }

        if (self.type === 'youtube' && self.player.mute) {
            self.player.unMute();
        }

        if (self.type === 'vimeo' && self.player.setVolume) {
            self.player.setVolume(self.options.volume);
        }

        if (self.type === 'local') {
            self.$video.muted = false;
        }
    }

    setVolume(volume = false) {
        const self = this;
        if (!self.player || !volume) {
            return;
        }

        if (self.type === 'youtube' && self.player.setVolume) {
            self.player.setVolume(volume);
        }

        if (self.type === 'vimeo' && self.player.setVolume) {
            self.player.setVolume(volume);
        }

        if (self.type === 'local') {
            self.$video.volume = volume / 100;
        }
    }

    getVolume(callback) {
        const self = this;
        if (!self.player) {
            callback(false);
            return;
        }

        if (self.type === 'youtube' && self.player.getVolume) {
            callback(self.player.getVolume());
        }

        if (self.type === 'vimeo' && self.player.getVolume) {
            self.player.getVolume().then((volume) => {
                callback(volume);
            });
        }

        if (self.type === 'local') {
            callback(self.$video.volume * 100);
        }
    }

    getMuted(callback) {
        const self = this;
        if (!self.player) {
            callback(null);
            return;
        }

        if (self.type === 'youtube' && self.player.isMuted) {
            callback(self.player.isMuted());
        }

        if (self.type === 'vimeo' && self.player.getVolume) {
            self.player.getVolume().then((volume) => {
                callback(!!volume);
            });
        }

        if (self.type === 'local') {
            callback(self.$video.muted);
        }
    }

    getImageURL(callback) {
        const self = this;

        if (self.videoImage) {
            callback(self.videoImage);
            return;
        }

        if (self.type === 'youtube') {
            const availableSizes = [
                'maxresdefault',
                'sddefault',
                'hqdefault',
                '0',
            ];
            let step = 0;

            const tempImg = new Image();
            tempImg.onload = function () {
                // if no thumbnail, youtube add their own image with width = 120px
                if ((this.naturalWidth || this.width) !== 120 || step === availableSizes.length - 1) {
                    // ok
                    self.videoImage = `https://img.youtube.com/vi/${self.videoID}/${availableSizes[step]}.jpg`;
                    callback(self.videoImage);
                } else {
                    // try another size
                    step++;
                    this.src = `https://img.youtube.com/vi/${self.videoID}/${availableSizes[step]}.jpg`;
                }
            };
            tempImg.src = `https://img.youtube.com/vi/${self.videoID}/${availableSizes[step]}.jpg`;
        }

        if (self.type === 'vimeo') {
            let request = new XMLHttpRequest();
            request.open('GET', `https://vimeo.com/api/v2/video/${self.videoID}.json`, true);
            request.onreadystatechange = function () {
                if (this.readyState === 4) {
                    if (this.status >= 200 && this.status < 400) {
                        // Success!
                        const response = JSON.parse(this.responseText);
                        self.videoImage = response[0].thumbnail_large;
                        callback(self.videoImage);
                    } else {
                        // Error :(
                    }
                }
            };
            request.send();
            request = null;
        }
    }

    // fallback to the old version.
    getIframe(callback) {
        this.getVideo(callback);
    }

    getVideo(callback) {
        const self = this;

        // return generated video block
        if (self.$video) {
            callback(self.$video);
            return;
        }

        // generate new video block
        self.onAPIready(() => {
            let hiddenDiv;
            if (!self.$video) {
                hiddenDiv = document.createElement('div');
                hiddenDiv.style.display = 'none';
            }

            // Youtube
            if (self.type === 'youtube') {
                self.playerOptions = {};
                self.playerOptions.videoId = self.videoID;
                self.playerOptions.playerVars = {
                    autohide: 1,
                    rel: 0,
                    autoplay: 0,
                    // autoplay enable on mobile devices
                    playsinline: 1,
                };

                // hide controls
                if (!self.options.showContols) {
                    self.playerOptions.playerVars.iv_load_policy = 3;
                    self.playerOptions.playerVars.modestbranding = 1;
                    self.playerOptions.playerVars.controls = 0;
                    self.playerOptions.playerVars.showinfo = 0;
                    self.playerOptions.playerVars.disablekb = 1;
                }

                // events
                let ytStarted;
                let ytProgressInterval;
                self.playerOptions.events = {
                    onReady(e) {
                        // mute
                        if (self.options.mute) {
                            e.target.mute();
                        } else if (self.options.volume) {
                            e.target.setVolume(self.options.volume);
                        }

                        // autoplay
                        if (self.options.autoplay) {
                            self.play(self.options.startTime);
                        }
                        self.fire('ready', e);

                        // volumechange
                        setInterval(() => {
                            self.getVolume((volume) => {
                                if (self.options.volume !== volume) {
                                    self.options.volume = volume;
                                    self.fire('volumechange', e);
                                }
                            });
                        }, 150);
                    },
                    onStateChange(e) {
                        // loop
                        if (self.options.loop && e.data === YT.PlayerState.ENDED) {
                            self.play(self.options.startTime);
                        }
                        if (!ytStarted && e.data === YT.PlayerState.PLAYING) {
                            ytStarted = 1;
                            self.fire('started', e);
                        }
                        if (e.data === YT.PlayerState.PLAYING) {
                            self.fire('play', e);
                        }
                        if (e.data === YT.PlayerState.PAUSED) {
                            self.fire('pause', e);
                        }
                        if (e.data === YT.PlayerState.ENDED) {
                            self.fire('ended', e);
                        }

                        // progress check
                        if (e.data === YT.PlayerState.PLAYING) {
                            ytProgressInterval = setInterval(() => {
                                self.fire('timeupdate', e);

                                // check for end of video and play again or stop
                                if (self.options.endTime && self.player.getCurrentTime() >= self.options.endTime) {
                                    if (self.options.loop) {
                                        self.play(self.options.startTime);
                                    } else {
                                        self.pause();
                                    }
                                }
                            }, 150);
                        } else {
                            clearInterval(ytProgressInterval);
                        }
                    },
                };

                const firstInit = !self.$video;
                if (firstInit) {
                    const div = document.createElement('div');
                    div.setAttribute('id', self.playerID);
                    hiddenDiv.appendChild(div);
                    document.body.appendChild(hiddenDiv);
                }
                self.player = self.player || new window.YT.Player(self.playerID, self.playerOptions);
                if (firstInit) {
                    self.$video = document.getElementById(self.playerID);

                    // get video width and height
                    self.videoWidth = parseInt(self.$video.getAttribute('width'), 10) || 1280;
                    self.videoHeight = parseInt(self.$video.getAttribute('height'), 10) || 720;
                }
            }

            // Vimeo
            if (self.type === 'vimeo') {
                self.playerOptions = {
                    id: self.videoID,
                    autopause: 0,
                    transparent: 0,
                    autoplay: self.options.autoplay ? 1 : 0,
                    loop: self.options.loop ? 1 : 0,
                    muted: self.options.mute ? 1 : 0,
                };

                if (self.options.volume) {
                    self.playerOptions.volume = self.options.volume;
                }

                // hide controls
                if (!self.options.showContols) {
                    self.playerOptions.badge = 0;
                    self.playerOptions.byline = 0;
                    self.playerOptions.portrait = 0;
                    self.playerOptions.title = 0;
                }


                if (!self.$video) {
                    let playerOptionsString = '';
                    Object.keys(self.playerOptions).forEach((key) => {
                        if (playerOptionsString !== '') {
                            playerOptionsString += '&';
                        }
                        playerOptionsString += `${key}=${encodeURIComponent(self.playerOptions[key])}`;
                    });

                    // we need to create iframe manually because when we create it using API
                    // js events won't triggers after iframe moved to another place
                    self.$video = document.createElement('iframe');
                    self.$video.setAttribute('id', self.playerID);
                    self.$video.setAttribute('src', `https://player.vimeo.com/video/${self.videoID}?${playerOptionsString}`);
                    self.$video.setAttribute('frameborder', '0');
                    self.$video.setAttribute('mozallowfullscreen', '');
                    self.$video.setAttribute('allowfullscreen', '');

                    hiddenDiv.appendChild(self.$video);
                    document.body.appendChild(hiddenDiv);
                }
                self.player = self.player || new Vimeo.Player(self.$video, self.playerOptions);

                // set current time for autoplay
                if (self.options.startTime && self.options.autoplay) {
                    self.player.setCurrentTime(self.options.startTime);
                }

                // get video width and height
                self.player.getVideoWidth().then((width) => {
                    self.videoWidth = width || 1280;
                });
                self.player.getVideoHeight().then((height) => {
                    self.videoHeight = height || 720;
                });

                // events
                let vmStarted;
                self.player.on('timeupdate', (e) => {
                    if (!vmStarted) {
                        self.fire('started', e);
                        vmStarted = 1;
                    }

                    self.fire('timeupdate', e);

                    // check for end of video and play again or stop
                    if (self.options.endTime) {
                        if (self.options.endTime && e.seconds >= self.options.endTime) {
                            if (self.options.loop) {
                                self.play(self.options.startTime);
                            } else {
                                self.pause();
                            }
                        }
                    }
                });
                self.player.on('play', (e) => {
                    self.fire('play', e);

                    // check for the start time and start with it
                    if (self.options.startTime && e.seconds === 0) {
                        self.play(self.options.startTime);
                    }
                });
                self.player.on('pause', (e) => {
                    self.fire('pause', e);
                });
                self.player.on('ended', (e) => {
                    self.fire('ended', e);
                });
                self.player.on('loaded', (e) => {
                    self.fire('ready', e);
                });
                self.player.on('volumechange', (e) => {
                    self.fire('volumechange', e);
                });
            }

            // Local
            function addSourceToLocal(element, src, type) {
                const source = document.createElement('source');
                source.src = src;
                source.type = type;
                element.appendChild(source);
            }
            if (self.type === 'local') {
                if (!self.$video) {
                    self.$video = document.createElement('video');

                    // show controls
                    if (self.options.showContols) {
                        self.$video.controls = true;
                    }

                    // mute
                    if (self.options.mute) {
                        self.$video.muted = true;
                    } else if (self.$video.volume) {
                        self.$video.volume = self.options.volume / 100;
                    }

                    // loop
                    if (self.options.loop) {
                        self.$video.loop = true;
                    }

                    // autoplay enable on mobile devices
                    self.$video.setAttribute('playsinline', '');
                    self.$video.setAttribute('webkit-playsinline', '');

                    self.$video.setAttribute('id', self.playerID);
                    hiddenDiv.appendChild(self.$video);
                    document.body.appendChild(hiddenDiv);

                    Object.keys(self.videoID).forEach((key) => {
                        addSourceToLocal(self.$video, self.videoID[key], `video/${key}`);
                    });
                }

                self.player = self.player || self.$video;

                let locStarted;
                self.player.addEventListener('playing', (e) => {
                    if (!locStarted) {
                        self.fire('started', e);
                    }
                    locStarted = 1;
                });
                self.player.addEventListener('timeupdate', function (e) {
                    self.fire('timeupdate', e);

                    // check for end of video and play again or stop
                    if (self.options.endTime) {
                        if (self.options.endTime && this.currentTime >= self.options.endTime) {
                            if (self.options.loop) {
                                self.play(self.options.startTime);
                            } else {
                                self.pause();
                            }
                        }
                    }
                });
                self.player.addEventListener('play', (e) => {
                    self.fire('play', e);
                });
                self.player.addEventListener('pause', (e) => {
                    self.fire('pause', e);
                });
                self.player.addEventListener('ended', (e) => {
                    self.fire('ended', e);
                });
                self.player.addEventListener('loadedmetadata', function () {
                    // get video width and height
                    self.videoWidth = this.videoWidth || 1280;
                    self.videoHeight = this.videoHeight || 720;

                    self.fire('ready');

                    // autoplay
                    if (self.options.autoplay) {
                        self.play(self.options.startTime);
                    }
                });
                self.player.addEventListener('volumechange', (e) => {
                    self.getVolume((volume) => {
                        self.options.volume = volume;
                    });
                    self.fire('volumechange', e);
                });
            }
            callback(self.$video);
        });
    }

    init() {
        const self = this;

        self.playerID = `VideoWorker-${self.ID}`;
    }

    loadAPI() {
        const self = this;

        if (YoutubeAPIadded && VimeoAPIadded) {
            return;
        }

        let src = '';

        // load Youtube API
        if (self.type === 'youtube' && !YoutubeAPIadded) {
            YoutubeAPIadded = 1;
            src = 'https://www.youtube.com/iframe_api';
        }

        // load Vimeo API
        if (self.type === 'vimeo' && !VimeoAPIadded) {
            VimeoAPIadded = 1;
            src = 'https://player.vimeo.com/api/player.js';
        }

        if (!src) {
            return;
        }

        // add script in head section
        let tag = document.createElement('script');
        let head = document.getElementsByTagName('head')[0];
        tag.src = src;

        head.appendChild(tag);

        head = null;
        tag = null;
    }

    onAPIready(callback) {
        const self = this;

        // Youtube
        if (self.type === 'youtube') {
            // Listen for global YT player callback
            if ((typeof YT === 'undefined' || YT.loaded === 0) && !loadingYoutubePlayer) {
                // Prevents Ready event from being called twice
                loadingYoutubePlayer = 1;

                // Creates deferred so, other players know when to wait.
                window.onYouTubeIframeAPIReady = function () {
                    window.onYouTubeIframeAPIReady = null;
                    loadingYoutubeDefer.resolve('done');
                    callback();
                };
            } else if (typeof YT === 'object' && YT.loaded === 1) {
                callback();
            } else {
                loadingYoutubeDefer.done(() => {
                    callback();
                });
            }
        }

        // Vimeo
        if (self.type === 'vimeo') {
            if (typeof Vimeo === 'undefined' && !loadingVimeoPlayer) {
                loadingVimeoPlayer = 1;
                const vimeoInterval = setInterval(() => {
                    if (typeof Vimeo !== 'undefined') {
                        clearInterval(vimeoInterval);
                        loadingVimeoDefer.resolve('done');
                        callback();
                    }
                }, 20);
            } else if (typeof Vimeo !== 'undefined') {
                callback();
            } else {
                loadingVimeoDefer.done(() => {
                    callback();
                });
            }
        }

        // Local
        if (self.type === 'local') {
            callback();
        }
    }
}


/***/ })

},[["./assets/js/skrollr.js","runtime","bewelcome"]]]);
//# sourceMappingURL=data:application/json;charset=utf-8;base64,eyJ2ZXJzaW9uIjozLCJzb3VyY2VzIjpbIndlYnBhY2s6Ly8vLi9hc3NldHMvanMvc2tyb2xsci5qcyIsIndlYnBhY2s6Ly8vLi9ub2RlX21vZHVsZXMvZ2xvYmFsL3dpbmRvdy5qcyIsIndlYnBhY2s6Ly8vLi9ub2RlX21vZHVsZXMvamFyYWxsYXgvaW5kZXguanMiLCJ3ZWJwYWNrOi8vLy4vbm9kZV9tb2R1bGVzL2phcmFsbGF4L3NyYy9qYXJhbGxheC1lbGVtZW50LmVzbS5qcyIsIndlYnBhY2s6Ly8vLi9ub2RlX21vZHVsZXMvamFyYWxsYXgvc3JjL2phcmFsbGF4LXZpZGVvLmVzbS5qcyIsIndlYnBhY2s6Ly8vLi9ub2RlX21vZHVsZXMvamFyYWxsYXgvc3JjL2phcmFsbGF4LmVzbS5qcyIsIndlYnBhY2s6Ly8vLi9ub2RlX21vZHVsZXMvbGl0ZS1yZWFkeS9saXRlcmVhZHkuanMiLCJ3ZWJwYWNrOi8vLy4vbm9kZV9tb2R1bGVzL3JhZmwvaW5kZXguanMiLCJ3ZWJwYWNrOi8vLy4vbm9kZV9tb2R1bGVzL3ZpZGVvLXdvcmtlci9pbmRleC5qcyIsIndlYnBhY2s6Ly8vLi9ub2RlX21vZHVsZXMvdmlkZW8td29ya2VyL3NyYy92aWRlby13b3JrZXIuZXNtLmpzIl0sIm5hbWVzIjpbXSwibWFwcGluZ3MiOiI7Ozs7Ozs7Ozs7QUFBQTtBQUFBO0FBQUE7Ozs7Ozs7Ozs7OztBQ0FBOztBQUVBO0FBQ0E7QUFDQSxDQUFDO0FBQ0Q7QUFDQSxDQUFDO0FBQ0Q7QUFDQSxDQUFDO0FBQ0Q7QUFDQTs7QUFFQTs7Ozs7Ozs7Ozs7OztBQ1pBLGlCQUFpQixtQkFBTyxDQUFDLHVFQUFvQjtBQUM3QyxzQkFBc0IsbUJBQU8sQ0FBQyxtRkFBMEI7QUFDeEQsd0JBQXdCLG1CQUFPLENBQUMsdUZBQTRCOztBQUU1RDtBQUNBO0FBQ0E7QUFDQTtBQUNBLEtBQUs7QUFDTDtBQUNBO0FBQ0EsS0FBSztBQUNMOzs7Ozs7Ozs7Ozs7O0FDWkE7QUFBQTtBQUFBO0FBQUE7QUFBQTtBQUM0Qjs7QUFFYixvQ0FBb0MsNkNBQU07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O0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTs7QUFFQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBOztBQUVBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7O0FBRUE7QUFDQTtBQUNBO0FBQ0E7O0FBRUE7QUFDQTtBQUNBO0FBQ0E7QUFDQTs7QUFFQTtBQUNBO0FBQ0E7QUFDQTs7QUFFQTtBQUNBO0FBQ0E7QUFDQSxtQkFBbUIsNkNBQU07QUFDekI7O0FBRUE7QUFDQTtBQUNBO0FBQ0E7O0FBRUE7QUFDQTtBQUNBLFNBQVM7QUFDVDtBQUNBOztBQUVBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBLGFBQWE7QUFDYixTQUFTO0FBQ1Q7QUFDQTs7QUFFQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBOztBQUVBO0FBQ0E7QUFDQTs7QUFFQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7O0FBRUE7QUFDQTtBQUNBO0FBQ0EsYUFBYTtBQUNiO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7O0FBRUE7QUFDQTtBQUNBO0FBQ0E7O0FBRUE7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBOztBQUVBO0FBQ0E7QUFDQTs7QUFFQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7O0FBRUE7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7O0FBRUE7QUFDQTtBQUNBO0FBQ0E7QUFDQSxhQUFhO0FBQ2I7QUFDQTtBQUNBO0FBQ0E7QUFDQSxhQUFhO0FBQ2I7O0FBRUE7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBLFNBQVM7O0FBRVQ7QUFDQTtBQUNBO0FBQ0E7QUFDQSxhQUFhO0FBQ2I7O0FBRUEsdUVBQXVFLGdCQUFnQjtBQUN2Rjs7QUFFQTtBQUNBO0FB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QUNBO0FBQ0E7QUFDQTtBQUNBLGFBQWE7QUFDYixTQUFTO0FBQ1Q7QUFDQTs7QUFFQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBOztBQUVBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBOztBQUVBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQSxhQUFhO0FBQ2I7QUFDQTs7QUFFQTtBQUNBO0FBQ0E7O0FBRUE7QUFDQTtBQUNBO0FBQ0EsU0FBUztBQUNUO0FBQ0E7QUFDQSxTQUFTO0FBQ1Q7QUFDQTtBQUNBOztBQUVBO0FBQ0E7O0FBRUE7QUFDQTtBQUNBOztBQUVBO0FBQ0E7QUFDQTs7QUFFQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTs7QUFFQTtBQUNBO0FBQ0EsU0FBUztBQUNUO0FBQ0E7QUFDQTtBQUNBO0FBQ0EsYUFBYTtBQUNiO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0EsYUFBYTtBQUNiO0FBQ0E7O0FBRUE7QUFDQTtBQUNBO0FBQ0E7QUFDQTs7QUFFQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBOztBQUVBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQSxhQUFhO0FBQ2I7O0FBRUE7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBOztBQUVBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7O0FBRUE7QUFDQTtBQUNBO0FBQ0E7QUFDQTs7QUFFQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0EsYUFBYTtBQUNiOztBQUVBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTs7QUFFQTtBQUNBO0FBQ0E7QUFDQTtBQUNBOztBQUVBO0FBQ0E7QUFDQTs7QUFFQTtBQUNBO0FBQ0E7O0FBRUE7QUFDQTtBQUNBO0FBQ0E7O0FBRUE7QUFDQTtBQUNBO0FBQ0E7QUFDQTs7QUFFQTtBQUNBO0FB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LGFBQWE7QUFDYjtBQUNBLGFBQWE7QUFDYjtBQUNBO0FBQ0EsaUJBQWlCO0FBQ2pCO0FBQ0E7O0FBRUE7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQSxpQkFBaUI7QUFDakIsYUFBYTtBQUNiO0FBQ0EsYUFBYTtBQUNiO0FBQ0E7QUFDQSxpQkFBaUI7QUFDakI7QUFDQTs7QUFFQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0EiLCJmaWxlIjoic2tyb2xsci5qcyIsInNvdXJjZXNDb250ZW50IjpbImltcG9ydCAnamFyYWxsYXgnO1xyXG4iLCJ2YXIgd2luO1xuXG5pZiAodHlwZW9mIHdpbmRvdyAhPT0gXCJ1bmRlZmluZWRcIikge1xuICAgIHdpbiA9IHdpbmRvdztcbn0gZWxzZSBpZiAodHlwZW9mIGdsb2JhbCAhPT0gXCJ1bmRlZmluZWRcIikge1xuICAgIHdpbiA9IGdsb2JhbDtcbn0gZWxzZSBpZiAodHlwZW9mIHNlbGYgIT09IFwidW5kZWZpbmVkXCIpe1xuICAgIHdpbiA9IHNlbGY7XG59IGVsc2Uge1xuICAgIHdpbiA9IHt9O1xufVxuXG5tb2R1bGUuZXhwb3J0cyA9IHdpbjtcbiIsImNvbnN0IGphcmFsbGF4ID0gcmVxdWlyZSgnLi9zcmMvamFyYWxsYXguZXNtJykuZGVmYXVsdDtcbmNvbnN0IGphcmFsbGF4VmlkZW8gPSByZXF1aXJlKCcuL3NyYy9qYXJhbGxheC12aWRlby5lc20nKS5kZWZhdWx0O1xuY29uc3QgamFyYWxsYXhFbGVtZW50ID0gcmVxdWlyZSgnLi9zcmMvamFyYWxsYXgtZWxlbWVudC5lc20nKS5kZWZhdWx0O1xuXG5tb2R1bGUuZXhwb3J0cyA9IHtcbiAgICBqYXJhbGxheCxcbiAgICBqYXJhbGxheEVsZW1lbnQoKSB7XG4gICAgICAgIHJldHVybiBqYXJhbGxheEVsZW1lbnQoamFyYWxsYXgpO1xuICAgIH0sXG4gICAgamFyYWxsYXhWaWRlbygpIHtcbiAgICAgICAgcmV0dXJuIGphcmFsbGF4VmlkZW8oamFyYWxsYXgpO1xuICAgIH0sXG59O1xuIiwiLyogZXNsaW50IG5vLWNhc2UtZGVjbGFyYXRpb25zOiBcIm9mZlwiICovXG5pbXBvcnQgZ2xvYmFsIGZyb20gJ2dsb2JhbCc7XG5cbmV4cG9ydCBkZWZhdWx0IGZ1bmN0aW9uIGphcmFsbGF4RWxlbWVudChqYXJhbGxheCA9IGdsb2JhbC5qYXJhbGxheCkge1xuICAgIGlmICh0eXBlb2YgamFyYWxsYXggPT09ICd1bmRlZmluZWQnKSB7XG4gICAgICAgIHJldHVybjtcbiAgICB9XG5cbiAgICBjb25zdCBKYXJhbGxheCA9IGphcmFsbGF4LmNvbnN0cnVjdG9yO1xuXG4gICAgLy8gcmVkZWZpbmUgZGVmYXVsdCBtZXRob2RzXG4gICAgW1xuICAgICAgICAnaW5pdEltZycsXG4gICAgICAgICdjYW5Jbml0UGFyYWxsYXgnLFxuICAgICAgICAnaW5pdCcsXG4gICAgICAgICdkZXN0cm95JyxcbiAgICAgICAgJ2NsaXBDb250YWluZXInLFxuICAgICAgICAnY292ZXJJbWFnZScsXG4gICAgICAgICdpc1Zpc2libGUnLFxuICAgICAgICAnb25TY3JvbGwnLFxuICAgICAgICAnb25SZXNpemUnLFxuICAgIF0uZm9yRWFjaCgoa2V5KSA9PiB7XG4gICAgICAgIGNvbnN0IGRlZiA9IEphcmFsbGF4LnByb3RvdHlwZVtrZXldO1xuICAgICAgICBKYXJhbGxheC5wcm90b3R5cGVba2V5XSA9IGZ1bmN0aW9uICgpIHtcbiAgICAgICAgICAgIGNvbnN0IHNlbGYgPSB0aGlzO1xuICAgICAgICAgICAgY29uc3QgYXJncyA9IGFyZ3VtZW50cyB8fCBbXTtcblxuICAgICAgICAgICAgaWYgKGtleSA9PT0gJ2luaXRJbWcnICYmIHNlbGYuJGl0ZW0uZ2V0QXR0cmlidXRlKCdkYXRhLWphcmFsbGF4LWVsZW1lbnQnKSAhPT0gbnVsbCkge1xuICAgICAgICAgICAgICAgIHNlbGYub3B0aW9ucy50eXBlID0gJ2VsZW1lbnQnO1xuICAgICAgICAgICAgICAgIHNlbGYucHVyZU9wdGlvbnMuc3BlZWQgPSBzZWxmLiRpdGVtLmdldEF0dHJpYnV0ZSgnZGF0YS1qYXJhbGxheC1lbGVtZW50JykgfHwgc2VsZi5wdXJlT3B0aW9ucy5zcGVlZDtcbiAgICAgICAgICAgIH1cbiAgICAgICAgICAgIGlmIChzZWxmLm9wdGlvbnMudHlwZSAhPT0gJ2VsZW1lbnQnKSB7XG4gICAgICAgICAgICAgICAgcmV0dXJuIGRlZi5hcHBseShzZWxmLCBhcmdzKTtcbiAgICAgICAgICAgIH1cblxuICAgICAgICAgICAgc2VsZi5wdXJlT3B0aW9ucy50aHJlc2hvbGQgPSBzZWxmLiRpdGVtLmdldEF0dHJpYnV0ZSgnZGF0YS10aHJlc2hvbGQnKSB8fCAnJztcblxuICAgICAgICAgICAgc3dpdGNoIChrZXkpIHtcbiAgICAgICAgICAgIGNhc2UgJ2luaXQnOlxuICAgICAgICAgICAgICAgIGNvbnN0IHNwZWVkQXJyID0gc2VsZi5wdXJlT3B0aW9ucy5zcGVlZC5zcGxpdCgnICcpO1xuICAgICAgICAgICAgICAgIHNlbGYub3B0aW9ucy5zcGVlZCA9IHNlbGYucHVyZU9wdGlvbnMuc3BlZWQgfHwgMDtcbiAgICAgICAgICAgICAgICBzZWxmLm9wdGlvbnMuc3BlZWRZID0gc3BlZWRBcnJbMF0gPyBwYXJzZUZsb2F0KHNwZWVkQXJyWzBdKSA6IDA7XG4gICAgICAgICAgICAgICAgc2VsZi5vcHRpb25zLnNwZWVkWCA9IHNwZWVkQXJyWzFdID8gcGFyc2VGbG9hdChzcGVlZEFyclsxXSkgOiAwO1xuXG4gICAgICAgICAgICAgICAgY29uc3QgdGhyZXNob2xkQXJyID0gc2VsZi5wdXJlT3B0aW9ucy50aHJlc2hvbGQuc3BsaXQoJyAnKTtcbiAgICAgICAgICAgICAgICBzZWxmLm9wdGlvbnMudGhyZXNob2xkWSA9IHRocmVzaG9sZEFyclswXSA/IHBhcnNlRmxvYXQodGhyZXNob2xkQXJyWzBdKSA6IG51bGw7XG4gICAgICAgICAgICAgICAgc2VsZi5vcHRpb25zLnRocmVzaG9sZFggPSB0aHJlc2hvbGRBcnJbMV0gPyBwYXJzZUZsb2F0KHRocmVzaG9sZEFyclsxXSkgOiBudWxsO1xuICAgICAgICAgICAgICAgIGJyZWFrO1xuICAgICAgICAgICAgY2FzZSAnb25SZXNpemUnOlxuICAgICAgICAgICAgICAgIGNvbnN0IGRlZlRyYW5zZm9ybSA9IHNlbGYuY3NzKHNlbGYuJGl0ZW0sICd0cmFuc2Zvcm0nKTtcbiAgICAgICAgICAgICAgICBzZWxmLmNzcyhzZWxmLiRpdGVtLCB7IHRyYW5zZm9ybTogJycgfSk7XG4gICAgICAgICAgICAgICAgY29uc3QgcmVjdCA9IHNlbGYuJGl0ZW0uZ2V0Qm91bmRpbmdDbGllbnRSZWN0KCk7XG4gICAgICAgICAgICAgICAgc2VsZi5pdGVtRGF0YSA9IHtcbiAgICAgICAgICAgICAgICAgICAgd2lkdGg6IHJlY3Qud2lkdGgsXG4gICAgICAgICAgICAgICAgICAgIGhlaWdodDogcmVjdC5oZWlnaHQsXG4gICAgICAgICAgICAgICAgICAgIHk6IHJlY3QudG9wICsgc2VsZi5nZXRXaW5kb3dEYXRhKCkueSxcbiAgICAgICAgICAgICAgICAgICAgeDogcmVjdC5sZWZ0LFxuICAgICAgICAgICAgICAgIH07XG4gICAgICAgICAgICAgICAgc2VsZi5jc3Moc2VsZi4kaXRlbSwgeyB0cmFuc2Zvcm06IGRlZlRyYW5zZm9ybSB9KTtcbiAgICAgICAgICAgICAgICBicmVhaztcbiAgICAgICAgICAgIGNhc2UgJ29uU2Nyb2xsJzpcbiAgICAgICAgICAgICAgICBjb25zdCB3bmQgPSBzZWxmLmdldFdpbmRvd0RhdGEoKTtcbiAgICAgICAgICAgICAgICBjb25zdCBjZW50ZXJQZXJjZW50ID0gKHduZC55ICsgd25kLmhlaWdodCAvIDIgLSBzZWxmLml0ZW1EYXRhLnkgLSBzZWxmLml0ZW1EYXRhLmhlaWdodCAvIDIpIC8gKHduZC5oZWlnaHQgLyAyKTtcbiAgICAgICAgICAgICAgICBjb25zdCBtb3ZlWSA9IGNlbnRlclBlcmNlbnQgKiBzZWxmLm9wdGlvbnMuc3BlZWRZO1xuICAgICAgICAgICAgICAgIGNvbnN0IG1vdmVYID0gY2VudGVyUGVyY2VudCAqIHNlbGYub3B0aW9ucy5zcGVlZFg7XG4gICAgICAgICAgICAgICAgbGV0IG15ID0gbW92ZVk7XG4gICAgICAgICAgICAgICAgbGV0IG14ID0gbW92ZVg7XG4gICAgICAgICAgICAgICAgaWYgKHNlbGYub3B0aW9ucy50aHJlc2hvbGRZICE9PSBudWxsICYmIG1vdmVZID4gc2VsZi5vcHRpb25zLnRocmVzaG9sZFkpIG15ID0gMDtcbiAgICAgICAgICAgICAgICBpZiAoc2VsZi5vcHRpb25zLnRocmVzaG9sZFggIT09IG51bGwgJiYgbW92ZVggPiBzZWxmLm9wdGlvbnMudGhyZXNob2xkWCkgbXggPSAwO1xuICAgICAgICAgICAgICAgIHNlbGYuY3NzKHNlbGYuJGl0ZW0sIHsgdHJhbnNmb3JtOiBgdHJhbnNsYXRlM2QoJHtteH1weCwke215fXB4LDApYCB9KTtcbiAgICAgICAgICAgICAgICBicmVhaztcbiAgICAgICAgICAgIGNhc2UgJ2luaXRJbWcnOlxuICAgICAgICAgICAgY2FzZSAnaXNWaXNpYmxlJzpcbiAgICAgICAgICAgIGNhc2UgJ2NsaXBDb250YWluZXInOlxuICAgICAgICAgICAgY2FzZSAnY292ZXJJbWFnZSc6XG4gICAgICAgICAgICAgICAgcmV0dXJuIHRydWU7XG4gICAgICAgICAgICAvLyBubyBkZWZhdWx0XG4gICAgICAgICAgICB9XG4gICAgICAgICAgICByZXR1cm4gZGVmLmFwcGx5KHNlbGYsIGFyZ3MpO1xuICAgICAgICB9O1xuICAgIH0pO1xufVxuIiwiaW1wb3J0IFZpZGVvV29ya2VyIGZyb20gJ3ZpZGVvLXdvcmtlcic7XG5pbXBvcnQgZ2xvYmFsIGZyb20gJ2dsb2JhbCc7XG5cbmV4cG9ydCBkZWZhdWx0IGZ1bmN0aW9uIGphcmFsbGF4VmlkZW8oamFyYWxsYXggPSBnbG9iYWwuamFyYWxsYXgpIHtcbiAgICBpZiAodHlwZW9mIGphcmFsbGF4ID09PSAndW5kZWZpbmVkJykge1xuICAgICAgICByZXR1cm47XG4gICAgfVxuXG4gICAgY29uc3QgSmFyYWxsYXggPSBqYXJhbGxheC5jb25zdHJ1Y3RvcjtcblxuICAgIC8vIGFwcGVuZCB2aWRlbyBhZnRlciBpbml0IEphcmFsbGF4XG4gICAgY29uc3QgZGVmSW5pdCA9IEphcmFsbGF4LnByb3RvdHlwZS5pbml0O1xuICAgIEphcmFsbGF4LnByb3RvdHlwZS5pbml0ID0gZnVuY3Rpb24gKCkge1xuICAgICAgICBjb25zdCBzZWxmID0gdGhpcztcblxuICAgICAgICBkZWZJbml0LmFwcGx5KHNlbGYpO1xuXG4gICAgICAgIGlmIChzZWxmLnZpZGVvICYmICFzZWxmLm9wdGlvbnMuZGlzYWJsZVZpZGVvKCkpIHtcbiAgICAgICAgICAgIHNlbGYudmlkZW8uZ2V0VmlkZW8oKHZpZGVvKSA9PiB7XG4gICAgICAgICAgICAgICAgY29uc3QgJHBhcmVudCA9IHZpZGVvLnBhcmVudE5vZGU7XG4gICAgICAgICAgICAgICAgc2VsZi5jc3ModmlkZW8sIHtcbiAgICAgICAgICAgICAgICAgICAgcG9zaXRpb246IHNlbGYuaW1hZ2UucG9zaXRpb24sXG4gICAgICAgICAgICAgICAgICAgIHRvcDogJzBweCcsXG4gICAgICAgICAgICAgICAgICAgIGxlZnQ6ICcwcHgnLFxuICAgICAgICAgICAgICAgICAgICByaWdodDogJzBweCcsXG4gICAgICAgICAgICAgICAgICAgIGJvdHRvbTogJzBweCcsXG4gICAgICAgICAgICAgICAgICAgIHdpZHRoOiAnMTAwJScsXG4gICAgICAgICAgICAgICAgICAgIGhlaWdodDogJzEwMCUnLFxuICAgICAgICAgICAgICAgICAgICBtYXhXaWR0aDogJ25vbmUnLFxuICAgICAgICAgICAgICAgICAgICBtYXhIZWlnaHQ6ICdub25lJyxcbiAgICAgICAgICAgICAgICAgICAgbWFyZ2luOiAwLFxuICAgICAgICAgICAgICAgICAgICB6SW5kZXg6IC0xLFxuICAgICAgICAgICAgICAgIH0pO1xuICAgICAgICAgICAgICAgIHNlbGYuJHZpZGVvID0gdmlkZW87XG4gICAgICAgICAgICAgICAgc2VsZi5pbWFnZS4kY29udGFpbmVyLmFwcGVuZENoaWxkKHZpZGVvKTtcblxuICAgICAgICAgICAgICAgIC8vIHJlbW92ZSBwYXJlbnQgdmlkZW8gZWxlbWVudCAoY3JlYXRlZCBieSBWaWRlb1dvcmtlcilcbiAgICAgICAgICAgICAgICAkcGFyZW50LnBhcmVudE5vZGUucmVtb3ZlQ2hpbGQoJHBhcmVudCk7XG4gICAgICAgICAgICB9KTtcbiAgICAgICAgfVxuICAgIH07XG5cbiAgICAvLyBjb3ZlciB2aWRlb1xuICAgIGNvbnN0IGRlZkNvdmVySW1hZ2UgPSBKYXJhbGxheC5wcm90b3R5cGUuY292ZXJJbWFnZTtcbiAgICBKYXJhbGxheC5wcm90b3R5cGUuY292ZXJJbWFnZSA9IGZ1bmN0aW9uICgpIHtcbiAgICAgICAgY29uc3Qgc2VsZiA9IHRoaXM7XG4gICAgICAgIGNvbnN0IGltYWdlRGF0YSA9IGRlZkNvdmVySW1hZ2UuYXBwbHkoc2VsZik7XG4gICAgICAgIGNvbnN0IG5vZGUgPSBzZWxmLmltYWdlLiRpdGVtID8gc2VsZi5pbWFnZS4kaXRlbS5ub2RlTmFtZSA6IGZhbHNlO1xuXG4gICAgICAgIGlmIChpbWFnZURhdGEgJiYgc2VsZi52aWRlbyAmJiBub2RlICYmIChub2RlID09PSAnSUZSQU1FJyB8fCBub2RlID09PSAnVklERU8nKSkge1xuICAgICAgICAgICAgbGV0IGggPSBpbWFnZURhdGEuaW1hZ2UuaGVpZ2h0O1xuICAgICAgICAgICAgbGV0IHcgPSBoICogc2VsZi5pbWFnZS53aWR0aCAvIHNlbGYuaW1hZ2UuaGVpZ2h0O1xuICAgICAgICAgICAgbGV0IG1sID0gKGltYWdlRGF0YS5jb250YWluZXIud2lkdGggLSB3KSAvIDI7XG4gICAgICAgICAgICBsZXQgbXQgPSBpbWFnZURhdGEuaW1hZ2UubWFyZ2luVG9wO1xuXG4gICAgICAgICAgICBpZiAoaW1hZ2VEYXRhLmNvbnRhaW5lci53aWR0aCA+IHcpIHtcbiAgICAgICAgICAgICAgICB3ID0gaW1hZ2VEYXRhLmNvbnRhaW5lci53aWR0aDtcbiAgICAgICAgICAgICAgICBoID0gdyAqIHNlbGYuaW1hZ2UuaGVpZ2h0IC8gc2VsZi5pbWFnZS53aWR0aDtcbiAgICAgICAgICAgICAgICBtbCA9IDA7XG4gICAgICAgICAgICAgICAgbXQgKz0gKGltYWdlRGF0YS5pbWFnZS5oZWlnaHQgLSBoKSAvIDI7XG4gICAgICAgICAgICB9XG5cbiAgICAgICAgICAgIC8vIGFkZCB2aWRlbyBoZWlnaHQgb3ZlciB0aGFuIG5lZWQgdG8gaGlkZSBjb250cm9sc1xuICAgICAgICAgICAgaWYgKG5vZGUgPT09ICdJRlJBTUUnKSB7XG4gICAgICAgICAgICAgICAgaCArPSA0MDA7XG4gICAgICAgICAgICAgICAgbXQgLT0gMjAwO1xuICAgICAgICAgICAgfVxuXG4gICAgICAgICAgICBzZWxmLmNzcyhzZWxmLiR2aWRlbywge1xuICAgICAgICAgICAgICAgIHdpZHRoOiBgJHt3fXB4YCxcbiAgICAgICAgICAgICAgICBtYXJnaW5MZWZ0OiBgJHttbH1weGAsXG4gICAgICAgICAgICAgICAgaGVpZ2h0OiBgJHtofXB4YCxcbiAgICAgICAgICAgICAgICBtYXJnaW5Ub3A6IGAke210fXB4YCxcbiAgICAgICAgICAgIH0pO1xuICAgICAgICB9XG5cbiAgICAgICAgcmV0dXJuIGltYWdlRGF0YTtcbiAgICB9O1xuXG4gICAgLy8gaW5pdCB2aWRlb1xuICAgIGNvbnN0IGRlZkluaXRJbWcgPSBKYXJhbGxheC5wcm90b3R5cGUuaW5pdEltZztcbiAgICBKYXJhbGxheC5wcm90b3R5cGUuaW5pdEltZyA9IGZ1bmN0aW9uICgpIHtcbiAgICAgICAgY29uc3Qgc2VsZiA9IHRoaXM7XG4gICAgICAgIGNvbnN0IGRlZmF1bHRSZXN1bHQgPSBkZWZJbml0SW1nLmFwcGx5KHNlbGYpO1xuXG4gICAgICAgIGlmICghc2VsZi5vcHRpb25zLnZpZGVvU3JjKSB7XG4gICAgICAgICAgICBzZWxmLm9wdGlvbnMudmlkZW9TcmMgPSBzZWxmLiRpdGVtLmdldEF0dHJpYnV0ZSgnZGF0YS1qYXJhbGxheC12aWRlbycpIHx8IG51bGw7XG4gICAgICAgIH1cblxuICAgICAgICBpZiAoc2VsZi5vcHRpb25zLnZpZGVvU3JjKSB7XG4gICAgICAgICAgICBzZWxmLmRlZmF1bHRJbml0SW1nUmVzdWx0ID0gZGVmYXVsdFJlc3VsdDtcbiAgICAgICAgICAgIHJldHVybiB0cnVlO1xuICAgICAgICB9XG5cbiAgICAgICAgcmV0dXJuIGRlZmF1bHRSZXN1bHQ7XG4gICAgfTtcblxuICAgIGNvbnN0IGRlZkNhbkluaXRQYXJhbGxheCA9IEphcmFsbGF4LnByb3RvdHlwZS5jYW5Jbml0UGFyYWxsYXg7XG4gICAgSmFyYWxsYXgucHJvdG90eXBlLmNhbkluaXRQYXJhbGxheCA9IGZ1bmN0aW9uICgpIHtcbiAgICAgICAgY29uc3Qgc2VsZiA9IHRoaXM7XG4gICAgICAgIGNvbnN0IGRlZmF1bHRSZXN1bHQgPSBkZWZDYW5Jbml0UGFyYWxsYXguYXBwbHkoc2VsZik7XG5cbiAgICAgICAgaWYgKCFzZWxmLm9wdGlvbnMudmlkZW9TcmMpIHtcbiAgICAgICAgICAgIHJldHVybiBkZWZhdWx0UmVzdWx0O1xuICAgICAgICB9XG5cbiAgICAgICAgY29uc3QgdmlkZW8gPSBuZXcgVmlkZW9Xb3JrZXIoc2VsZi5vcHRpb25zLnZpZGVvU3JjLCB7XG4gICAgICAgICAgICBhdXRvcGxheTogdHJ1ZSxcbiAgICAgICAgICAgIGxvb3A6IHRydWUsXG4gICAgICAgICAgICBzaG93Q29udG9sczogZmFsc2UsXG4gICAgICAgICAgICBzdGFydFRpbWU6IHNlbGYub3B0aW9ucy52aWRlb1N0YXJ0VGltZSB8fCAwLFxuICAgICAgICAgICAgZW5kVGltZTogc2VsZi5vcHRpb25zLnZpZGVvRW5kVGltZSB8fCAwLFxuICAgICAgICAgICAgbXV0ZTogc2VsZi5vcHRpb25zLnZpZGVvVm9sdW1lID8gMCA6IDEsXG4gICAgICAgICAgICB2b2x1bWU6IHNlbGYub3B0aW9ucy52aWRlb1ZvbHVtZSB8fCAwLFxuICAgICAgICB9KTtcblxuICAgICAgICBpZiAodmlkZW8uaXNWYWxpZCgpKSB7XG4gICAgICAgICAgICAvLyBpZiBwYXJhbGxheCB3aWxsIG5vdCBiZSBpbml0ZWQsIHdlIGNhbiBhZGQgdGh1bWJuYWlsIG9uIGJhY2tncm91bmQuXG4gICAgICAgICAgICBpZiAoIWRlZmF1bHRSZXN1bHQpIHtcbiAgICAgICAgICAgICAgICBpZiAoIXNlbGYuZGVmYXVsdEluaXRJbWdSZXN1bHQpIHtcbiAgICAgICAgICAgICAgICAgICAgdmlkZW8uZ2V0SW1hZ2VVUkwoKHVybCkgPT4ge1xuICAgICAgICAgICAgICAgICAgICAgICAgLy8gc2F2ZSBkZWZhdWx0IHVzZXIgc3R5bGVzXG4gICAgICAgICAgICAgICAgICAgICAgICBjb25zdCBjdXJTdHlsZSA9IHNlbGYuJGl0ZW0uZ2V0QXR0cmlidXRlKCdzdHlsZScpO1xuICAgICAgICAgICAgICAgICAgICAgICAgaWYgKGN1clN0eWxlKSB7XG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgc2VsZi4kaXRlbS5zZXRBdHRyaWJ1dGUoJ2RhdGEtamFyYWxsYXgtb3JpZ2luYWwtc3R5bGVzJywgY3VyU3R5bGUpO1xuICAgICAgICAgICAgICAgICAgICAgICAgfVxuXG4gICAgICAgICAgICAgICAgICAgICAgICAvLyBzZXQgbmV3IGJhY2tncm91bmRcbiAgICAgICAgICAgICAgICAgICAgICAgIHNlbGYuY3NzKHNlbGYuJGl0ZW0sIHtcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICAnYmFja2dyb3VuZC1pbWFnZSc6IGB1cmwoXCIke3VybH1cIilgLFxuICAgICAgICAgICAgICAgICAgICAgICAgICAgICdiYWNrZ3JvdW5kLXBvc2l0aW9uJzogJ2NlbnRlcicsXG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgJ2JhY2tncm91bmQtc2l6ZSc6ICdjb3ZlcicsXG4gICAgICAgICAgICAgICAgICAgICAgICB9KTtcbiAgICAgICAgICAgICAgICAgICAgfSk7XG4gICAgICAgICAgICAgICAgfVxuXG4gICAgICAgICAgICAgICAgLy8gaW5pdCB2aWRlb1xuICAgICAgICAgICAgfSBlbHNlIHtcbiAgICAgICAgICAgICAgICB2aWRlby5vbigncmVhZHknLCAoKSA9PiB7XG4gICAgICAgICAgICAgICAgICAgIGlmIChzZWxmLm9wdGlvbnMudmlkZW9QbGF5T25seVZpc2libGUpIHtcbiAgICAgICAgICAgICAgICAgICAgICAgIGNvbnN0IG9sZE9uU2Nyb2xsID0gc2VsZi5vblNjcm9sbDtcbiAgICAgICAgICAgICAgICAgICAgICAgIHNlbGYub25TY3JvbGwgPSBmdW5jdGlvbiAoKSB7XG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgb2xkT25TY3JvbGwuYXBwbHkoc2VsZik7XG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgaWYgKHNlbGYuaXNWaXNpYmxlKCkpIHtcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgdmlkZW8ucGxheSgpO1xuICAgICAgICAgICAgICAgICAgICAgICAgICAgIH0gZWxzZSB7XG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIHZpZGVvLnBhdXNlKCk7XG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgfVxuICAgICAgICAgICAgICAgICAgICAgICAgfTtcbiAgICAgICAgICAgICAgICAgICAgfSBlbHNlIHtcbiAgICAgICAgICAgICAgICAgICAgICAgIHZpZGVvLnBsYXkoKTtcbiAgICAgICAgICAgICAgICAgICAgfVxuICAgICAgICAgICAgICAgIH0pO1xuXG4gICAgICAgICAgICAgICAgdmlkZW8ub24oJ3N0YXJ0ZWQnLCAoKSA9PiB7XG4gICAgICAgICAgICAgICAgICAgIHNlbGYuaW1hZ2UuJGRlZmF1bHRfaXRlbSA9IHNlbGYuaW1hZ2UuJGl0ZW07XG4gICAgICAgICAgICAgICAgICAgIHNlbGYuaW1hZ2UuJGl0ZW0gPSBzZWxmLiR2aWRlbztcblxuICAgICAgICAgICAgICAgICAgICAvLyBzZXQgdmlkZW8gd2lkdGggYW5kIGhlaWdodFxuICAgICAgICAgICAgICAgICAgICBzZWxmLmltYWdlLndpZHRoID0gc2VsZi52aWRlby52aWRlb1dpZHRoIHx8IDEyODA7XG4gICAgICAgICAgICAgICAgICAgIHNlbGYuaW1hZ2UuaGVpZ2h0ID0gc2VsZi52aWRlby52aWRlb0hlaWdodCB8fCA3MjA7XG4gICAgICAgICAgICAgICAgICAgIHNlbGYub3B0aW9ucy5pbWdXaWR0aCA9IHNlbGYuaW1hZ2Uud2lkdGg7XG4gICAgICAgICAgICAgICAgICAgIHNlbGYub3B0aW9ucy5pbWdIZWlnaHQgPSBzZWxmLmltYWdlLmhlaWdodDtcbiAgICAgICAgICAgICAgICAgICAgc2VsZi5jb3ZlckltYWdlKCk7XG4gICAgICAgICAgICAgICAgICAgIHNlbGYuY2xpcENvbnRhaW5lcigpO1xuICAgICAgICAgICAgICAgICAgICBzZWxmLm9uU2Nyb2xsKCk7XG5cbiAgICAgICAgICAgICAgICAgICAgLy8gaGlkZSBpbWFnZVxuICAgICAgICAgICAgICAgICAgICBpZiAoc2VsZi5pbWFnZS4kZGVmYXVsdF9pdGVtKSB7XG4gICAgICAgICAgICAgICAgICAgICAgICBzZWxmLmltYWdlLiRkZWZhdWx0X2l0ZW0uc3R5bGUuZGlzcGxheSA9ICdub25lJztcbiAgICAgICAgICAgICAgICAgICAgfVxuICAgICAgICAgICAgICAgIH0pO1xuXG4gICAgICAgICAgICAgICAgc2VsZi52aWRlbyA9IHZpZGVvO1xuXG4gICAgICAgICAgICAgICAgLy8gc2V0IGltYWdlIGlmIG5vdCBleGlzdHNcbiAgICAgICAgICAgICAgICBpZiAoIXNlbGYuZGVmYXVsdEluaXRJbWdSZXN1bHQpIHtcbiAgICAgICAgICAgICAgICAgICAgaWYgKHZpZGVvLnR5cGUgIT09ICdsb2NhbCcpIHtcbiAgICAgICAgICAgICAgICAgICAgICAgIHZpZGVvLmdldEltYWdlVVJMKCh1cmwpID0+IHtcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICBzZWxmLmltYWdlLnNyYyA9IHVybDtcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICBzZWxmLmluaXQoKTtcbiAgICAgICAgICAgICAgICAgICAgICAgIH0pO1xuXG4gICAgICAgICAgICAgICAgICAgICAgICByZXR1cm4gZmFsc2U7XG4gICAgICAgICAgICAgICAgICAgIH1cblxuICAgICAgICAgICAgICAgICAgICAvLyBzZXQgZW1wdHkgaW1hZ2Ugb24gbG9jYWwgdmlkZW8gaWYgbm90IGRlZmluZWRcbiAgICAgICAgICAgICAgICAgICAgc2VsZi5pbWFnZS5zcmMgPSAnZGF0YTppbWFnZS9naWY7YmFzZTY0LFIwbEdPRGxoQVFBQkFJQUFBQUFBQVAvLy95SDVCQUVBQUFBQUxBQUFBQUFCQUFFQUFBSUJSQUE3JztcbiAgICAgICAgICAgICAgICAgICAgcmV0dXJuIHRydWU7XG4gICAgICAgICAgICAgICAgfVxuICAgICAgICAgICAgfVxuICAgICAgICB9XG5cbiAgICAgICAgcmV0dXJuIGRlZmF1bHRSZXN1bHQ7XG4gICAgfTtcblxuICAgIC8vIERlc3Ryb3kgdmlkZW8gcGFyYWxsYXhcbiAgICBjb25zdCBkZWZEZXN0cm95ID0gSmFyYWxsYXgucHJvdG90eXBlLmRlc3Ryb3k7XG4gICAgSmFyYWxsYXgucHJvdG90eXBlLmRlc3Ryb3kgPSBmdW5jdGlvbiAoKSB7XG4gICAgICAgIGNvbnN0IHNlbGYgPSB0aGlzO1xuXG4gICAgICAgIGlmIChzZWxmLmltYWdlLiRkZWZhdWx0X2l0ZW0pIHtcbiAgICAgICAgICAgIHNlbGYuaW1hZ2UuJGl0ZW0gPSBzZWxmLmltYWdlLiRkZWZhdWx0X2l0ZW07XG4gICAgICAgICAgICBkZWxldGUgc2VsZi5pbWFnZS4kZGVmYXVsdF9pdGVtO1xuICAgICAgICB9XG5cbiAgICAgICAgZGVmRGVzdHJveS5hcHBseShzZWxmKTtcbiAgICB9O1xufVxuIiwiaW1wb3J0IGRvbVJlYWR5IGZyb20gJ2xpdGUtcmVhZHknO1xuaW1wb3J0IHJhZiBmcm9tICdyYWZsJztcbmltcG9ydCB7IHdpbmRvdyB9IGZyb20gJ2dsb2JhbCc7XG5cbmNvbnN0IGlzSUUgPSBuYXZpZ2F0b3IudXNlckFnZW50LmluZGV4T2YoJ01TSUUgJykgPiAtMSB8fCBuYXZpZ2F0b3IudXNlckFnZW50LmluZGV4T2YoJ1RyaWRlbnQvJykgPiAtMSB8fCBuYXZpZ2F0b3IudXNlckFnZW50LmluZGV4T2YoJ0VkZ2UvJykgPiAtMTtcblxuY29uc3Qgc3VwcG9ydFRyYW5zZm9ybSA9ICgoKSA9PiB7XG4gICAgY29uc3QgcHJlZml4ZXMgPSAndHJhbnNmb3JtIFdlYmtpdFRyYW5zZm9ybSBNb3pUcmFuc2Zvcm0nLnNwbGl0KCcgJyk7XG4gICAgY29uc3QgZGl2ID0gZG9jdW1lbnQuY3JlYXRlRWxlbWVudCgnZGl2Jyk7XG4gICAgZm9yIChsZXQgaSA9IDA7IGkgPCBwcmVmaXhlcy5sZW5ndGg7IGkrKykge1xuICAgICAgICBpZiAoZGl2ICYmIGRpdi5zdHlsZVtwcmVmaXhlc1tpXV0gIT09IHVuZGVmaW5lZCkge1xuICAgICAgICAgICAgcmV0dXJuIHByZWZpeGVzW2ldO1xuICAgICAgICB9XG4gICAgfVxuICAgIHJldHVybiBmYWxzZTtcbn0pKCk7XG5cbi8vIFdpbmRvdyBkYXRhXG5sZXQgd25kVztcbmxldCB3bmRIO1xubGV0IHduZFk7XG5sZXQgZm9yY2VSZXNpemVQYXJhbGxheCA9IGZhbHNlO1xubGV0IGZvcmNlU2Nyb2xsUGFyYWxsYXggPSBmYWxzZTtcbmZ1bmN0aW9uIHVwZGF0ZVduZFZhcnMoZSkge1xuICAgIHduZFcgPSB3aW5kb3cuaW5uZXJXaWR0aCB8fCBkb2N1bWVudC5kb2N1bWVudEVsZW1lbnQuY2xpZW50V2lkdGg7XG4gICAgd25kSCA9IHdpbmRvdy5pbm5lckhlaWdodCB8fCBkb2N1bWVudC5kb2N1bWVudEVsZW1lbnQuY2xpZW50SGVpZ2h0O1xuICAgIGlmICh0eXBlb2YgZSA9PT0gJ29iamVjdCcgJiYgKGUudHlwZSA9PT0gJ2xvYWQnIHx8IGUudHlwZSA9PT0gJ2RvbS1sb2FkZWQnKSkge1xuICAgICAgICBmb3JjZVJlc2l6ZVBhcmFsbGF4ID0gdHJ1ZTtcbiAgICB9XG59XG51cGRhdGVXbmRWYXJzKCk7XG53aW5kb3cuYWRkRXZlbnRMaXN0ZW5lcigncmVzaXplJywgdXBkYXRlV25kVmFycyk7XG53aW5kb3cuYWRkRXZlbnRMaXN0ZW5lcignb3JpZW50YXRpb25jaGFuZ2UnLCB1cGRhdGVXbmRWYXJzKTtcbndpbmRvdy5hZGRFdmVudExpc3RlbmVyKCdsb2FkJywgdXBkYXRlV25kVmFycyk7XG5kb21SZWFkeSgoKSA9PiB7XG4gICAgdXBkYXRlV25kVmFycyh7XG4gICAgICAgIHR5cGU6ICdkb20tbG9hZGVkJyxcbiAgICB9KTtcbn0pO1xuXG4vLyBsaXN0IHdpdGggYWxsIGphcmFsbGF4IGluc3RhbmNlc1xuLy8gbmVlZCB0byByZW5kZXIgYWxsIGluIG9uZSBzY3JvbGwvcmVzaXplIGV2ZW50XG5jb25zdCBqYXJhbGxheExpc3QgPSBbXTtcblxuLy8gQW5pbWF0ZSBpZiBjaGFuZ2VkIHdpbmRvdyBzaXplIG9yIHNjcm9sbGVkIHBhZ2VcbmxldCBvbGRQYWdlRGF0YSA9IGZhbHNlO1xuZnVuY3Rpb24gdXBkYXRlUGFyYWxsYXgoKSB7XG4gICAgaWYgKCFqYXJhbGxheExpc3QubGVuZ3RoKSB7XG4gICAgICAgIHJldHVybjtcbiAgICB9XG5cbiAgICBpZiAod2luZG93LnBhZ2VZT2Zmc2V0ICE9PSB1bmRlZmluZWQpIHtcbiAgICAgICAgd25kWSA9IHdpbmRvdy5wYWdlWU9mZnNldDtcbiAgICB9IGVsc2Uge1xuICAgICAgICB3bmRZID0gKGRvY3VtZW50LmRvY3VtZW50RWxlbWVudCB8fCBkb2N1bWVudC5ib2R5LnBhcmVudE5vZGUgfHwgZG9jdW1lbnQuYm9keSkuc2Nyb2xsVG9wO1xuICAgIH1cblxuICAgIGNvbnN0IGlzUmVzaXplZCA9IGZvcmNlUmVzaXplUGFyYWxsYXggfHwgIW9sZFBhZ2VEYXRhIHx8IG9sZFBhZ2VEYXRhLndpZHRoICE9PSB3bmRXIHx8IG9sZFBhZ2VEYXRhLmhlaWdodCAhPT0gd25kSDtcbiAgICBjb25zdCBpc1Njcm9sbGVkID0gZm9yY2VTY3JvbGxQYXJhbGxheCB8fCBpc1Jlc2l6ZWQgfHwgIW9sZFBhZ2VEYXRhIHx8IG9sZFBhZ2VEYXRhLnkgIT09IHduZFk7XG5cbiAgICBmb3JjZVJlc2l6ZVBhcmFsbGF4ID0gZmFsc2U7XG4gICAgZm9yY2VTY3JvbGxQYXJhbGxheCA9IGZhbHNlO1xuXG4gICAgaWYgKGlzUmVzaXplZCB8fCBpc1Njcm9sbGVkKSB7XG4gICAgICAgIGphcmFsbGF4TGlzdC5mb3JFYWNoKChpdGVtKSA9PiB7XG4gICAgICAgICAgICBpZiAoaXNSZXNpemVkKSB7XG4gICAgICAgICAgICAgICAgaXRlbS5vblJlc2l6ZSgpO1xuICAgICAgICAgICAgfVxuICAgICAgICAgICAgaWYgKGlzU2Nyb2xsZWQpIHtcbiAgICAgICAgICAgICAgICBpdGVtLm9uU2Nyb2xsKCk7XG4gICAgICAgICAgICB9XG4gICAgICAgIH0pO1xuXG4gICAgICAgIG9sZFBhZ2VEYXRhID0ge1xuICAgICAgICAgICAgd2lkdGg6IHduZFcsXG4gICAgICAgICAgICBoZWlnaHQ6IHduZEgsXG4gICAgICAgICAgICB5OiB3bmRZLFxuICAgICAgICB9O1xuICAgIH1cblxuICAgIHJhZih1cGRhdGVQYXJhbGxheCk7XG59XG5cblxuLy8gUmVzaXplT2JzZXJ2ZXJcbmNvbnN0IHJlc2l6ZU9ic2VydmVyID0gZ2xvYmFsLlJlc2l6ZU9ic2VydmVyID8gbmV3IGdsb2JhbC5SZXNpemVPYnNlcnZlcigoZW50cnkpID0+IHtcbiAgICBpZiAoZW50cnkgJiYgZW50cnkubGVuZ3RoKSB7XG4gICAgICAgIHJhZigoKSA9PiB7XG4gICAgICAgICAgICBlbnRyeS5mb3JFYWNoKChpdGVtKSA9PiB7XG4gICAgICAgICAgICAgICAgaWYgKGl0ZW0udGFyZ2V0ICYmIGl0ZW0udGFyZ2V0LmphcmFsbGF4KSB7XG4gICAgICAgICAgICAgICAgICAgIGlmICghZm9yY2VSZXNpemVQYXJhbGxheCkge1xuICAgICAgICAgICAgICAgICAgICAgICAgaXRlbS50YXJnZXQuamFyYWxsYXgub25SZXNpemUoKTtcbiAgICAgICAgICAgICAgICAgICAgfVxuICAgICAgICAgICAgICAgICAgICBmb3JjZVNjcm9sbFBhcmFsbGF4ID0gdHJ1ZTtcbiAgICAgICAgICAgICAgICB9XG4gICAgICAgICAgICB9KTtcbiAgICAgICAgfSk7XG4gICAgfVxufSkgOiBmYWxzZTtcblxuXG5sZXQgaW5zdGFuY2VJRCA9IDA7XG5cbi8vIEphcmFsbGF4IGNsYXNzXG5jbGFzcyBKYXJhbGxheCB7XG4gICAgY29uc3RydWN0b3IoaXRlbSwgdXNlck9wdGlvbnMpIHtcbiAgICAgICAgY29uc3Qgc2VsZiA9IHRoaXM7XG5cbiAgICAgICAgc2VsZi5pbnN0YW5jZUlEID0gaW5zdGFuY2VJRCsrO1xuXG4gICAgICAgIHNlbGYuJGl0ZW0gPSBpdGVtO1xuXG4gICAgICAgIHNlbGYuZGVmYXVsdHMgPSB7XG4gICAgICAgICAgICB0eXBlOiAnc2Nyb2xsJywgLy8gdHlwZSBvZiBwYXJhbGxheDogc2Nyb2xsLCBzY2FsZSwgb3BhY2l0eSwgc2NhbGUtb3BhY2l0eSwgc2Nyb2xsLW9wYWNpdHlcbiAgICAgICAgICAgIHNwZWVkOiAwLjUsIC8vIHN1cHBvcnRlZCB2YWx1ZSBmcm9tIC0xIHRvIDJcbiAgICAgICAgICAgIGltZ1NyYzogbnVsbCxcbiAgICAgICAgICAgIGltZ0VsZW1lbnQ6ICcuamFyYWxsYXgtaW1nJyxcbiAgICAgICAgICAgIGltZ1NpemU6ICdjb3ZlcicsXG4gICAgICAgICAgICBpbWdQb3NpdGlvbjogJzUwJSA1MCUnLFxuICAgICAgICAgICAgaW1nUmVwZWF0OiAnbm8tcmVwZWF0JywgLy8gc3VwcG9ydGVkIG9ubHkgZm9yIGJhY2tncm91bmQsIG5vdCBmb3IgPGltZz4gdGFnXG4gICAgICAgICAgICBrZWVwSW1nOiBmYWxzZSwgLy8ga2VlcCA8aW1nPiB0YWcgaW4gaXQncyBkZWZhdWx0IHBsYWNlXG4gICAgICAgICAgICBlbGVtZW50SW5WaWV3cG9ydDogbnVsbCxcbiAgICAgICAgICAgIHpJbmRleDogLTEwMCxcbiAgICAgICAgICAgIGRpc2FibGVQYXJhbGxheDogZmFsc2UsXG4gICAgICAgICAgICBkaXNhYmxlVmlkZW86IGZhbHNlLFxuICAgICAgICAgICAgYXV0b21hdGljUmVzaXplOiB0cnVlLCAvLyB1c2UgUmVzaXplT2JzZXJ2ZXIgdG8gcmVjYWxjdWxhdGUgcG9zaXRpb24gYW5kIHNpemUgb2YgcGFyYWxsYXggaW1hZ2VcblxuICAgICAgICAgICAgLy8gdmlkZW9cbiAgICAgICAgICAgIHZpZGVvU3JjOiBudWxsLFxuICAgICAgICAgICAgdmlkZW9TdGFydFRpbWU6IDAsXG4gICAgICAgICAgICB2aWRlb0VuZFRpbWU6IDAsXG4gICAgICAgICAgICB2aWRlb1ZvbHVtZTogMCxcbiAgICAgICAgICAgIHZpZGVvUGxheU9ubHlWaXNpYmxlOiB0cnVlLFxuXG4gICAgICAgICAgICAvLyBldmVudHNcbiAgICAgICAgICAgIG9uU2Nyb2xsOiBudWxsLCAvLyBmdW5jdGlvbihjYWxjdWxhdGlvbnMpIHt9XG4gICAgICAgICAgICBvbkluaXQ6IG51bGwsIC8vIGZ1bmN0aW9uKCkge31cbiAgICAgICAgICAgIG9uRGVzdHJveTogbnVsbCwgLy8gZnVuY3Rpb24oKSB7fVxuICAgICAgICAgICAgb25Db3ZlckltYWdlOiBudWxsLCAvLyBmdW5jdGlvbigpIHt9XG4gICAgICAgIH07XG5cbiAgICAgICAgLy8gREVQUkVDQVRFRDogb2xkIGRhdGEtb3B0aW9uc1xuICAgICAgICBjb25zdCBkZXByZWNhdGVkRGF0YUF0dHJpYnV0ZSA9IHNlbGYuJGl0ZW0uZ2V0QXR0cmlidXRlKCdkYXRhLWphcmFsbGF4Jyk7XG4gICAgICAgIGNvbnN0IG9sZERhdGFPcHRpb25zID0gSlNPTi5wYXJzZShkZXByZWNhdGVkRGF0YUF0dHJpYnV0ZSB8fCAne30nKTtcbiAgICAgICAgaWYgKGRlcHJlY2F0ZWREYXRhQXR0cmlidXRlKSB7XG4gICAgICAgICAgICAvLyBlc2xpbnQtZGlzYWJsZS1uZXh0LWxpbmUgbm8tY29uc29sZVxuICAgICAgICAgICAgY29uc29sZS53YXJuKCdEZXRlY3RlZCB1c2FnZSBvZiBkZXByZWNhdGVkIGRhdGEtamFyYWxsYXggSlNPTiBvcHRpb25zLCB5b3Ugc2hvdWxkIHVzZSBwdXJlIGRhdGEtYXR0cmlidXRlIG9wdGlvbnMuIFNlZSBpbmZvIGhlcmUgLSBodHRwczovL2dpdGh1Yi5jb20vbmstby9qYXJhbGxheC9pc3N1ZXMvNTMnKTtcbiAgICAgICAgfVxuXG4gICAgICAgIC8vIHByZXBhcmUgZGF0YS1vcHRpb25zXG4gICAgICAgIGNvbnN0IGRhdGFPcHRpb25zID0gc2VsZi4kaXRlbS5kYXRhc2V0IHx8IHt9O1xuICAgICAgICBjb25zdCBwdXJlRGF0YU9wdGlvbnMgPSB7fTtcbiAgICAgICAgT2JqZWN0LmtleXMoZGF0YU9wdGlvbnMpLmZvckVhY2goKGtleSkgPT4ge1xuICAgICAgICAgICAgY29uc3QgbG93ZUNhc2VPcHRpb24gPSBrZXkuc3Vic3RyKDAsIDEpLnRvTG93ZXJDYXNlKCkgKyBrZXkuc3Vic3RyKDEpO1xuICAgICAgICAgICAgaWYgKGxvd2VDYXNlT3B0aW9uICYmIHR5cGVvZiBzZWxmLmRlZmF1bHRzW2xvd2VDYXNlT3B0aW9uXSAhPT0gJ3VuZGVmaW5lZCcpIHtcbiAgICAgICAgICAgICAgICBwdXJlRGF0YU9wdGlvbnNbbG93ZUNhc2VPcHRpb25dID0gZGF0YU9wdGlvbnNba2V5XTtcbiAgICAgICAgICAgIH1cbiAgICAgICAgfSk7XG5cbiAgICAgICAgc2VsZi5vcHRpb25zID0gc2VsZi5leHRlbmQoe30sIHNlbGYuZGVmYXVsdHMsIG9sZERhdGFPcHRpb25zLCBwdXJlRGF0YU9wdGlvbnMsIHVzZXJPcHRpb25zKTtcbiAgICAgICAgc2VsZi5wdXJlT3B0aW9ucyA9IHNlbGYuZXh0ZW5kKHt9LCBzZWxmLm9wdGlvbnMpO1xuXG4gICAgICAgIC8vIHByZXBhcmUgJ3RydWUnIGFuZCAnZmFsc2UnIHN0cmluZ3MgdG8gYm9vbGVhblxuICAgICAgICBPYmplY3Qua2V5cyhzZWxmLm9wdGlvbnMpLmZvckVhY2goKGtleSkgPT4ge1xuICAgICAgICAgICAgaWYgKHNlbGYub3B0aW9uc1trZXldID09PSAndHJ1ZScpIHtcbiAgICAgICAgICAgICAgICBzZWxmLm9wdGlvbnNba2V5XSA9IHRydWU7XG4gICAgICAgICAgICB9IGVsc2UgaWYgKHNlbGYub3B0aW9uc1trZXldID09PSAnZmFsc2UnKSB7XG4gICAgICAgICAgICAgICAgc2VsZi5vcHRpb25zW2tleV0gPSBmYWxzZTtcbiAgICAgICAgICAgIH1cbiAgICAgICAgfSk7XG5cbiAgICAgICAgLy8gZml4IHNwZWVkIG9wdGlvbiBbLTEuMCwgMi4wXVxuICAgICAgICBzZWxmLm9wdGlvbnMuc3BlZWQgPSBNYXRoLm1pbigyLCBNYXRoLm1heCgtMSwgcGFyc2VGbG9hdChzZWxmLm9wdGlvbnMuc3BlZWQpKSk7XG5cbiAgICAgICAgLy8gZGVwcmVjYXRlZCBub0FuZHJvaWQgYW5kIG5vSW9zIG9wdGlvbnNcbiAgICAgICAgaWYgKHNlbGYub3B0aW9ucy5ub0FuZHJvaWQgfHwgc2VsZi5vcHRpb25zLm5vSW9zKSB7XG4gICAgICAgICAgICAvLyBlc2xpbnQtZGlzYWJsZS1uZXh0LWxpbmUgbm8tY29uc29sZVxuICAgICAgICAgICAgY29uc29sZS53YXJuKCdEZXRlY3RlZCB1c2FnZSBvZiBkZXByZWNhdGVkIG5vQW5kcm9pZCBvciBub0lvcyBvcHRpb25zLCB5b3Ugc2hvdWxkIHVzZSBkaXNhYmxlUGFyYWxsYXggb3B0aW9uLiBTZWUgaW5mbyBoZXJlIC0gaHR0cHM6Ly9naXRodWIuY29tL25rLW8vamFyYWxsYXgvI2Rpc2FibGUtb24tbW9iaWxlLWRldmljZXMnKTtcblxuICAgICAgICAgICAgLy8gcHJlcGFyZSBmYWxsYmFjayBpZiBkaXNhYmxlUGFyYWxsYXggb3B0aW9uIGlzIG5vdCB1c2VkXG4gICAgICAgICAgICBpZiAoIXNlbGYub3B0aW9ucy5kaXNhYmxlUGFyYWxsYXgpIHtcbiAgICAgICAgICAgICAgICBpZiAoc2VsZi5vcHRpb25zLm5vSW9zICYmIHNlbGYub3B0aW9ucy5ub0FuZHJvaWQpIHtcbiAgICAgICAgICAgICAgICAgICAgc2VsZi5vcHRpb25zLmRpc2FibGVQYXJhbGxheCA9IC9pUGFkfGlQaG9uZXxpUG9kfEFuZHJvaWQvO1xuICAgICAgICAgICAgICAgIH0gZWxzZSBpZiAoc2VsZi5vcHRpb25zLm5vSW9zKSB7XG4gICAgICAgICAgICAgICAgICAgIHNlbGYub3B0aW9ucy5kaXNhYmxlUGFyYWxsYXggPSAvaVBhZHxpUGhvbmV8aVBvZC87XG4gICAgICAgICAgICAgICAgfSBlbHNlIGlmIChzZWxmLm9wdGlvbnMubm9BbmRyb2lkKSB7XG4gICAgICAgICAgICAgICAgICAgIHNlbGYub3B0aW9ucy5kaXNhYmxlUGFyYWxsYXggPSAvQW5kcm9pZC87XG4gICAgICAgICAgICAgICAgfVxuICAgICAgICAgICAgfVxuICAgICAgICB9XG5cbiAgICAgICAgLy8gcHJlcGFyZSBkaXNhYmxlUGFyYWxsYXggY2FsbGJhY2tcbiAgICAgICAgaWYgKHR5cGVvZiBzZWxmLm9wdGlvbnMuZGlzYWJsZVBhcmFsbGF4ID09PSAnc3RyaW5nJykge1xuICAgICAgICAgICAgc2VsZi5vcHRpb25zLmRpc2FibGVQYXJhbGxheCA9IG5ldyBSZWdFeHAoc2VsZi5vcHRpb25zLmRpc2FibGVQYXJhbGxheCk7XG4gICAgICAgIH1cbiAgICAgICAgaWYgKHNlbGYub3B0aW9ucy5kaXNhYmxlUGFyYWxsYXggaW5zdGFuY2VvZiBSZWdFeHApIHtcbiAgICAgICAgICAgIGNvbnN0IGRpc2FibGVQYXJhbGxheFJlZ2V4cCA9IHNlbGYub3B0aW9ucy5kaXNhYmxlUGFyYWxsYXg7XG4gICAgICAgICAgICBzZWxmLm9wdGlvbnMuZGlzYWJsZVBhcmFsbGF4ID0gKCkgPT4gZGlzYWJsZVBhcmFsbGF4UmVnZXhwLnRlc3QobmF2aWdhdG9yLnVzZXJBZ2VudCk7XG4gICAgICAgIH1cbiAgICAgICAgaWYgKHR5cGVvZiBzZWxmLm9wdGlvbnMuZGlzYWJsZVBhcmFsbGF4ICE9PSAnZnVuY3Rpb24nKSB7XG4gICAgICAgICAgICBzZWxmLm9wdGlvbnMuZGlzYWJsZVBhcmFsbGF4ID0gKCkgPT4gZmFsc2U7XG4gICAgICAgIH1cblxuICAgICAgICAvLyBwcmVwYXJlIGRpc2FibGVWaWRlbyBjYWxsYmFja1xuICAgICAgICBpZiAodHlwZW9mIHNlbGYub3B0aW9ucy5kaXNhYmxlVmlkZW8gPT09ICdzdHJpbmcnKSB7XG4gICAgICAgICAgICBzZWxmLm9wdGlvbnMuZGlzYWJsZVZpZGVvID0gbmV3IFJlZ0V4cChzZWxmLm9wdGlvbnMuZGlzYWJsZVZpZGVvKTtcbiAgICAgICAgfVxuICAgICAgICBpZiAoc2VsZi5vcHRpb25zLmRpc2FibGVWaWRlbyBpbnN0YW5jZW9mIFJlZ0V4cCkge1xuICAgICAgICAgICAgY29uc3QgZGlzYWJsZVZpZGVvUmVnZXhwID0gc2VsZi5vcHRpb25zLmRpc2FibGVWaWRlbztcbiAgICAgICAgICAgIHNlbGYub3B0aW9ucy5kaXNhYmxlVmlkZW8gPSAoKSA9PiBkaXNhYmxlVmlkZW9SZWdleHAudGVzdChuYXZpZ2F0b3IudXNlckFnZW50KTtcbiAgICAgICAgfVxuICAgICAgICBpZiAodHlwZW9mIHNlbGYub3B0aW9ucy5kaXNhYmxlVmlkZW8gIT09ICdmdW5jdGlvbicpIHtcbiAgICAgICAgICAgIHNlbGYub3B0aW9ucy5kaXNhYmxlVmlkZW8gPSAoKSA9PiBmYWxzZTtcbiAgICAgICAgfVxuXG4gICAgICAgIC8vIGN1c3RvbSBlbGVtZW50IHRvIGNoZWNrIGlmIHBhcmFsbGF4IGluIHZpZXdwb3J0XG4gICAgICAgIGxldCBlbGVtZW50SW5WUCA9IHNlbGYub3B0aW9ucy5lbGVtZW50SW5WaWV3cG9ydDtcbiAgICAgICAgLy8gZ2V0IGZpcnN0IGl0ZW0gZnJvbSBhcnJheVxuICAgICAgICBpZiAoZWxlbWVudEluVlAgJiYgdHlwZW9mIGVsZW1lbnRJblZQID09PSAnb2JqZWN0JyAmJiB0eXBlb2YgZWxlbWVudEluVlAubGVuZ3RoICE9PSAndW5kZWZpbmVkJykge1xuICAgICAgICAgICAgW2VsZW1lbnRJblZQXSA9IGVsZW1lbnRJblZQO1xuICAgICAgICB9XG4gICAgICAgIC8vIGNoZWNrIGlmIGRvbSBlbGVtZW50XG4gICAgICAgIGlmICghKGVsZW1lbnRJblZQIGluc3RhbmNlb2YgRWxlbWVudCkpIHtcbiAgICAgICAgICAgIGVsZW1lbnRJblZQID0gbnVsbDtcbiAgICAgICAgfVxuICAgICAgICBzZWxmLm9wdGlvbnMuZWxlbWVudEluVmlld3BvcnQgPSBlbGVtZW50SW5WUDtcblxuICAgICAgICBzZWxmLmltYWdlID0ge1xuICAgICAgICAgICAgc3JjOiBzZWxmLm9wdGlvbnMuaW1nU3JjIHx8IG51bGwsXG4gICAgICAgICAgICAkY29udGFpbmVyOiBudWxsLFxuICAgICAgICAgICAgdXNlSW1nVGFnOiBmYWxzZSxcblxuICAgICAgICAgICAgLy8gcG9zaXRpb24gZml4ZWQgaXMgbmVlZGVkIGZvciB0aGUgbW9zdCBvZiBicm93c2VycyBiZWNhdXNlIGFic29sdXRlIHBvc2l0aW9uIGhhdmUgZ2xpdGNoZXNcbiAgICAgICAgICAgIC8vIG9uIE1hY09TIHdpdGggc21vb3RoIHNjcm9sbCB0aGVyZSBpcyBhIGh1Z2UgbGFncyB3aXRoIGFic29sdXRlIHBvc2l0aW9uIC0gaHR0cHM6Ly9naXRodWIuY29tL25rLW8vamFyYWxsYXgvaXNzdWVzLzc1XG4gICAgICAgICAgICAvLyBvbiBtb2JpbGUgZGV2aWNlcyBiZXR0ZXIgc2Nyb2xsZWQgd2l0aCBhYnNvbHV0ZSBwb3NpdGlvblxuICAgICAgICAgICAgcG9zaXRpb246IC9pUGFkfGlQaG9uZXxpUG9kfEFuZHJvaWQvLnRlc3QobmF2aWdhdG9yLnVzZXJBZ2VudCkgPyAnYWJzb2x1dGUnIDogJ2ZpeGVkJyxcbiAgICAgICAgfTtcblxuICAgICAgICBpZiAoc2VsZi5pbml0SW1nKCkgJiYgc2VsZi5jYW5Jbml0UGFyYWxsYXgoKSkge1xuICAgICAgICAgICAgc2VsZi5pbml0KCk7XG4gICAgICAgIH1cbiAgICB9XG5cbiAgICAvLyBhZGQgc3R5bGVzIHRvIGVsZW1lbnRcbiAgICBjc3MoZWwsIHN0eWxlcykge1xuICAgICAgICBpZiAodHlwZW9mIHN0eWxlcyA9PT0gJ3N0cmluZycpIHtcbiAgICAgICAgICAgIHJldHVybiB3aW5kb3cuZ2V0Q29tcHV0ZWRTdHlsZShlbCkuZ2V0UHJvcGVydHlWYWx1ZShzdHlsZXMpO1xuICAgICAgICB9XG5cbiAgICAgICAgLy8gYWRkIHRyYW5zZm9ybSBwcm9wZXJ0eSB3aXRoIHZlbmRvciBwcmVmaXhcbiAgICAgICAgaWYgKHN0eWxlcy50cmFuc2Zvcm0gJiYgc3VwcG9ydFRyYW5zZm9ybSkge1xuICAgICAgICAgICAgc3R5bGVzW3N1cHBvcnRUcmFuc2Zvcm1dID0gc3R5bGVzLnRyYW5zZm9ybTtcbiAgICAgICAgfVxuXG4gICAgICAgIE9iamVjdC5rZXlzKHN0eWxlcykuZm9yRWFjaCgoa2V5KSA9PiB7XG4gICAgICAgICAgICBlbC5zdHlsZVtrZXldID0gc3R5bGVzW2tleV07XG4gICAgICAgIH0pO1xuICAgICAgICByZXR1cm4gZWw7XG4gICAgfVxuXG4gICAgLy8gRXh0ZW5kIGxpa2UgalF1ZXJ5LmV4dGVuZFxuICAgIGV4dGVuZChvdXQpIHtcbiAgICAgICAgb3V0ID0gb3V0IHx8IHt9O1xuICAgICAgICBPYmplY3Qua2V5cyhhcmd1bWVudHMpLmZvckVhY2goKGkpID0+IHtcbiAgICAgICAgICAgIGlmICghYXJndW1lbnRzW2ldKSB7XG4gICAgICAgICAgICAgICAgcmV0dXJuO1xuICAgICAgICAgICAgfVxuICAgICAgICAgICAgT2JqZWN0LmtleXMoYXJndW1lbnRzW2ldKS5mb3JFYWNoKChrZXkpID0+IHtcbiAgICAgICAgICAgICAgICBvdXRba2V5XSA9IGFyZ3VtZW50c1tpXVtrZXldO1xuICAgICAgICAgICAgfSk7XG4gICAgICAgIH0pO1xuICAgICAgICByZXR1cm4gb3V0O1xuICAgIH1cblxuICAgIC8vIGdldCB3aW5kb3cgc2l6ZSBhbmQgc2Nyb2xsIHBvc2l0aW9uLiBVc2VmdWwgZm9yIGV4dGVuc2lvbnNcbiAgICBnZXRXaW5kb3dEYXRhKCkge1xuICAgICAgICByZXR1cm4ge1xuICAgICAgICAgICAgd2lkdGg6IHduZFcsXG4gICAgICAgICAgICBoZWlnaHQ6IHduZEgsXG4gICAgICAgICAgICB5OiB3bmRZLFxuICAgICAgICB9O1xuICAgIH1cblxuICAgIC8vIEphcmFsbGF4IGZ1bmN0aW9uc1xuICAgIGluaXRJbWcoKSB7XG4gICAgICAgIGNvbnN0IHNlbGYgPSB0aGlzO1xuXG4gICAgICAgIC8vIGZpbmQgaW1hZ2UgZWxlbWVudFxuICAgICAgICBsZXQgJGltZ0VsZW1lbnQgPSBzZWxmLm9wdGlvbnMuaW1nRWxlbWVudDtcbiAgICAgICAgaWYgKCRpbWdFbGVtZW50ICYmIHR5cGVvZiAkaW1nRWxlbWVudCA9PT0gJ3N0cmluZycpIHtcbiAgICAgICAgICAgICRpbWdFbGVtZW50ID0gc2VsZi4kaXRlbS5xdWVyeVNlbGVjdG9yKCRpbWdFbGVtZW50KTtcbiAgICAgICAgfVxuICAgICAgICAvLyBjaGVjayBpZiBkb20gZWxlbWVudFxuICAgICAgICBpZiAoISgkaW1nRWxlbWVudCBpbnN0YW5jZW9mIEVsZW1lbnQpKSB7XG4gICAgICAgICAgICAkaW1nRWxlbWVudCA9IG51bGw7XG4gICAgICAgIH1cblxuICAgICAgICBpZiAoJGltZ0VsZW1lbnQpIHtcbiAgICAgICAgICAgIGlmIChzZWxmLm9wdGlvbnMua2VlcEltZykge1xuICAgICAgICAgICAgICAgIHNlbGYuaW1hZ2UuJGl0ZW0gPSAkaW1nRWxlbWVudC5jbG9uZU5vZGUodHJ1ZSk7XG4gICAgICAgICAgICB9IGVsc2Uge1xuICAgICAgICAgICAgICAgIHNlbGYuaW1hZ2UuJGl0ZW0gPSAkaW1nRWxlbWVudDtcbiAgICAgICAgICAgICAgICBzZWxmLmltYWdlLiRpdGVtUGFyZW50ID0gJGltZ0VsZW1lbnQucGFyZW50Tm9kZTtcbiAgICAgICAgICAgIH1cbiAgICAgICAgICAgIHNlbGYuaW1hZ2UudXNlSW1nVGFnID0gdHJ1ZTtcbiAgICAgICAgfVxuXG4gICAgICAgIC8vIHRydWUgaWYgdGhlcmUgaXMgaW1nIHRhZ1xuICAgICAgICBpZiAoc2VsZi5pbWFnZS4kaXRlbSkge1xuICAgICAgICAgICAgcmV0dXJuIHRydWU7XG4gICAgICAgIH1cblxuICAgICAgICAvLyBnZXQgaW1hZ2Ugc3JjXG4gICAgICAgIGlmIChzZWxmLmltYWdlLnNyYyA9PT0gbnVsbCkge1xuICAgICAgICAgICAgc2VsZi5pbWFnZS5zcmMgPSBzZWxmLmNzcyhzZWxmLiRpdGVtLCAnYmFja2dyb3VuZC1pbWFnZScpLnJlcGxhY2UoL151cmxcXChbJ1wiXT8vZywgJycpLnJlcGxhY2UoL1snXCJdP1xcKSQvZywgJycpO1xuICAgICAgICB9XG4gICAgICAgIHJldHVybiAhKCFzZWxmLmltYWdlLnNyYyB8fCBzZWxmLmltYWdlLnNyYyA9PT0gJ25vbmUnKTtcbiAgICB9XG5cbiAgICBjYW5Jbml0UGFyYWxsYXgoKSB7XG4gICAgICAgIHJldHVybiBzdXBwb3J0VHJhbnNmb3JtICYmICF0aGlzLm9wdGlvbnMuZGlzYWJsZVBhcmFsbGF4KCk7XG4gICAgfVxuXG4gICAgaW5pdCgpIHtcbiAgICAgICAgY29uc3Qgc2VsZiA9IHRoaXM7XG4gICAgICAgIGNvbnN0IGNvbnRhaW5lclN0eWxlcyA9IHtcbiAgICAgICAgICAgIHBvc2l0aW9uOiAnYWJzb2x1dGUnLFxuICAgICAgICAgICAgdG9wOiAwLFxuICAgICAgICAgICAgbGVmdDogMCxcbiAgICAgICAgICAgIHdpZHRoOiAnMTAwJScsXG4gICAgICAgICAgICBoZWlnaHQ6ICcxMDAlJyxcbiAgICAgICAgICAgIG92ZXJmbG93OiAnaGlkZGVuJyxcbiAgICAgICAgICAgIHBvaW50ZXJFdmVudHM6ICdub25lJyxcbiAgICAgICAgfTtcbiAgICAgICAgbGV0IGltYWdlU3R5bGVzID0ge307XG5cbiAgICAgICAgaWYgKCFzZWxmLm9wdGlvbnMua2VlcEltZykge1xuICAgICAgICAgICAgLy8gc2F2ZSBkZWZhdWx0IHVzZXIgc3R5bGVzXG4gICAgICAgICAgICBjb25zdCBjdXJTdHlsZSA9IHNlbGYuJGl0ZW0uZ2V0QXR0cmlidXRlKCdzdHlsZScpO1xuICAgICAgICAgICAgaWYgKGN1clN0eWxlKSB7XG4gICAgICAgICAgICAgICAgc2VsZi4kaXRlbS5zZXRBdHRyaWJ1dGUoJ2RhdGEtamFyYWxsYXgtb3JpZ2luYWwtc3R5bGVzJywgY3VyU3R5bGUpO1xuICAgICAgICAgICAgfVxuICAgICAgICAgICAgaWYgKHNlbGYuaW1hZ2UudXNlSW1nVGFnKSB7XG4gICAgICAgICAgICAgICAgY29uc3QgY3VySW1nU3R5bGUgPSBzZWxmLmltYWdlLiRpdGVtLmdldEF0dHJpYnV0ZSgnc3R5bGUnKTtcbiAgICAgICAgICAgICAgICBpZiAoY3VySW1nU3R5bGUpIHtcbiAgICAgICAgICAgICAgICAgICAgc2VsZi5pbWFnZS4kaXRlbS5zZXRBdHRyaWJ1dGUoJ2RhdGEtamFyYWxsYXgtb3JpZ2luYWwtc3R5bGVzJywgY3VySW1nU3R5bGUpO1xuICAgICAgICAgICAgICAgIH1cbiAgICAgICAgICAgIH1cbiAgICAgICAgfVxuXG4gICAgICAgIC8vIHNldCByZWxhdGl2ZSBwb3NpdGlvbiBhbmQgei1pbmRleCB0byB0aGUgcGFyZW50XG4gICAgICAgIGlmIChzZWxmLmNzcyhzZWxmLiRpdGVtLCAncG9zaXRpb24nKSA9PT0gJ3N0YXRpYycpIHtcbiAgICAgICAgICAgIHNlbGYuY3NzKHNlbGYuJGl0ZW0sIHtcbiAgICAgICAgICAgICAgICBwb3NpdGlvbjogJ3JlbGF0aXZlJyxcbiAgICAgICAgICAgIH0pO1xuICAgICAgICB9XG4gICAgICAgIGlmIChzZWxmLmNzcyhzZWxmLiRpdGVtLCAnei1pbmRleCcpID09PSAnYXV0bycpIHtcbiAgICAgICAgICAgIHNlbGYuY3NzKHNlbGYuJGl0ZW0sIHtcbiAgICAgICAgICAgICAgICB6SW5kZXg6IDAsXG4gICAgICAgICAgICB9KTtcbiAgICAgICAgfVxuXG4gICAgICAgIC8vIGNvbnRhaW5lciBmb3IgcGFyYWxsYXggaW1hZ2VcbiAgICAgICAgc2VsZi5pbWFnZS4kY29udGFpbmVyID0gZG9jdW1lbnQuY3JlYXRlRWxlbWVudCgnZGl2Jyk7XG4gICAgICAgIHNlbGYuY3NzKHNlbGYuaW1hZ2UuJGNvbnRhaW5lciwgY29udGFpbmVyU3R5bGVzKTtcbiAgICAgICAgc2VsZi5jc3Moc2VsZi5pbWFnZS4kY29udGFpbmVyLCB7XG4gICAgICAgICAgICAnei1pbmRleCc6IHNlbGYub3B0aW9ucy56SW5kZXgsXG4gICAgICAgIH0pO1xuXG4gICAgICAgIC8vIGZpeCBmb3IgSUUgaHR0cHM6Ly9naXRodWIuY29tL25rLW8vamFyYWxsYXgvaXNzdWVzLzExMFxuICAgICAgICBpZiAoaXNJRSkge1xuICAgICAgICAgICAgc2VsZi5jc3Moc2VsZi5pbWFnZS4kY29udGFpbmVyLCB7XG4gICAgICAgICAgICAgICAgb3BhY2l0eTogMC45OTk5LFxuICAgICAgICAgICAgfSk7XG4gICAgICAgIH1cblxuICAgICAgICBzZWxmLmltYWdlLiRjb250YWluZXIuc2V0QXR0cmlidXRlKCdpZCcsIGBqYXJhbGxheC1jb250YWluZXItJHtzZWxmLmluc3RhbmNlSUR9YCk7XG4gICAgICAgIHNlbGYuJGl0ZW0uYXBwZW5kQ2hpbGQoc2VsZi5pbWFnZS4kY29udGFpbmVyKTtcblxuICAgICAgICAvLyB1c2UgaW1nIHRhZ1xuICAgICAgICBpZiAoc2VsZi5pbWFnZS51c2VJbWdUYWcpIHtcbiAgICAgICAgICAgIGltYWdlU3R5bGVzID0gc2VsZi5leHRlbmQoe1xuICAgICAgICAgICAgICAgICdvYmplY3QtZml0Jzogc2VsZi5vcHRpb25zLmltZ1NpemUsXG4gICAgICAgICAgICAgICAgJ29iamVjdC1wb3NpdGlvbic6IHNlbGYub3B0aW9ucy5pbWdQb3NpdGlvbixcbiAgICAgICAgICAgICAgICAvLyBzdXBwb3J0IGZvciBwbHVnaW4gaHR0cHM6Ly9naXRodWIuY29tL2JmcmVkLWl0L29iamVjdC1maXQtaW1hZ2VzXG4gICAgICAgICAgICAgICAgJ2ZvbnQtZmFtaWx5JzogYG9iamVjdC1maXQ6ICR7c2VsZi5vcHRpb25zLmltZ1NpemV9OyBvYmplY3QtcG9zaXRpb246ICR7c2VsZi5vcHRpb25zLmltZ1Bvc2l0aW9ufTtgLFxuICAgICAgICAgICAgICAgICdtYXgtd2lkdGgnOiAnbm9uZScsXG4gICAgICAgICAgICB9LCBjb250YWluZXJTdHlsZXMsIGltYWdlU3R5bGVzKTtcblxuICAgICAgICAvLyB1c2UgZGl2IHdpdGggYmFja2dyb3VuZCBpbWFnZVxuICAgICAgICB9IGVsc2Uge1xuICAgICAgICAgICAgc2VsZi5pbWFnZS4kaXRlbSA9IGRvY3VtZW50LmNyZWF0ZUVsZW1lbnQoJ2RpdicpO1xuICAgICAgICAgICAgaWYgKHNlbGYuaW1hZ2Uuc3JjKSB7XG4gICAgICAgICAgICAgICAgaW1hZ2VTdHlsZXMgPSBzZWxmLmV4dGVuZCh7XG4gICAgICAgICAgICAgICAgICAgICdiYWNrZ3JvdW5kLXBvc2l0aW9uJzogc2VsZi5vcHRpb25zLmltZ1Bvc2l0aW9uLFxuICAgICAgICAgICAgICAgICAgICAnYmFja2dyb3VuZC1zaXplJzogc2VsZi5vcHRpb25zLmltZ1NpemUsXG4gICAgICAgICAgICAgICAgICAgICdiYWNrZ3JvdW5kLXJlcGVhdCc6IHNlbGYub3B0aW9ucy5pbWdSZXBlYXQsXG4gICAgICAgICAgICAgICAgICAgICdiYWNrZ3JvdW5kLWltYWdlJzogYHVybChcIiR7c2VsZi5pbWFnZS5zcmN9XCIpYCxcbiAgICAgICAgICAgICAgICB9LCBjb250YWluZXJTdHlsZXMsIGltYWdlU3R5bGVzKTtcbiAgICAgICAgICAgIH1cbiAgICAgICAgfVxuXG4gICAgICAgIGlmIChzZWxmLm9wdGlvbnMudHlwZSA9PT0gJ29wYWNpdHknIHx8IHNlbGYub3B0aW9ucy50eXBlID09PSAnc2NhbGUnIHx8IHNlbGYub3B0aW9ucy50eXBlID09PSAnc2NhbGUtb3BhY2l0eScgfHwgc2VsZi5vcHRpb25zLnNwZWVkID09PSAxKSB7XG4gICAgICAgICAgICBzZWxmLmltYWdlLnBvc2l0aW9uID0gJ2Fic29sdXRlJztcbiAgICAgICAgfVxuXG4gICAgICAgIC8vIGNoZWNrIGlmIG9uZSBvZiBwYXJlbnRzIGhhdmUgdHJhbnNmb3JtIHN0eWxlICh3aXRob3V0IHRoaXMgY2hlY2ssIHNjcm9sbCB0cmFuc2Zvcm0gd2lsbCBiZSBpbnZlcnRlZCBpZiB1c2VkIHBhcmFsbGF4IHdpdGggcG9zaXRpb24gZml4ZWQpXG4gICAgICAgIC8vIGRpc2N1c3Npb24gLSBodHRwczovL2dpdGh1Yi5jb20vbmstby9qYXJhbGxheC9pc3N1ZXMvOVxuICAgICAgICBpZiAoc2VsZi5pbWFnZS5wb3NpdGlvbiA9PT0gJ2ZpeGVkJykge1xuICAgICAgICAgICAgbGV0IHBhcmVudFdpdGhUcmFuc2Zvcm0gPSAwO1xuICAgICAgICAgICAgbGV0ICRpdGVtUGFyZW50cyA9IHNlbGYuJGl0ZW07XG4gICAgICAgICAgICB3aGlsZSAoJGl0ZW1QYXJlbnRzICE9PSBudWxsICYmICRpdGVtUGFyZW50cyAhPT0gZG9jdW1lbnQgJiYgcGFyZW50V2l0aFRyYW5zZm9ybSA9PT0gMCkge1xuICAgICAgICAgICAgICAgIGNvbnN0IHBhcmVudFRyYW5zZm9ybSA9IHNlbGYuY3NzKCRpdGVtUGFyZW50cywgJy13ZWJraXQtdHJhbnNmb3JtJykgfHwgc2VsZi5jc3MoJGl0ZW1QYXJlbnRzLCAnLW1vei10cmFuc2Zvcm0nKSB8fCBzZWxmLmNzcygkaXRlbVBhcmVudHMsICd0cmFuc2Zvcm0nKTtcbiAgICAgICAgICAgICAgICBpZiAocGFyZW50VHJhbnNmb3JtICYmIHBhcmVudFRyYW5zZm9ybSAhPT0gJ25vbmUnKSB7XG4gICAgICAgICAgICAgICAgICAgIHBhcmVudFdpdGhUcmFuc2Zvcm0gPSAxO1xuICAgICAgICAgICAgICAgICAgICBzZWxmLmltYWdlLnBvc2l0aW9uID0gJ2Fic29sdXRlJztcbiAgICAgICAgICAgICAgICB9XG4gICAgICAgICAgICAgICAgJGl0ZW1QYXJlbnRzID0gJGl0ZW1QYXJlbnRzLnBhcmVudE5vZGU7XG4gICAgICAgICAgICB9XG4gICAgICAgIH1cblxuICAgICAgICAvLyBhZGQgcG9zaXRpb24gdG8gcGFyYWxsYXggYmxvY2tcbiAgICAgICAgaW1hZ2VTdHlsZXMucG9zaXRpb24gPSBzZWxmLmltYWdlLnBvc2l0aW9uO1xuXG4gICAgICAgIC8vIGluc2VydCBwYXJhbGxheCBpbWFnZVxuICAgICAgICBzZWxmLmNzcyhzZWxmLmltYWdlLiRpdGVtLCBpbWFnZVN0eWxlcyk7XG4gICAgICAgIHNlbGYuaW1hZ2UuJGNvbnRhaW5lci5hcHBlbmRDaGlsZChzZWxmLmltYWdlLiRpdGVtKTtcblxuICAgICAgICAvLyBzZXQgaW5pdGlhbCBwb3NpdGlvbiBhbmQgc2l6ZVxuICAgICAgICBzZWxmLm9uUmVzaXplKCk7XG4gICAgICAgIHNlbGYub25TY3JvbGwodHJ1ZSk7XG5cbiAgICAgICAgLy8gUmVzaXplT2JzZXJ2ZXJcbiAgICAgICAgaWYgKHNlbGYub3B0aW9ucy5hdXRvbWF0aWNSZXNpemUgJiYgcmVzaXplT2JzZXJ2ZXIpIHtcbiAgICAgICAgICAgIHJlc2l6ZU9ic2VydmVyLm9ic2VydmUoc2VsZi4kaXRlbSk7XG4gICAgICAgIH1cblxuICAgICAgICAvLyBjYWxsIG9uSW5pdCBldmVudFxuICAgICAgICBpZiAoc2VsZi5vcHRpb25zLm9uSW5pdCkge1xuICAgICAgICAgICAgc2VsZi5vcHRpb25zLm9uSW5pdC5jYWxsKHNlbGYpO1xuICAgICAgICB9XG5cbiAgICAgICAgLy8gcmVtb3ZlIGRlZmF1bHQgdXNlciBiYWNrZ3JvdW5kXG4gICAgICAgIGlmIChzZWxmLmNzcyhzZWxmLiRpdGVtLCAnYmFja2dyb3VuZC1pbWFnZScpICE9PSAnbm9uZScpIHtcbiAgICAgICAgICAgIHNlbGYuY3NzKHNlbGYuJGl0ZW0sIHtcbiAgICAgICAgICAgICAgICAnYmFja2dyb3VuZC1pbWFnZSc6ICdub25lJyxcbiAgICAgICAgICAgIH0pO1xuICAgICAgICB9XG5cbiAgICAgICAgc2VsZi5hZGRUb1BhcmFsbGF4TGlzdCgpO1xuICAgIH1cblxuICAgIC8vIGFkZCB0byBwYXJhbGxheCBpbnN0YW5jZXMgbGlzdFxuICAgIGFkZFRvUGFyYWxsYXhMaXN0KCkge1xuICAgICAgICBqYXJhbGxheExpc3QucHVzaCh0aGlzKTtcblxuICAgICAgICBpZiAoamFyYWxsYXhMaXN0Lmxlbmd0aCA9PT0gMSkge1xuICAgICAgICAgICAgdXBkYXRlUGFyYWxsYXgoKTtcbiAgICAgICAgfVxuICAgIH1cblxuICAgIC8vIHJlbW92ZSBmcm9tIHBhcmFsbGF4IGluc3RhbmNlcyBsaXN0XG4gICAgcmVtb3ZlRnJvbVBhcmFsbGF4TGlzdCgpIHtcbiAgICAgICAgY29uc3Qgc2VsZiA9IHRoaXM7XG5cbiAgICAgICAgamFyYWxsYXhMaXN0LmZvckVhY2goKGl0ZW0sIGtleSkgPT4ge1xuICAgICAgICAgICAgaWYgKGl0ZW0uaW5zdGFuY2VJRCA9PT0gc2VsZi5pbnN0YW5jZUlEKSB7XG4gICAgICAgICAgICAgICAgamFyYWxsYXhMaXN0LnNwbGljZShrZXksIDEpO1xuICAgICAgICAgICAgfVxuICAgICAgICB9KTtcbiAgICB9XG5cbiAgICBkZXN0cm95KCkge1xuICAgICAgICBjb25zdCBzZWxmID0gdGhpcztcblxuICAgICAgICBzZWxmLnJlbW92ZUZyb21QYXJhbGxheExpc3QoKTtcblxuICAgICAgICAvLyByZXR1cm4gc3R5bGVzIG9uIGNvbnRhaW5lciBhcyBiZWZvcmUgamFyYWxsYXggaW5pdFxuICAgICAgICBjb25zdCBvcmlnaW5hbFN0eWxlc1RhZyA9IHNlbGYuJGl0ZW0uZ2V0QXR0cmlidXRlKCdkYXRhLWphcmFsbGF4LW9yaWdpbmFsLXN0eWxlcycpO1xuICAgICAgICBzZWxmLiRpdGVtLnJlbW92ZUF0dHJpYnV0ZSgnZGF0YS1qYXJhbGxheC1vcmlnaW5hbC1zdHlsZXMnKTtcbiAgICAgICAgLy8gbnVsbCBvY2N1cnMgaWYgdGhlcmUgaXMgbm8gc3R5bGUgdGFnIGJlZm9yZSBqYXJhbGxheCBpbml0XG4gICAgICAgIGlmICghb3JpZ2luYWxTdHlsZXNUYWcpIHtcbiAgICAgICAgICAgIHNlbGYuJGl0ZW0ucmVtb3ZlQXR0cmlidXRlKCdzdHlsZScpO1xuICAgICAgICB9IGVsc2Uge1xuICAgICAgICAgICAgc2VsZi4kaXRlbS5zZXRBdHRyaWJ1dGUoJ3N0eWxlJywgb3JpZ2luYWxTdHlsZXNUYWcpO1xuICAgICAgICB9XG5cbiAgICAgICAgaWYgKHNlbGYuaW1hZ2UudXNlSW1nVGFnKSB7XG4gICAgICAgICAgICAvLyByZXR1cm4gc3R5bGVzIG9uIGltZyB0YWcgYXMgYmVmb3JlIGphcmFsbGF4IGluaXRcbiAgICAgICAgICAgIGNvbnN0IG9yaWdpbmFsU3R5bGVzSW1nVGFnID0gc2VsZi5pbWFnZS4kaXRlbS5nZXRBdHRyaWJ1dGUoJ2RhdGEtamFyYWxsYXgtb3JpZ2luYWwtc3R5bGVzJyk7XG4gICAgICAgICAgICBzZWxmLmltYWdlLiRpdGVtLnJlbW92ZUF0dHJpYnV0ZSgnZGF0YS1qYXJhbGxheC1vcmlnaW5hbC1zdHlsZXMnKTtcbiAgICAgICAgICAgIC8vIG51bGwgb2NjdXJzIGlmIHRoZXJlIGlzIG5vIHN0eWxlIHRhZyBiZWZvcmUgamFyYWxsYXggaW5pdFxuICAgICAgICAgICAgaWYgKCFvcmlnaW5hbFN0eWxlc0ltZ1RhZykge1xuICAgICAgICAgICAgICAgIHNlbGYuaW1hZ2UuJGl0ZW0ucmVtb3ZlQXR0cmlidXRlKCdzdHlsZScpO1xuICAgICAgICAgICAgfSBlbHNlIHtcbiAgICAgICAgICAgICAgICBzZWxmLmltYWdlLiRpdGVtLnNldEF0dHJpYnV0ZSgnc3R5bGUnLCBvcmlnaW5hbFN0eWxlc1RhZyk7XG4gICAgICAgICAgICB9XG5cbiAgICAgICAgICAgIC8vIG1vdmUgaW1nIHRhZyB0byBpdHMgZGVmYXVsdCBwb3NpdGlvblxuICAgICAgICAgICAgaWYgKHNlbGYuaW1hZ2UuJGl0ZW1QYXJlbnQpIHtcbiAgICAgICAgICAgICAgICBzZWxmLmltYWdlLiRpdGVtUGFyZW50LmFwcGVuZENoaWxkKHNlbGYuaW1hZ2UuJGl0ZW0pO1xuICAgICAgICAgICAgfVxuICAgICAgICB9XG5cbiAgICAgICAgLy8gcmVtb3ZlIGFkZGl0aW9uYWwgZG9tIGVsZW1lbnRzXG4gICAgICAgIGlmIChzZWxmLiRjbGlwU3R5bGVzKSB7XG4gICAgICAgICAgICBzZWxmLiRjbGlwU3R5bGVzLnBhcmVudE5vZGUucmVtb3ZlQ2hpbGQoc2VsZi4kY2xpcFN0eWxlcyk7XG4gICAgICAgIH1cbiAgICAgICAgaWYgKHNlbGYuaW1hZ2UuJGNvbnRhaW5lcikge1xuICAgICAgICAgICAgc2VsZi5pbWFnZS4kY29udGFpbmVyLnBhcmVudE5vZGUucmVtb3ZlQ2hpbGQoc2VsZi5pbWFnZS4kY29udGFpbmVyKTtcbiAgICAgICAgfVxuXG4gICAgICAgIC8vIGNhbGwgb25EZXN0cm95IGV2ZW50XG4gICAgICAgIGlmIChzZWxmLm9wdGlvbnMub25EZXN0cm95KSB7XG4gICAgICAgICAgICBzZWxmLm9wdGlvbnMub25EZXN0cm95LmNhbGwoc2VsZik7XG4gICAgICAgIH1cblxuICAgICAgICAvLyBkZWxldGUgamFyYWxsYXggZnJvbSBpdGVtXG4gICAgICAgIGRlbGV0ZSBzZWxmLiRpdGVtLmphcmFsbGF4O1xuICAgIH1cblxuICAgIC8vIGl0IHdpbGwgcmVtb3ZlIHNvbWUgaW1hZ2Ugb3ZlcmxhcHBpbmdcbiAgICAvLyBvdmVybGFwcGluZyBvY2N1ciBkdWUgdG8gYW4gaW1hZ2UgcG9zaXRpb24gZml4ZWQgaW5zaWRlIGFic29sdXRlIHBvc2l0aW9uIGVsZW1lbnRcbiAgICBjbGlwQ29udGFpbmVyKCkge1xuICAgICAgICAvLyBuZWVkZWQgb25seSB3aGVuIGJhY2tncm91bmQgaW4gZml4ZWQgcG9zaXRpb25cbiAgICAgICAgaWYgKHRoaXMuaW1hZ2UucG9zaXRpb24gIT09ICdmaXhlZCcpIHtcbiAgICAgICAgICAgIHJldHVybjtcbiAgICAgICAgfVxuXG4gICAgICAgIGNvbnN0IHNlbGYgPSB0aGlzO1xuICAgICAgICBjb25zdCByZWN0ID0gc2VsZi5pbWFnZS4kY29udGFpbmVyLmdldEJvdW5kaW5nQ2xpZW50UmVjdCgpO1xuICAgICAgICBjb25zdCB7IHdpZHRoLCBoZWlnaHQgfSA9IHJlY3Q7XG5cbiAgICAgICAgaWYgKCFzZWxmLiRjbGlwU3R5bGVzKSB7XG4gICAgICAgICAgICBzZWxmLiRjbGlwU3R5bGVzID0gZG9jdW1lbnQuY3JlYXRlRWxlbWVudCgnc3R5bGUnKTtcbiAgICAgICAgICAgIHNlbGYuJGNsaXBTdHlsZXMuc2V0QXR0cmlidXRlKCd0eXBlJywgJ3RleHQvY3NzJyk7XG4gICAgICAgICAgICBzZWxmLiRjbGlwU3R5bGVzLnNldEF0dHJpYnV0ZSgnaWQnLCBgamFyYWxsYXgtY2xpcC0ke3NlbGYuaW5zdGFuY2VJRH1gKTtcbiAgICAgICAgICAgIGNvbnN0IGhlYWQgPSBkb2N1bWVudC5oZWFkIHx8IGRvY3VtZW50LmdldEVsZW1lbnRzQnlUYWdOYW1lKCdoZWFkJylbMF07XG4gICAgICAgICAgICBoZWFkLmFwcGVuZENoaWxkKHNlbGYuJGNsaXBTdHlsZXMpO1xuICAgICAgICB9XG5cbiAgICAgICAgY29uc3Qgc3R5bGVzID0gYCNqYXJhbGxheC1jb250YWluZXItJHtzZWxmLmluc3RhbmNlSUR9IHtcbiAgICAgICAgICAgY2xpcDogcmVjdCgwICR7d2lkdGh9cHggJHtoZWlnaHR9cHggMCk7XG4gICAgICAgICAgIGNsaXA6IHJlY3QoMCwgJHt3aWR0aH1weCwgJHtoZWlnaHR9cHgsIDApO1xuICAgICAgICB9YDtcblxuICAgICAgICAvLyBhZGQgY2xpcCBzdHlsZXMgaW5saW5lICh0aGlzIG1ldGhvZCBuZWVkIGZvciBzdXBwb3J0IElFOCBhbmQgbGVzcyBicm93c2VycylcbiAgICAgICAgaWYgKHNlbGYuJGNsaXBTdHlsZXMuc3R5bGVTaGVldCkge1xuICAgICAgICAgICAgc2VsZi4kY2xpcFN0eWxlcy5zdHlsZVNoZWV0LmNzc1RleHQgPSBzdHlsZXM7XG4gICAgICAgIH0gZWxzZSB7XG4gICAgICAgICAgICBzZWxmLiRjbGlwU3R5bGVzLmlubmVySFRNTCA9IHN0eWxlcztcbiAgICAgICAgfVxuICAgIH1cblxuICAgIGNvdmVySW1hZ2UoKSB7XG4gICAgICAgIGNvbnN0IHNlbGYgPSB0aGlzO1xuXG4gICAgICAgIGNvbnN0IHJlY3QgPSBzZWxmLmltYWdlLiRjb250YWluZXIuZ2V0Qm91bmRpbmdDbGllbnRSZWN0KCk7XG4gICAgICAgIGNvbnN0IGNvbnRIID0gcmVjdC5oZWlnaHQ7XG4gICAgICAgIGNvbnN0IHsgc3BlZWQgfSA9IHNlbGYub3B0aW9ucztcbiAgICAgICAgY29uc3QgaXNTY3JvbGwgPSBzZWxmLm9wdGlvbnMudHlwZSA9PT0gJ3Njcm9sbCcgfHwgc2VsZi5vcHRpb25zLnR5cGUgPT09ICdzY3JvbGwtb3BhY2l0eSc7XG4gICAgICAgIGxldCBzY3JvbGxEaXN0ID0gMDtcbiAgICAgICAgbGV0IHJlc3VsdEggPSBjb250SDtcbiAgICAgICAgbGV0IHJlc3VsdE1UID0gMDtcblxuICAgICAgICAvLyBzY3JvbGwgcGFyYWxsYXhcbiAgICAgICAgaWYgKGlzU2Nyb2xsKSB7XG4gICAgICAgICAgICAvLyBzY3JvbGwgZGlzdGFuY2UgYW5kIGhlaWdodCBmb3IgaW1hZ2VcbiAgICAgICAgICAgIGlmIChzcGVlZCA8IDApIHtcbiAgICAgICAgICAgICAgICBzY3JvbGxEaXN0ID0gc3BlZWQgKiBNYXRoLm1heChjb250SCwgd25kSCk7XG5cbiAgICAgICAgICAgICAgICBpZiAod25kSCA8IGNvbnRIKSB7XG4gICAgICAgICAgICAgICAgICAgIHNjcm9sbERpc3QgLT0gc3BlZWQgKiAoY29udEggLSB3bmRIKTtcbiAgICAgICAgICAgICAgICB9XG4gICAgICAgICAgICB9IGVsc2Uge1xuICAgICAgICAgICAgICAgIHNjcm9sbERpc3QgPSBzcGVlZCAqIChjb250SCArIHduZEgpO1xuICAgICAgICAgICAgfVxuXG4gICAgICAgICAgICAvLyBzaXplIGZvciBzY3JvbGwgcGFyYWxsYXhcbiAgICAgICAgICAgIGlmIChzcGVlZCA+IDEpIHtcbiAgICAgICAgICAgICAgICByZXN1bHRIID0gTWF0aC5hYnMoc2Nyb2xsRGlzdCAtIHduZEgpO1xuICAgICAgICAgICAgfSBlbHNlIGlmIChzcGVlZCA8IDApIHtcbiAgICAgICAgICAgICAgICByZXN1bHRIID0gc2Nyb2xsRGlzdCAvIHNwZWVkICsgTWF0aC5hYnMoc2Nyb2xsRGlzdCk7XG4gICAgICAgICAgICB9IGVsc2Uge1xuICAgICAgICAgICAgICAgIHJlc3VsdEggKz0gKHduZEggLSBjb250SCkgKiAoMSAtIHNwZWVkKTtcbiAgICAgICAgICAgIH1cblxuICAgICAgICAgICAgc2Nyb2xsRGlzdCAvPSAyO1xuICAgICAgICB9XG5cbiAgICAgICAgLy8gc3RvcmUgc2Nyb2xsIGRpc3RhbmNlXG4gICAgICAgIHNlbGYucGFyYWxsYXhTY3JvbGxEaXN0YW5jZSA9IHNjcm9sbERpc3Q7XG5cbiAgICAgICAgLy8gdmVydGljYWwgY2VudGVyXG4gICAgICAgIGlmIChpc1Njcm9sbCkge1xuICAgICAgICAgICAgcmVzdWx0TVQgPSAod25kSCAtIHJlc3VsdEgpIC8gMjtcbiAgICAgICAgfSBlbHNlIHtcbiAgICAgICAgICAgIHJlc3VsdE1UID0gKGNvbnRIIC0gcmVzdWx0SCkgLyAyO1xuICAgICAgICB9XG5cbiAgICAgICAgLy8gYXBwbHkgcmVzdWx0IHRvIGl0ZW1cbiAgICAgICAgc2VsZi5jc3Moc2VsZi5pbWFnZS4kaXRlbSwge1xuICAgICAgICAgICAgaGVpZ2h0OiBgJHtyZXN1bHRIfXB4YCxcbiAgICAgICAgICAgIG1hcmdpblRvcDogYCR7cmVzdWx0TVR9cHhgLFxuICAgICAgICAgICAgbGVmdDogc2VsZi5pbWFnZS5wb3NpdGlvbiA9PT0gJ2ZpeGVkJyA/IGAke3JlY3QubGVmdH1weGAgOiAnMCcsXG4gICAgICAgICAgICB3aWR0aDogYCR7cmVjdC53aWR0aH1weGAsXG4gICAgICAgIH0pO1xuXG4gICAgICAgIC8vIGNhbGwgb25Db3ZlckltYWdlIGV2ZW50XG4gICAgICAgIGlmIChzZWxmLm9wdGlvbnMub25Db3ZlckltYWdlKSB7XG4gICAgICAgICAgICBzZWxmLm9wdGlvbnMub25Db3ZlckltYWdlLmNhbGwoc2VsZik7XG4gICAgICAgIH1cblxuICAgICAgICAvLyByZXR1cm4gc29tZSB1c2VmdWwgZGF0YS4gVXNlZCBpbiB0aGUgdmlkZW8gY292ZXIgZnVuY3Rpb25cbiAgICAgICAgcmV0dXJuIHtcbiAgICAgICAgICAgIGltYWdlOiB7XG4gICAgICAgICAgICAgICAgaGVpZ2h0OiByZXN1bHRILFxuICAgICAgICAgICAgICAgIG1hcmdpblRvcDogcmVzdWx0TVQsXG4gICAgICAgICAgICB9LFxuICAgICAgICAgICAgY29udGFpbmVyOiByZWN0LFxuICAgICAgICB9O1xuICAgIH1cblxuICAgIGlzVmlzaWJsZSgpIHtcbiAgICAgICAgcmV0dXJuIHRoaXMuaXNFbGVtZW50SW5WaWV3cG9ydCB8fCBmYWxzZTtcbiAgICB9XG5cbiAgICBvblNjcm9sbChmb3JjZSkge1xuICAgICAgICBjb25zdCBzZWxmID0gdGhpcztcblxuICAgICAgICBjb25zdCByZWN0ID0gc2VsZi4kaXRlbS5nZXRCb3VuZGluZ0NsaWVudFJlY3QoKTtcbiAgICAgICAgY29uc3QgY29udFQgPSByZWN0LnRvcDtcbiAgICAgICAgY29uc3QgY29udEggPSByZWN0LmhlaWdodDtcbiAgICAgICAgY29uc3Qgc3R5bGVzID0ge307XG5cbiAgICAgICAgLy8gY2hlY2sgaWYgaW4gdmlld3BvcnRcbiAgICAgICAgbGV0IHZpZXdwb3J0UmVjdCA9IHJlY3Q7XG4gICAgICAgIGlmIChzZWxmLm9wdGlvbnMuZWxlbWVudEluVmlld3BvcnQpIHtcbiAgICAgICAgICAgIHZpZXdwb3J0UmVjdCA9IHNlbGYub3B0aW9ucy5lbGVtZW50SW5WaWV3cG9ydC5nZXRCb3VuZGluZ0NsaWVudFJlY3QoKTtcbiAgICAgICAgfVxuICAgICAgICBzZWxmLmlzRWxlbWVudEluVmlld3BvcnQgPSB2aWV3cG9ydFJlY3QuYm90dG9tID49IDBcbiAgICAgICAgICAgICYmIHZpZXdwb3J0UmVjdC5yaWdodCA+PSAwXG4gICAgICAgICAgICAmJiB2aWV3cG9ydFJlY3QudG9wIDw9IHduZEhcbiAgICAgICAgICAgICYmIHZpZXdwb3J0UmVjdC5sZWZ0IDw9IHduZFc7XG5cbiAgICAgICAgLy8gc3RvcCBjYWxjdWxhdGlvbnMgaWYgaXRlbSBpcyBub3QgaW4gdmlld3BvcnRcbiAgICAgICAgaWYgKGZvcmNlID8gZmFsc2UgOiAhc2VsZi5pc0VsZW1lbnRJblZpZXdwb3J0KSB7XG4gICAgICAgICAgICByZXR1cm47XG4gICAgICAgIH1cblxuICAgICAgICAvLyBjYWxjdWxhdGUgcGFyYWxsYXggaGVscGluZyB2YXJpYWJsZXNcbiAgICAgICAgY29uc3QgYmVmb3JlVG9wID0gTWF0aC5tYXgoMCwgY29udFQpO1xuICAgICAgICBjb25zdCBiZWZvcmVUb3BFbmQgPSBNYXRoLm1heCgwLCBjb250SCArIGNvbnRUKTtcbiAgICAgICAgY29uc3QgYWZ0ZXJUb3AgPSBNYXRoLm1heCgwLCAtY29udFQpO1xuICAgICAgICBjb25zdCBiZWZvcmVCb3R0b20gPSBNYXRoLm1heCgwLCBjb250VCArIGNvbnRIIC0gd25kSCk7XG4gICAgICAgIGNvbnN0IGJlZm9yZUJvdHRvbUVuZCA9IE1hdGgubWF4KDAsIGNvbnRIIC0gKGNvbnRUICsgY29udEggLSB3bmRIKSk7XG4gICAgICAgIGNvbnN0IGFmdGVyQm90dG9tID0gTWF0aC5tYXgoMCwgLWNvbnRUICsgd25kSCAtIGNvbnRIKTtcbiAgICAgICAgY29uc3QgZnJvbVZpZXdwb3J0Q2VudGVyID0gMSAtIDIgKiAod25kSCAtIGNvbnRUKSAvICh3bmRIICsgY29udEgpO1xuXG4gICAgICAgIC8vIGNhbGN1bGF0ZSBvbiBob3cgcGVyY2VudCBvZiBzZWN0aW9uIGlzIHZpc2libGVcbiAgICAgICAgbGV0IHZpc2libGVQZXJjZW50ID0gMTtcbiAgICAgICAgaWYgKGNvbnRIIDwgd25kSCkge1xuICAgICAgICAgICAgdmlzaWJsZVBlcmNlbnQgPSAxIC0gKGFmdGVyVG9wIHx8IGJlZm9yZUJvdHRvbSkgLyBjb250SDtcbiAgICAgICAgfSBlbHNlIGlmIChiZWZvcmVUb3BFbmQgPD0gd25kSCkge1xuICAgICAgICAgICAgdmlzaWJsZVBlcmNlbnQgPSBiZWZvcmVUb3BFbmQgLyB3bmRIO1xuICAgICAgICB9IGVsc2UgaWYgKGJlZm9yZUJvdHRvbUVuZCA8PSB3bmRIKSB7XG4gICAgICAgICAgICB2aXNpYmxlUGVyY2VudCA9IGJlZm9yZUJvdHRvbUVuZCAvIHduZEg7XG4gICAgICAgIH1cblxuICAgICAgICAvLyBvcGFjaXR5XG4gICAgICAgIGlmIChzZWxmLm9wdGlvbnMudHlwZSA9PT0gJ29wYWNpdHknIHx8IHNlbGYub3B0aW9ucy50eXBlID09PSAnc2NhbGUtb3BhY2l0eScgfHwgc2VsZi5vcHRpb25zLnR5cGUgPT09ICdzY3JvbGwtb3BhY2l0eScpIHtcbiAgICAgICAgICAgIHN0eWxlcy50cmFuc2Zvcm0gPSAndHJhbnNsYXRlM2QoMCwwLDApJztcbiAgICAgICAgICAgIHN0eWxlcy5vcGFjaXR5ID0gdmlzaWJsZVBlcmNlbnQ7XG4gICAgICAgIH1cblxuICAgICAgICAvLyBzY2FsZVxuICAgICAgICBpZiAoc2VsZi5vcHRpb25zLnR5cGUgPT09ICdzY2FsZScgfHwgc2VsZi5vcHRpb25zLnR5cGUgPT09ICdzY2FsZS1vcGFjaXR5Jykge1xuICAgICAgICAgICAgbGV0IHNjYWxlID0gMTtcbiAgICAgICAgICAgIGlmIChzZWxmLm9wdGlvbnMuc3BlZWQgPCAwKSB7XG4gICAgICAgICAgICAgICAgc2NhbGUgLT0gc2VsZi5vcHRpb25zLnNwZWVkICogdmlzaWJsZVBlcmNlbnQ7XG4gICAgICAgICAgICB9IGVsc2Uge1xuICAgICAgICAgICAgICAgIHNjYWxlICs9IHNlbGYub3B0aW9ucy5zcGVlZCAqICgxIC0gdmlzaWJsZVBlcmNlbnQpO1xuICAgICAgICAgICAgfVxuICAgICAgICAgICAgc3R5bGVzLnRyYW5zZm9ybSA9IGBzY2FsZSgke3NjYWxlfSkgdHJhbnNsYXRlM2QoMCwwLDApYDtcbiAgICAgICAgfVxuXG4gICAgICAgIC8vIHNjcm9sbFxuICAgICAgICBpZiAoc2VsZi5vcHRpb25zLnR5cGUgPT09ICdzY3JvbGwnIHx8IHNlbGYub3B0aW9ucy50eXBlID09PSAnc2Nyb2xsLW9wYWNpdHknKSB7XG4gICAgICAgICAgICBsZXQgcG9zaXRpb25ZID0gc2VsZi5wYXJhbGxheFNjcm9sbERpc3RhbmNlICogZnJvbVZpZXdwb3J0Q2VudGVyO1xuXG4gICAgICAgICAgICAvLyBmaXggaWYgcGFyYWxsYXggYmxvY2sgaW4gYWJzb2x1dGUgcG9zaXRpb25cbiAgICAgICAgICAgIGlmIChzZWxmLmltYWdlLnBvc2l0aW9uID09PSAnYWJzb2x1dGUnKSB7XG4gICAgICAgICAgICAgICAgcG9zaXRpb25ZIC09IGNvbnRUO1xuICAgICAgICAgICAgfVxuXG4gICAgICAgICAgICBzdHlsZXMudHJhbnNmb3JtID0gYHRyYW5zbGF0ZTNkKDAsJHtwb3NpdGlvbll9cHgsMClgO1xuICAgICAgICB9XG5cbiAgICAgICAgc2VsZi5jc3Moc2VsZi5pbWFnZS4kaXRlbSwgc3R5bGVzKTtcblxuICAgICAgICAvLyBjYWxsIG9uU2Nyb2xsIGV2ZW50XG4gICAgICAgIGlmIChzZWxmLm9wdGlvbnMub25TY3JvbGwpIHtcbiAgICAgICAgICAgIHNlbGYub3B0aW9ucy5vblNjcm9sbC5jYWxsKHNlbGYsIHtcbiAgICAgICAgICAgICAgICBzZWN0aW9uOiByZWN0LFxuXG4gICAgICAgICAgICAgICAgYmVmb3JlVG9wLFxuICAgICAgICAgICAgICAgIGJlZm9yZVRvcEVuZCxcbiAgICAgICAgICAgICAgICBhZnRlclRvcCxcbiAgICAgICAgICAgICAgICBiZWZvcmVCb3R0b20sXG4gICAgICAgICAgICAgICAgYmVmb3JlQm90dG9tRW5kLFxuICAgICAgICAgICAgICAgIGFmdGVyQm90dG9tLFxuXG4gICAgICAgICAgICAgICAgdmlzaWJsZVBlcmNlbnQsXG4gICAgICAgICAgICAgICAgZnJvbVZpZXdwb3J0Q2VudGVyLFxuICAgICAgICAgICAgfSk7XG4gICAgICAgIH1cbiAgICB9XG5cbiAgICBvblJlc2l6ZSgpIHtcbiAgICAgICAgdGhpcy5jb3ZlckltYWdlKCk7XG4gICAgICAgIHRoaXMuY2xpcENvbnRhaW5lcigpO1xuICAgIH1cbn1cblxuXG4vLyBnbG9iYWwgZGVmaW5pdGlvblxuY29uc3QgcGx1Z2luID0gZnVuY3Rpb24gKGl0ZW1zKSB7XG4gICAgLy8gY2hlY2sgZm9yIGRvbSBlbGVtZW50XG4gICAgLy8gdGhhbmtzOiBodHRwOi8vc3RhY2tvdmVyZmxvdy5jb20vcXVlc3Rpb25zLzM4NDI4Ni9qYXZhc2NyaXB0LWlzZG9tLWhvdy1kby15b3UtY2hlY2staWYtYS1qYXZhc2NyaXB0LW9iamVjdC1pcy1hLWRvbS1vYmplY3RcbiAgICBpZiAodHlwZW9mIEhUTUxFbGVtZW50ID09PSAnb2JqZWN0JyA/IGl0ZW1zIGluc3RhbmNlb2YgSFRNTEVsZW1lbnQgOiBpdGVtcyAmJiB0eXBlb2YgaXRlbXMgPT09ICdvYmplY3QnICYmIGl0ZW1zICE9PSBudWxsICYmIGl0ZW1zLm5vZGVUeXBlID09PSAxICYmIHR5cGVvZiBpdGVtcy5ub2RlTmFtZSA9PT0gJ3N0cmluZycpIHtcbiAgICAgICAgaXRlbXMgPSBbaXRlbXNdO1xuICAgIH1cblxuICAgIGNvbnN0IG9wdGlvbnMgPSBhcmd1bWVudHNbMV07XG4gICAgY29uc3QgYXJncyA9IEFycmF5LnByb3RvdHlwZS5zbGljZS5jYWxsKGFyZ3VtZW50cywgMik7XG4gICAgY29uc3QgbGVuID0gaXRlbXMubGVuZ3RoO1xuICAgIGxldCBrID0gMDtcbiAgICBsZXQgcmV0O1xuXG4gICAgZm9yIChrOyBrIDwgbGVuOyBrKyspIHtcbiAgICAgICAgaWYgKHR5cGVvZiBvcHRpb25zID09PSAnb2JqZWN0JyB8fCB0eXBlb2Ygb3B0aW9ucyA9PT0gJ3VuZGVmaW5lZCcpIHtcbiAgICAgICAgICAgIGlmICghaXRlbXNba10uamFyYWxsYXgpIHtcbiAgICAgICAgICAgICAgICBpdGVtc1trXS5qYXJhbGxheCA9IG5ldyBKYXJhbGxheChpdGVtc1trXSwgb3B0aW9ucyk7XG4gICAgICAgICAgICB9XG4gICAgICAgIH0gZWxzZSBpZiAoaXRlbXNba10uamFyYWxsYXgpIHtcbiAgICAgICAgICAgIC8vIGVzbGludC1kaXNhYmxlLW5leHQtbGluZSBwcmVmZXItc3ByZWFkXG4gICAgICAgICAgICByZXQgPSBpdGVtc1trXS5qYXJhbGxheFtvcHRpb25zXS5hcHBseShpdGVtc1trXS5qYXJhbGxheCwgYXJncyk7XG4gICAgICAgIH1cbiAgICAgICAgaWYgKHR5cGVvZiByZXQgIT09ICd1bmRlZmluZWQnKSB7XG4gICAgICAgICAgICByZXR1cm4gcmV0O1xuICAgICAgICB9XG4gICAgfVxuXG4gICAgcmV0dXJuIGl0ZW1zO1xufTtcbnBsdWdpbi5jb25zdHJ1Y3RvciA9IEphcmFsbGF4O1xuXG5leHBvcnQgZGVmYXVsdCBwbHVnaW47XG4iLCJtb2R1bGUuZXhwb3J0cyA9IGZ1bmN0aW9uIChjYWxsYmFjaykge1xyXG5cclxuXHRpZiAoZG9jdW1lbnQucmVhZHlTdGF0ZSA9PT0gJ2NvbXBsZXRlJyB8fCBkb2N1bWVudC5yZWFkeVN0YXRlID09PSAnaW50ZXJhY3RpdmUnKSB7XHJcblx0XHQvLyBBbHJlYWR5IHJlYWR5IG9yIGludGVyYWN0aXZlLCBleGVjdXRlIGNhbGxiYWNrXHJcblx0XHRjYWxsYmFjay5jYWxsKCk7XHJcblx0fVxyXG5cdGVsc2UgaWYgKGRvY3VtZW50LmF0dGFjaEV2ZW50KSB7XHJcblx0XHQvLyBPbGQgYnJvd3NlcnNcclxuXHRcdGRvY3VtZW50LmF0dGFjaEV2ZW50KCdvbnJlYWR5c3RhdGVjaGFuZ2UnLCBmdW5jdGlvbiAoKSB7XHJcblx0XHRcdGlmIChkb2N1bWVudC5yZWFkeVN0YXRlID09PSAnaW50ZXJhY3RpdmUnKVxyXG5cdFx0XHRcdGNhbGxiYWNrLmNhbGwoKTtcclxuXHRcdH0pO1xyXG5cdH1cclxuXHRlbHNlIGlmIChkb2N1bWVudC5hZGRFdmVudExpc3RlbmVyKSB7XHJcblx0XHQvLyBNb2Rlcm4gYnJvd3NlcnNcclxuXHRcdGRvY3VtZW50LmFkZEV2ZW50TGlzdGVuZXIoJ0RPTUNvbnRlbnRMb2FkZWQnLCBjYWxsYmFjayk7XHJcblx0fVxyXG59XHJcbiIsInZhciBnbG9iYWwgPSByZXF1aXJlKCdnbG9iYWwnKVxuXG4vKipcbiAqIGByZXF1ZXN0QW5pbWF0aW9uRnJhbWUoKWBcbiAqL1xuXG52YXIgcmVxdWVzdCA9IGdsb2JhbC5yZXF1ZXN0QW5pbWF0aW9uRnJhbWVcbiAgfHwgZ2xvYmFsLndlYmtpdFJlcXVlc3RBbmltYXRpb25GcmFtZVxuICB8fCBnbG9iYWwubW96UmVxdWVzdEFuaW1hdGlvbkZyYW1lXG4gIHx8IGZhbGxiYWNrXG5cbnZhciBwcmV2ID0gK25ldyBEYXRlXG5mdW5jdGlvbiBmYWxsYmFjayAoZm4pIHtcbiAgdmFyIGN1cnIgPSArbmV3IERhdGVcbiAgdmFyIG1zID0gTWF0aC5tYXgoMCwgMTYgLSAoY3VyciAtIHByZXYpKVxuICB2YXIgcmVxID0gc2V0VGltZW91dChmbiwgbXMpXG4gIHJldHVybiBwcmV2ID0gY3VyciwgcmVxXG59XG5cbi8qKlxuICogYGNhbmNlbEFuaW1hdGlvbkZyYW1lKClgXG4gKi9cblxudmFyIGNhbmNlbCA9IGdsb2JhbC5jYW5jZWxBbmltYXRpb25GcmFtZVxuICB8fCBnbG9iYWwud2Via2l0Q2FuY2VsQW5pbWF0aW9uRnJhbWVcbiAgfHwgZ2xvYmFsLm1vekNhbmNlbEFuaW1hdGlvbkZyYW1lXG4gIHx8IGNsZWFyVGltZW91dFxuXG5pZiAoRnVuY3Rpb24ucHJvdG90eXBlLmJpbmQpIHtcbiAgcmVxdWVzdCA9IHJlcXVlc3QuYmluZChnbG9iYWwpXG4gIGNhbmNlbCA9IGNhbmNlbC5iaW5kKGdsb2JhbClcbn1cblxuZXhwb3J0cyA9IG1vZHVsZS5leHBvcnRzID0gcmVxdWVzdFxuZXhwb3J0cy5jYW5jZWwgPSBjYW5jZWxcbiIsIm1vZHVsZS5leHBvcnRzID0gcmVxdWlyZSgnLi9zcmMvdmlkZW8td29ya2VyLmVzbScpO1xuIiwiLy8gRGVmZXJyZWRcbi8vIHRoYW5rcyBodHRwOi8vc3RhY2tvdmVyZmxvdy5jb20vcXVlc3Rpb25zLzE4MDk2NzE1L2ltcGxlbWVudC1kZWZlcnJlZC1vYmplY3Qtd2l0aG91dC11c2luZy1qcXVlcnlcbmZ1bmN0aW9uIERlZmVycmVkKCkge1xuICAgIHRoaXMuX2RvbmUgPSBbXTtcbiAgICB0aGlzLl9mYWlsID0gW107XG59XG5EZWZlcnJlZC5wcm90b3R5cGUgPSB7XG4gICAgZXhlY3V0ZShsaXN0LCBhcmdzKSB7XG4gICAgICAgIGxldCBpID0gbGlzdC5sZW5ndGg7XG4gICAgICAgIGFyZ3MgPSBBcnJheS5wcm90b3R5cGUuc2xpY2UuY2FsbChhcmdzKTtcbiAgICAgICAgd2hpbGUgKGktLSkge1xuICAgICAgICAgICAgbGlzdFtpXS5hcHBseShudWxsLCBhcmdzKTtcbiAgICAgICAgfVxuICAgIH0sXG4gICAgcmVzb2x2ZSgpIHtcbiAgICAgICAgdGhpcy5leGVjdXRlKHRoaXMuX2RvbmUsIGFyZ3VtZW50cyk7XG4gICAgfSxcbiAgICByZWplY3QoKSB7XG4gICAgICAgIHRoaXMuZXhlY3V0ZSh0aGlzLl9mYWlsLCBhcmd1bWVudHMpO1xuICAgIH0sXG4gICAgZG9uZShjYWxsYmFjaykge1xuICAgICAgICB0aGlzLl9kb25lLnB1c2goY2FsbGJhY2spO1xuICAgIH0sXG4gICAgZmFpbChjYWxsYmFjaykge1xuICAgICAgICB0aGlzLl9mYWlsLnB1c2goY2FsbGJhY2spO1xuICAgIH0sXG59O1xuXG5sZXQgSUQgPSAwO1xubGV0IFlvdXR1YmVBUElhZGRlZCA9IDA7XG5sZXQgVmltZW9BUElhZGRlZCA9IDA7XG5sZXQgbG9hZGluZ1lvdXR1YmVQbGF5ZXIgPSAwO1xubGV0IGxvYWRpbmdWaW1lb1BsYXllciA9IDA7XG5jb25zdCBsb2FkaW5nWW91dHViZURlZmVyID0gbmV3IERlZmVycmVkKCk7XG5jb25zdCBsb2FkaW5nVmltZW9EZWZlciA9IG5ldyBEZWZlcnJlZCgpO1xuXG5leHBvcnQgZGVmYXVsdCBjbGFzcyBWaWRlb1dvcmtlciB7XG4gICAgY29uc3RydWN0b3IodXJsLCBvcHRpb25zKSB7XG4gICAgICAgIGNvbnN0IHNlbGYgPSB0aGlzO1xuXG4gICAgICAgIHNlbGYudXJsID0gdXJsO1xuXG4gICAgICAgIHNlbGYub3B0aW9uc19kZWZhdWx0ID0ge1xuICAgICAgICAgICAgYXV0b3BsYXk6IGZhbHNlLFxuICAgICAgICAgICAgbG9vcDogZmFsc2UsXG4gICAgICAgICAgICBtdXRlOiBmYWxzZSxcbiAgICAgICAgICAgIHZvbHVtZTogMTAwLFxuICAgICAgICAgICAgc2hvd0NvbnRvbHM6IHRydWUsXG5cbiAgICAgICAgICAgIC8vIHN0YXJ0IC8gZW5kIHZpZGVvIHRpbWUgaW4gc2Vjb25kc1xuICAgICAgICAgICAgc3RhcnRUaW1lOiAwLFxuICAgICAgICAgICAgZW5kVGltZTogMCxcbiAgICAgICAgfTtcblxuICAgICAgICBzZWxmLm9wdGlvbnMgPSBzZWxmLmV4dGVuZCh7fSwgc2VsZi5vcHRpb25zX2RlZmF1bHQsIG9wdGlvbnMpO1xuXG4gICAgICAgIC8vIGNoZWNrIFVSTFxuICAgICAgICBzZWxmLnZpZGVvSUQgPSBzZWxmLnBhcnNlVVJMKHVybCk7XG5cbiAgICAgICAgLy8gaW5pdFxuICAgICAgICBpZiAoc2VsZi52aWRlb0lEKSB7XG4gICAgICAgICAgICBzZWxmLklEID0gSUQrKztcbiAgICAgICAgICAgIHNlbGYubG9hZEFQSSgpO1xuICAgICAgICAgICAgc2VsZi5pbml0KCk7XG4gICAgICAgIH1cbiAgICB9XG5cbiAgICAvLyBFeHRlbmQgbGlrZSBqUXVlcnkuZXh0ZW5kXG4gICAgZXh0ZW5kKG91dCkge1xuICAgICAgICBvdXQgPSBvdXQgfHwge307XG4gICAgICAgIE9iamVjdC5rZXlzKGFyZ3VtZW50cykuZm9yRWFjaCgoaSkgPT4ge1xuICAgICAgICAgICAgaWYgKCFhcmd1bWVudHNbaV0pIHtcbiAgICAgICAgICAgICAgICByZXR1cm47XG4gICAgICAgICAgICB9XG4gICAgICAgICAgICBPYmplY3Qua2V5cyhhcmd1bWVudHNbaV0pLmZvckVhY2goKGtleSkgPT4ge1xuICAgICAgICAgICAgICAgIG91dFtrZXldID0gYXJndW1lbnRzW2ldW2tleV07XG4gICAgICAgICAgICB9KTtcbiAgICAgICAgfSk7XG4gICAgICAgIHJldHVybiBvdXQ7XG4gICAgfVxuXG4gICAgcGFyc2VVUkwodXJsKSB7XG4gICAgICAgIC8vIHBhcnNlIHlvdXR1YmUgSURcbiAgICAgICAgZnVuY3Rpb24gZ2V0WW91dHViZUlEKHl0VXJsKSB7XG4gICAgICAgICAgICAvLyBlc2xpbnQtZGlzYWJsZS1uZXh0LWxpbmUgbm8tdXNlbGVzcy1lc2NhcGVcbiAgICAgICAgICAgIGNvbnN0IHJlZ0V4cCA9IC8uKig/OnlvdXR1LmJlXFwvfHZcXC98dVxcL1xcd1xcL3xlbWJlZFxcL3x3YXRjaFxcP3Y9KShbXiNcXCZcXD9dKikuKi87XG4gICAgICAgICAgICBjb25zdCBtYXRjaCA9IHl0VXJsLm1hdGNoKHJlZ0V4cCk7XG4gICAgICAgICAgICByZXR1cm4gbWF0Y2ggJiYgbWF0Y2hbMV0ubGVuZ3RoID09PSAxMSA/IG1hdGNoWzFdIDogZmFsc2U7XG4gICAgICAgIH1cblxuICAgICAgICAvLyBwYXJzZSB2aW1lbyBJRFxuICAgICAgICBmdW5jdGlvbiBnZXRWaW1lb0lEKHZtVXJsKSB7XG4gICAgICAgICAgICAvLyBlc2xpbnQtZGlzYWJsZS1uZXh0LWxpbmUgbm8tdXNlbGVzcy1lc2NhcGVcbiAgICAgICAgICAgIGNvbnN0IHJlZ0V4cCA9IC9odHRwcz86XFwvXFwvKD86d3d3XFwufHBsYXllclxcLik/dmltZW8uY29tXFwvKD86Y2hhbm5lbHNcXC8oPzpcXHcrXFwvKT98Z3JvdXBzXFwvKFteXFwvXSopXFwvdmlkZW9zXFwvfGFsYnVtXFwvKFxcZCspXFwvdmlkZW9cXC98dmlkZW9cXC98KShcXGQrKSg/OiR8XFwvfFxcPykvO1xuICAgICAgICAgICAgY29uc3QgbWF0Y2ggPSB2bVVybC5tYXRjaChyZWdFeHApO1xuICAgICAgICAgICAgcmV0dXJuIG1hdGNoICYmIG1hdGNoWzNdID8gbWF0Y2hbM10gOiBmYWxzZTtcbiAgICAgICAgfVxuXG4gICAgICAgIC8vIHBhcnNlIGxvY2FsIHN0cmluZ1xuICAgICAgICBmdW5jdGlvbiBnZXRMb2NhbFZpZGVvcyhsb2NVcmwpIHtcbiAgICAgICAgICAgIC8vIGVzbGludC1kaXNhYmxlLW5leHQtbGluZSBuby11c2VsZXNzLWVzY2FwZVxuICAgICAgICAgICAgY29uc3QgdmlkZW9Gb3JtYXRzID0gbG9jVXJsLnNwbGl0KC8sKD89bXA0XFw6fHdlYm1cXDp8b2d2XFw6fG9nZ1xcOikvKTtcbiAgICAgICAgICAgIGNvbnN0IHJlc3VsdCA9IHt9O1xuICAgICAgICAgICAgbGV0IHJlYWR5ID0gMDtcbiAgICAgICAgICAgIHZpZGVvRm9ybWF0cy5mb3JFYWNoKCh2YWwpID0+IHtcbiAgICAgICAgICAgICAgICAvLyBlc2xpbnQtZGlzYWJsZS1uZXh0LWxpbmUgbm8tdXNlbGVzcy1lc2NhcGVcbiAgICAgICAgICAgICAgICBjb25zdCBtYXRjaCA9IHZhbC5tYXRjaCgvXihtcDR8d2VibXxvZ3Z8b2dnKVxcOiguKikvKTtcbiAgICAgICAgICAgICAgICBpZiAobWF0Y2ggJiYgbWF0Y2hbMV0gJiYgbWF0Y2hbMl0pIHtcbiAgICAgICAgICAgICAgICAgICAgLy8gZXNsaW50LWRpc2FibGUtbmV4dC1saW5lIHByZWZlci1kZXN0cnVjdHVyaW5nXG4gICAgICAgICAgICAgICAgICAgIHJlc3VsdFttYXRjaFsxXSA9PT0gJ29ndicgPyAnb2dnJyA6IG1hdGNoWzFdXSA9IG1hdGNoWzJdO1xuICAgICAgICAgICAgICAgICAgICByZWFkeSA9IDE7XG4gICAgICAgICAgICAgICAgfVxuICAgICAgICAgICAgfSk7XG4gICAgICAgICAgICByZXR1cm4gcmVhZHkgPyByZXN1bHQgOiBmYWxzZTtcbiAgICAgICAgfVxuXG4gICAgICAgIGNvbnN0IFlvdXR1YmUgPSBnZXRZb3V0dWJlSUQodXJsKTtcbiAgICAgICAgY29uc3QgVmltZW8gPSBnZXRWaW1lb0lEKHVybCk7XG4gICAgICAgIGNvbnN0IExvY2FsID0gZ2V0TG9jYWxWaWRlb3ModXJsKTtcblxuICAgICAgICBpZiAoWW91dHViZSkge1xuICAgICAgICAgICAgdGhpcy50eXBlID0gJ3lvdXR1YmUnO1xuICAgICAgICAgICAgcmV0dXJuIFlvdXR1YmU7XG4gICAgICAgIH0gZWxzZSBpZiAoVmltZW8pIHtcbiAgICAgICAgICAgIHRoaXMudHlwZSA9ICd2aW1lbyc7XG4gICAgICAgICAgICByZXR1cm4gVmltZW87XG4gICAgICAgIH0gZWxzZSBpZiAoTG9jYWwpIHtcbiAgICAgICAgICAgIHRoaXMudHlwZSA9ICdsb2NhbCc7XG4gICAgICAgICAgICByZXR1cm4gTG9jYWw7XG4gICAgICAgIH1cblxuICAgICAgICByZXR1cm4gZmFsc2U7XG4gICAgfVxuXG4gICAgaXNWYWxpZCgpIHtcbiAgICAgICAgcmV0dXJuICEhdGhpcy52aWRlb0lEO1xuICAgIH1cblxuICAgIC8vIGV2ZW50c1xuICAgIG9uKG5hbWUsIGNhbGxiYWNrKSB7XG4gICAgICAgIHRoaXMudXNlckV2ZW50c0xpc3QgPSB0aGlzLnVzZXJFdmVudHNMaXN0IHx8IFtdO1xuXG4gICAgICAgIC8vIGFkZCBuZXcgY2FsbGJhY2sgaW4gZXZlbnRzIGxpc3RcbiAgICAgICAgKHRoaXMudXNlckV2ZW50c0xpc3RbbmFtZV0gfHwgKHRoaXMudXNlckV2ZW50c0xpc3RbbmFtZV0gPSBbXSkpLnB1c2goY2FsbGJhY2spO1xuICAgIH1cbiAgICBvZmYobmFtZSwgY2FsbGJhY2spIHtcbiAgICAgICAgaWYgKCF0aGlzLnVzZXJFdmVudHNMaXN0IHx8ICF0aGlzLnVzZXJFdmVudHNMaXN0W25hbWVdKSB7XG4gICAgICAgICAgICByZXR1cm47XG4gICAgICAgIH1cblxuICAgICAgICBpZiAoIWNhbGxiYWNrKSB7XG4gICAgICAgICAgICBkZWxldGUgdGhpcy51c2VyRXZlbnRzTGlzdFtuYW1lXTtcbiAgICAgICAgfSBlbHNlIHtcbiAgICAgICAgICAgIHRoaXMudXNlckV2ZW50c0xpc3RbbmFtZV0uZm9yRWFjaCgodmFsLCBrZXkpID0+IHtcbiAgICAgICAgICAgICAgICBpZiAodmFsID09PSBjYWxsYmFjaykge1xuICAgICAgICAgICAgICAgICAgICB0aGlzLnVzZXJFdmVudHNMaXN0W25hbWVdW2tleV0gPSBmYWxzZTtcbiAgICAgICAgICAgICAgICB9XG4gICAgICAgICAgICB9KTtcbiAgICAgICAgfVxuICAgIH1cbiAgICBmaXJlKG5hbWUpIHtcbiAgICAgICAgY29uc3QgYXJncyA9IFtdLnNsaWNlLmNhbGwoYXJndW1lbnRzLCAxKTtcbiAgICAgICAgaWYgKHRoaXMudXNlckV2ZW50c0xpc3QgJiYgdHlwZW9mIHRoaXMudXNlckV2ZW50c0xpc3RbbmFtZV0gIT09ICd1bmRlZmluZWQnKSB7XG4gICAgICAgICAgICB0aGlzLnVzZXJFdmVudHNMaXN0W25hbWVdLmZvckVhY2goKHZhbCkgPT4ge1xuICAgICAgICAgICAgICAgIC8vIGNhbGwgd2l0aCBhbGwgYXJndW1lbnRzXG4gICAgICAgICAgICAgICAgaWYgKHZhbCkge1xuICAgICAgICAgICAgICAgICAgICB2YWwuYXBwbHkodGhpcywgYXJncyk7XG4gICAgICAgICAgICAgICAgfVxuICAgICAgICAgICAgfSk7XG4gICAgICAgIH1cbiAgICB9XG5cbiAgICBwbGF5KHN0YXJ0KSB7XG4gICAgICAgIGNvbnN0IHNlbGYgPSB0aGlzO1xuICAgICAgICBpZiAoIXNlbGYucGxheWVyKSB7XG4gICAgICAgICAgICByZXR1cm47XG4gICAgICAgIH1cblxuICAgICAgICBpZiAoc2VsZi50eXBlID09PSAneW91dHViZScgJiYgc2VsZi5wbGF5ZXIucGxheVZpZGVvKSB7XG4gICAgICAgICAgICBpZiAodHlwZW9mIHN0YXJ0ICE9PSAndW5kZWZpbmVkJykge1xuICAgICAgICAgICAgICAgIHNlbGYucGxheWVyLnNlZWtUbyhzdGFydCB8fCAwKTtcbiAgICAgICAgICAgIH1cbiAgICAgICAgICAgIGlmIChZVC5QbGF5ZXJTdGF0ZS5QTEFZSU5HICE9PSBzZWxmLnBsYXllci5nZXRQbGF5ZXJTdGF0ZSgpKSB7XG4gICAgICAgICAgICAgICAgc2VsZi5wbGF5ZXIucGxheVZpZGVvKCk7XG4gICAgICAgICAgICB9XG4gICAgICAgIH1cblxuICAgICAgICBpZiAoc2VsZi50eXBlID09PSAndmltZW8nKSB7XG4gICAgICAgICAgICBpZiAodHlwZW9mIHN0YXJ0ICE9PSAndW5kZWZpbmVkJykge1xuICAgICAgICAgICAgICAgIHNlbGYucGxheWVyLnNldEN1cnJlbnRUaW1lKHN0YXJ0KTtcbiAgICAgICAgICAgIH1cbiAgICAgICAgICAgIHNlbGYucGxheWVyLmdldFBhdXNlZCgpLnRoZW4oKHBhdXNlZCkgPT4ge1xuICAgICAgICAgICAgICAgIGlmIChwYXVzZWQpIHtcbiAgICAgICAgICAgICAgICAgICAgc2VsZi5wbGF5ZXIucGxheSgpO1xuICAgICAgICAgICAgICAgIH1cbiAgICAgICAgICAgIH0pO1xuICAgICAgICB9XG5cbiAgICAgICAgaWYgKHNlbGYudHlwZSA9PT0gJ2xvY2FsJykge1xuICAgICAgICAgICAgaWYgKHR5cGVvZiBzdGFydCAhPT0gJ3VuZGVmaW5lZCcpIHtcbiAgICAgICAgICAgICAgICBzZWxmLnBsYXllci5jdXJyZW50VGltZSA9IHN0YXJ0O1xuICAgICAgICAgICAgfVxuICAgICAgICAgICAgaWYgKHNlbGYucGxheWVyLnBhdXNlZCkge1xuICAgICAgICAgICAgICAgIHNlbGYucGxheWVyLnBsYXkoKTtcbiAgICAgICAgICAgIH1cbiAgICAgICAgfVxuICAgIH1cblxuICAgIHBhdXNlKCkge1xuICAgICAgICBjb25zdCBzZWxmID0gdGhpcztcbiAgICAgICAgaWYgKCFzZWxmLnBsYXllcikge1xuICAgICAgICAgICAgcmV0dXJuO1xuICAgICAgICB9XG5cbiAgICAgICAgaWYgKHNlbGYudHlwZSA9PT0gJ3lvdXR1YmUnICYmIHNlbGYucGxheWVyLnBhdXNlVmlkZW8pIHtcbiAgICAgICAgICAgIGlmIChZVC5QbGF5ZXJTdGF0ZS5QTEFZSU5HID09PSBzZWxmLnBsYXllci5nZXRQbGF5ZXJTdGF0ZSgpKSB7XG4gICAgICAgICAgICAgICAgc2VsZi5wbGF5ZXIucGF1c2VWaWRlbygpO1xuICAgICAgICAgICAgfVxuICAgICAgICB9XG5cbiAgICAgICAgaWYgKHNlbGYudHlwZSA9PT0gJ3ZpbWVvJykge1xuICAgICAgICAgICAgc2VsZi5wbGF5ZXIuZ2V0UGF1c2VkKCkudGhlbigocGF1c2VkKSA9PiB7XG4gICAgICAgICAgICAgICAgaWYgKCFwYXVzZWQpIHtcbiAgICAgICAgICAgICAgICAgICAgc2VsZi5wbGF5ZXIucGF1c2UoKTtcbiAgICAgICAgICAgICAgICB9XG4gICAgICAgICAgICB9KTtcbiAgICAgICAgfVxuXG4gICAgICAgIGlmIChzZWxmLnR5cGUgPT09ICdsb2NhbCcpIHtcbiAgICAgICAgICAgIGlmICghc2VsZi5wbGF5ZXIucGF1c2VkKSB7XG4gICAgICAgICAgICAgICAgc2VsZi5wbGF5ZXIucGF1c2UoKTtcbiAgICAgICAgICAgIH1cbiAgICAgICAgfVxuICAgIH1cblxuICAgIG11dGUoKSB7XG4gICAgICAgIGNvbnN0IHNlbGYgPSB0aGlzO1xuICAgICAgICBpZiAoIXNlbGYucGxheWVyKSB7XG4gICAgICAgICAgICByZXR1cm47XG4gICAgICAgIH1cblxuICAgICAgICBpZiAoc2VsZi50eXBlID09PSAneW91dHViZScgJiYgc2VsZi5wbGF5ZXIubXV0ZSkge1xuICAgICAgICAgICAgc2VsZi5wbGF5ZXIubXV0ZSgpO1xuICAgICAgICB9XG5cbiAgICAgICAgaWYgKHNlbGYudHlwZSA9PT0gJ3ZpbWVvJyAmJiBzZWxmLnBsYXllci5zZXRWb2x1bWUpIHtcbiAgICAgICAgICAgIHNlbGYucGxheWVyLnNldFZvbHVtZSgwKTtcbiAgICAgICAgfVxuXG4gICAgICAgIGlmIChzZWxmLnR5cGUgPT09ICdsb2NhbCcpIHtcbiAgICAgICAgICAgIHNlbGYuJHZpZGVvLm11dGVkID0gdHJ1ZTtcbiAgICAgICAgfVxuICAgIH1cblxuICAgIHVubXV0ZSgpIHtcbiAgICAgICAgY29uc3Qgc2VsZiA9IHRoaXM7XG4gICAgICAgIGlmICghc2VsZi5wbGF5ZXIpIHtcbiAgICAgICAgICAgIHJldHVybjtcbiAgICAgICAgfVxuXG4gICAgICAgIGlmIChzZWxmLnR5cGUgPT09ICd5b3V0dWJlJyAmJiBzZWxmLnBsYXllci5tdXRlKSB7XG4gICAgICAgICAgICBzZWxmLnBsYXllci51bk11dGUoKTtcbiAgICAgICAgfVxuXG4gICAgICAgIGlmIChzZWxmLnR5cGUgPT09ICd2aW1lbycgJiYgc2VsZi5wbGF5ZXIuc2V0Vm9sdW1lKSB7XG4gICAgICAgICAgICBzZWxmLnBsYXllci5zZXRWb2x1bWUoc2VsZi5vcHRpb25zLnZvbHVtZSk7XG4gICAgICAgIH1cblxuICAgICAgICBpZiAoc2VsZi50eXBlID09PSAnbG9jYWwnKSB7XG4gICAgICAgICAgICBzZWxmLiR2aWRlby5tdXRlZCA9IGZhbHNlO1xuICAgICAgICB9XG4gICAgfVxuXG4gICAgc2V0Vm9sdW1lKHZvbHVtZSA9IGZhbHNlKSB7XG4gICAgICAgIGNvbnN0IHNlbGYgPSB0aGlzO1xuICAgICAgICBpZiAoIXNlbGYucGxheWVyIHx8ICF2b2x1bWUpIHtcbiAgICAgICAgICAgIHJldHVybjtcbiAgICAgICAgfVxuXG4gICAgICAgIGlmIChzZWxmLnR5cGUgPT09ICd5b3V0dWJlJyAmJiBzZWxmLnBsYXllci5zZXRWb2x1bWUpIHtcbiAgICAgICAgICAgIHNlbGYucGxheWVyLnNldFZvbHVtZSh2b2x1bWUpO1xuICAgICAgICB9XG5cbiAgICAgICAgaWYgKHNlbGYudHlwZSA9PT0gJ3ZpbWVvJyAmJiBzZWxmLnBsYXllci5zZXRWb2x1bWUpIHtcbiAgICAgICAgICAgIHNlbGYucGxheWVyLnNldFZvbHVtZSh2b2x1bWUpO1xuICAgICAgICB9XG5cbiAgICAgICAgaWYgKHNlbGYudHlwZSA9PT0gJ2xvY2FsJykge1xuICAgICAgICAgICAgc2VsZi4kdmlkZW8udm9sdW1lID0gdm9sdW1lIC8gMTAwO1xuICAgICAgICB9XG4gICAgfVxuXG4gICAgZ2V0Vm9sdW1lKGNhbGxiYWNrKSB7XG4gICAgICAgIGNvbnN0IHNlbGYgPSB0aGlzO1xuICAgICAgICBpZiAoIXNlbGYucGxheWVyKSB7XG4gICAgICAgICAgICBjYWxsYmFjayhmYWxzZSk7XG4gICAgICAgICAgICByZXR1cm47XG4gICAgICAgIH1cblxuICAgICAgICBpZiAoc2VsZi50eXBlID09PSAneW91dHViZScgJiYgc2VsZi5wbGF5ZXIuZ2V0Vm9sdW1lKSB7XG4gICAgICAgICAgICBjYWxsYmFjayhzZWxmLnBsYXllci5nZXRWb2x1bWUoKSk7XG4gICAgICAgIH1cblxuICAgICAgICBpZiAoc2VsZi50eXBlID09PSAndmltZW8nICYmIHNlbGYucGxheWVyLmdldFZvbHVtZSkge1xuICAgICAgICAgICAgc2VsZi5wbGF5ZXIuZ2V0Vm9sdW1lKCkudGhlbigodm9sdW1lKSA9PiB7XG4gICAgICAgICAgICAgICAgY2FsbGJhY2sodm9sdW1lKTtcbiAgICAgICAgICAgIH0pO1xuICAgICAgICB9XG5cbiAgICAgICAgaWYgKHNlbGYudHlwZSA9PT0gJ2xvY2FsJykge1xuICAgICAgICAgICAgY2FsbGJhY2soc2VsZi4kdmlkZW8udm9sdW1lICogMTAwKTtcbiAgICAgICAgfVxuICAgIH1cblxuICAgIGdldE11dGVkKGNhbGxiYWNrKSB7XG4gICAgICAgIGNvbnN0IHNlbGYgPSB0aGlzO1xuICAgICAgICBpZiAoIXNlbGYucGxheWVyKSB7XG4gICAgICAgICAgICBjYWxsYmFjayhudWxsKTtcbiAgICAgICAgICAgIHJldHVybjtcbiAgICAgICAgfVxuXG4gICAgICAgIGlmIChzZWxmLnR5cGUgPT09ICd5b3V0dWJlJyAmJiBzZWxmLnBsYXllci5pc011dGVkKSB7XG4gICAgICAgICAgICBjYWxsYmFjayhzZWxmLnBsYXllci5pc011dGVkKCkpO1xuICAgICAgICB9XG5cbiAgICAgICAgaWYgKHNlbGYudHlwZSA9PT0gJ3ZpbWVvJyAmJiBzZWxmLnBsYXllci5nZXRWb2x1bWUpIHtcbiAgICAgICAgICAgIHNlbGYucGxheWVyLmdldFZvbHVtZSgpLnRoZW4oKHZvbHVtZSkgPT4ge1xuICAgICAgICAgICAgICAgIGNhbGxiYWNrKCEhdm9sdW1lKTtcbiAgICAgICAgICAgIH0pO1xuICAgICAgICB9XG5cbiAgICAgICAgaWYgKHNlbGYudHlwZSA9PT0gJ2xvY2FsJykge1xuICAgICAgICAgICAgY2FsbGJhY2soc2VsZi4kdmlkZW8ubXV0ZWQpO1xuICAgICAgICB9XG4gICAgfVxuXG4gICAgZ2V0SW1hZ2VVUkwoY2FsbGJhY2spIHtcbiAgICAgICAgY29uc3Qgc2VsZiA9IHRoaXM7XG5cbiAgICAgICAgaWYgKHNlbGYudmlkZW9JbWFnZSkge1xuICAgICAgICAgICAgY2FsbGJhY2soc2VsZi52aWRlb0ltYWdlKTtcbiAgICAgICAgICAgIHJldHVybjtcbiAgICAgICAgfVxuXG4gICAgICAgIGlmIChzZWxmLnR5cGUgPT09ICd5b3V0dWJlJykge1xuICAgICAgICAgICAgY29uc3QgYXZhaWxhYmxlU2l6ZXMgPSBbXG4gICAgICAgICAgICAgICAgJ21heHJlc2RlZmF1bHQnLFxuICAgICAgICAgICAgICAgICdzZGRlZmF1bHQnLFxuICAgICAgICAgICAgICAgICdocWRlZmF1bHQnLFxuICAgICAgICAgICAgICAgICcwJyxcbiAgICAgICAgICAgIF07XG4gICAgICAgICAgICBsZXQgc3RlcCA9IDA7XG5cbiAgICAgICAgICAgIGNvbnN0IHRlbXBJbWcgPSBuZXcgSW1hZ2UoKTtcbiAgICAgICAgICAgIHRlbXBJbWcub25sb2FkID0gZnVuY3Rpb24gKCkge1xuICAgICAgICAgICAgICAgIC8vIGlmIG5vIHRodW1ibmFpbCwgeW91dHViZSBhZGQgdGhlaXIgb3duIGltYWdlIHdpdGggd2lkdGggPSAxMjBweFxuICAgICAgICAgICAgICAgIGlmICgodGhpcy5uYXR1cmFsV2lkdGggfHwgdGhpcy53aWR0aCkgIT09IDEyMCB8fCBzdGVwID09PSBhdmFpbGFibGVTaXplcy5sZW5ndGggLSAxKSB7XG4gICAgICAgICAgICAgICAgICAgIC8vIG9rXG4gICAgICAgICAgICAgICAgICAgIHNlbGYudmlkZW9JbWFnZSA9IGBodHRwczovL2ltZy55b3V0dWJlLmNvbS92aS8ke3NlbGYudmlkZW9JRH0vJHthdmFpbGFibGVTaXplc1tzdGVwXX0uanBnYDtcbiAgICAgICAgICAgICAgICAgICAgY2FsbGJhY2soc2VsZi52aWRlb0ltYWdlKTtcbiAgICAgICAgICAgICAgICB9IGVsc2Uge1xuICAgICAgICAgICAgICAgICAgICAvLyB0cnkgYW5vdGhlciBzaXplXG4gICAgICAgICAgICAgICAgICAgIHN0ZXArKztcbiAgICAgICAgICAgICAgICAgICAgdGhpcy5zcmMgPSBgaHR0cHM6Ly9pbWcueW91dHViZS5jb20vdmkvJHtzZWxmLnZpZGVvSUR9LyR7YXZhaWxhYmxlU2l6ZXNbc3RlcF19LmpwZ2A7XG4gICAgICAgICAgICAgICAgfVxuICAgICAgICAgICAgfTtcbiAgICAgICAgICAgIHRlbXBJbWcuc3JjID0gYGh0dHBzOi8vaW1nLnlvdXR1YmUuY29tL3ZpLyR7c2VsZi52aWRlb0lEfS8ke2F2YWlsYWJsZVNpemVzW3N0ZXBdfS5qcGdgO1xuICAgICAgICB9XG5cbiAgICAgICAgaWYgKHNlbGYudHlwZSA9PT0gJ3ZpbWVvJykge1xuICAgICAgICAgICAgbGV0IHJlcXVlc3QgPSBuZXcgWE1MSHR0cFJlcXVlc3QoKTtcbiAgICAgICAgICAgIHJlcXVlc3Qub3BlbignR0VUJywgYGh0dHBzOi8vdmltZW8uY29tL2FwaS92Mi92aWRlby8ke3NlbGYudmlkZW9JRH0uanNvbmAsIHRydWUpO1xuICAgICAgICAgICAgcmVxdWVzdC5vbnJlYWR5c3RhdGVjaGFuZ2UgPSBmdW5jdGlvbiAoKSB7XG4gICAgICAgICAgICAgICAgaWYgKHRoaXMucmVhZHlTdGF0ZSA9PT0gNCkge1xuICAgICAgICAgICAgICAgICAgICBpZiAodGhpcy5zdGF0dXMgPj0gMjAwICYmIHRoaXMuc3RhdHVzIDwgNDAwKSB7XG4gICAgICAgICAgICAgICAgICAgICAgICAvLyBTdWNjZXNzIVxuICAgICAgICAgICAgICAgICAgICAgICAgY29uc3QgcmVzcG9uc2UgPSBKU09OLnBhcnNlKHRoaXMucmVzcG9uc2VUZXh0KTtcbiAgICAgICAgICAgICAgICAgICAgICAgIHNlbGYudmlkZW9JbWFnZSA9IHJlc3BvbnNlWzBdLnRodW1ibmFpbF9sYXJnZTtcbiAgICAgICAgICAgICAgICAgICAgICAgIGNhbGxiYWNrKHNlbGYudmlkZW9JbWFnZSk7XG4gICAgICAgICAgICAgICAgICAgIH0gZWxzZSB7XG4gICAgICAgICAgICAgICAgICAgICAgICAvLyBFcnJvciA6KFxuICAgICAgICAgICAgICAgICAgICB9XG4gICAgICAgICAgICAgICAgfVxuICAgICAgICAgICAgfTtcbiAgICAgICAgICAgIHJlcXVlc3Quc2VuZCgpO1xuICAgICAgICAgICAgcmVxdWVzdCA9IG51bGw7XG4gICAgICAgIH1cbiAgICB9XG5cbiAgICAvLyBmYWxsYmFjayB0byB0aGUgb2xkIHZlcnNpb24uXG4gICAgZ2V0SWZyYW1lKGNhbGxiYWNrKSB7XG4gICAgICAgIHRoaXMuZ2V0VmlkZW8oY2FsbGJhY2spO1xuICAgIH1cblxuICAgIGdldFZpZGVvKGNhbGxiYWNrKSB7XG4gICAgICAgIGNvbnN0IHNlbGYgPSB0aGlzO1xuXG4gICAgICAgIC8vIHJldHVybiBnZW5lcmF0ZWQgdmlkZW8gYmxvY2tcbiAgICAgICAgaWYgKHNlbGYuJHZpZGVvKSB7XG4gICAgICAgICAgICBjYWxsYmFjayhzZWxmLiR2aWRlbyk7XG4gICAgICAgICAgICByZXR1cm47XG4gICAgICAgIH1cblxuICAgICAgICAvLyBnZW5lcmF0ZSBuZXcgdmlkZW8gYmxvY2tcbiAgICAgICAgc2VsZi5vbkFQSXJlYWR5KCgpID0+IHtcbiAgICAgICAgICAgIGxldCBoaWRkZW5EaXY7XG4gICAgICAgICAgICBpZiAoIXNlbGYuJHZpZGVvKSB7XG4gICAgICAgICAgICAgICAgaGlkZGVuRGl2ID0gZG9jdW1lbnQuY3JlYXRlRWxlbWVudCgnZGl2Jyk7XG4gICAgICAgICAgICAgICAgaGlkZGVuRGl2LnN0eWxlLmRpc3BsYXkgPSAnbm9uZSc7XG4gICAgICAgICAgICB9XG5cbiAgICAgICAgICAgIC8vIFlvdXR1YmVcbiAgICAgICAgICAgIGlmIChzZWxmLnR5cGUgPT09ICd5b3V0dWJlJykge1xuICAgICAgICAgICAgICAgIHNlbGYucGxheWVyT3B0aW9ucyA9IHt9O1xuICAgICAgICAgICAgICAgIHNlbGYucGxheWVyT3B0aW9ucy52aWRlb0lkID0gc2VsZi52aWRlb0lEO1xuICAgICAgICAgICAgICAgIHNlbGYucGxheWVyT3B0aW9ucy5wbGF5ZXJWYXJzID0ge1xuICAgICAgICAgICAgICAgICAgICBhdXRvaGlkZTogMSxcbiAgICAgICAgICAgICAgICAgICAgcmVsOiAwLFxuICAgICAgICAgICAgICAgICAgICBhdXRvcGxheTogMCxcbiAgICAgICAgICAgICAgICAgICAgLy8gYXV0b3BsYXkgZW5hYmxlIG9uIG1vYmlsZSBkZXZpY2VzXG4gICAgICAgICAgICAgICAgICAgIHBsYXlzaW5saW5lOiAxLFxuICAgICAgICAgICAgICAgIH07XG5cbiAgICAgICAgICAgICAgICAvLyBoaWRlIGNvbnRyb2xzXG4gICAgICAgICAgICAgICAgaWYgKCFzZWxmLm9wdGlvbnMuc2hvd0NvbnRvbHMpIHtcbiAgICAgICAgICAgICAgICAgICAgc2VsZi5wbGF5ZXJPcHRpb25zLnBsYXllclZhcnMuaXZfbG9hZF9wb2xpY3kgPSAzO1xuICAgICAgICAgICAgICAgICAgICBzZWxmLnBsYXllck9wdGlvbnMucGxheWVyVmFycy5tb2Rlc3RicmFuZGluZyA9IDE7XG4gICAgICAgICAgICAgICAgICAgIHNlbGYucGxheWVyT3B0aW9ucy5wbGF5ZXJWYXJzLmNvbnRyb2xzID0gMDtcbiAgICAgICAgICAgICAgICAgICAgc2VsZi5wbGF5ZXJPcHRpb25zLnBsYXllclZhcnMuc2hvd2luZm8gPSAwO1xuICAgICAgICAgICAgICAgICAgICBzZWxmLnBsYXllck9wdGlvbnMucGxheWVyVmFycy5kaXNhYmxla2IgPSAxO1xuICAgICAgICAgICAgICAgIH1cblxuICAgICAgICAgICAgICAgIC8vIGV2ZW50c1xuICAgICAgICAgICAgICAgIGxldCB5dFN0YXJ0ZWQ7XG4gICAgICAgICAgICAgICAgbGV0IHl0UHJvZ3Jlc3NJbnRlcnZhbDtcbiAgICAgICAgICAgICAgICBzZWxmLnBsYXllck9wdGlvbnMuZXZlbnRzID0ge1xuICAgICAgICAgICAgICAgICAgICBvblJlYWR5KGUpIHtcbiAgICAgICAgICAgICAgICAgICAgICAgIC8vIG11dGVcbiAgICAgICAgICAgICAgICAgICAgICAgIGlmIChzZWxmLm9wdGlvbnMubXV0ZSkge1xuICAgICAgICAgICAgICAgICAgICAgICAgICAgIGUudGFyZ2V0Lm11dGUoKTtcbiAgICAgICAgICAgICAgICAgICAgICAgIH0gZWxzZSBpZiAoc2VsZi5vcHRpb25zLnZvbHVtZSkge1xuICAgICAgICAgICAgICAgICAgICAgICAgICAgIGUudGFyZ2V0LnNldFZvbHVtZShzZWxmLm9wdGlvbnMudm9sdW1lKTtcbiAgICAgICAgICAgICAgICAgICAgICAgIH1cblxuICAgICAgICAgICAgICAgICAgICAgICAgLy8gYXV0b3BsYXlcbiAgICAgICAgICAgICAgICAgICAgICAgIGlmIChzZWxmLm9wdGlvbnMuYXV0b3BsYXkpIHtcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICBzZWxmLnBsYXkoc2VsZi5vcHRpb25zLnN0YXJ0VGltZSk7XG4gICAgICAgICAgICAgICAgICAgICAgICB9XG4gICAgICAgICAgICAgICAgICAgICAgICBzZWxmLmZpcmUoJ3JlYWR5JywgZSk7XG5cbiAgICAgICAgICAgICAgICAgICAgICAgIC8vIHZvbHVtZWNoYW5nZVxuICAgICAgICAgICAgICAgICAgICAgICAgc2V0SW50ZXJ2YWwoKCkgPT4ge1xuICAgICAgICAgICAgICAgICAgICAgICAgICAgIHNlbGYuZ2V0Vm9sdW1lKCh2b2x1bWUpID0+IHtcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgaWYgKHNlbGYub3B0aW9ucy52b2x1bWUgIT09IHZvbHVtZSkge1xuICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgc2VsZi5vcHRpb25zLnZvbHVtZSA9IHZvbHVtZTtcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIHNlbGYuZmlyZSgndm9sdW1lY2hhbmdlJywgZSk7XG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIH1cbiAgICAgICAgICAgICAgICAgICAgICAgICAgICB9KTtcbiAgICAgICAgICAgICAgICAgICAgICAgIH0sIDE1MCk7XG4gICAgICAgICAgICAgICAgICAgIH0sXG4gICAgICAgICAgICAgICAgICAgIG9uU3RhdGVDaGFuZ2UoZSkge1xuICAgICAgICAgICAgICAgICAgICAgICAgLy8gbG9vcFxuICAgICAgICAgICAgICAgICAgICAgICAgaWYgKHNlbGYub3B0aW9ucy5sb29wICYmIGUuZGF0YSA9PT0gWVQuUGxheWVyU3RhdGUuRU5ERUQpIHtcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICBzZWxmLnBsYXkoc2VsZi5vcHRpb25zLnN0YXJ0VGltZSk7XG4gICAgICAgICAgICAgICAgICAgICAgICB9XG4gICAgICAgICAgICAgICAgICAgICAgICBpZiAoIXl0U3RhcnRlZCAmJiBlLmRhdGEgPT09IFlULlBsYXllclN0YXRlLlBMQVlJTkcpIHtcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICB5dFN0YXJ0ZWQgPSAxO1xuICAgICAgICAgICAgICAgICAgICAgICAgICAgIHNlbGYuZmlyZSgnc3RhcnRlZCcsIGUpO1xuICAgICAgICAgICAgICAgICAgICAgICAgfVxuICAgICAgICAgICAgICAgICAgICAgICAgaWYgKGUuZGF0YSA9PT0gWVQuUGxheWVyU3RhdGUuUExBWUlORykge1xuICAgICAgICAgICAgICAgICAgICAgICAgICAgIHNlbGYuZmlyZSgncGxheScsIGUpO1xuICAgICAgICAgICAgICAgICAgICAgICAgfVxuICAgICAgICAgICAgICAgICAgICAgICAgaWYgKGUuZGF0YSA9PT0gWVQuUGxheWVyU3RhdGUuUEFVU0VEKSB7XG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgc2VsZi5maXJlKCdwYXVzZScsIGUpO1xuICAgICAgICAgICAgICAgICAgICAgICAgfVxuICAgICAgICAgICAgICAgICAgICAgICAgaWYgKGUuZGF0YSA9PT0gWVQuUGxheWVyU3RhdGUuRU5ERUQpIHtcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICBzZWxmLmZpcmUoJ2VuZGVkJywgZSk7XG4gICAgICAgICAgICAgICAgICAgICAgICB9XG5cbiAgICAgICAgICAgICAgICAgICAgICAgIC8vIHByb2dyZXNzIGNoZWNrXG4gICAgICAgICAgICAgICAgICAgICAgICBpZiAoZS5kYXRhID09PSBZVC5QbGF5ZXJTdGF0ZS5QTEFZSU5HKSB7XG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgeXRQcm9ncmVzc0ludGVydmFsID0gc2V0SW50ZXJ2YWwoKCkgPT4ge1xuICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICBzZWxmLmZpcmUoJ3RpbWV1cGRhdGUnLCBlKTtcblxuICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAvLyBjaGVjayBmb3IgZW5kIG9mIHZpZGVvIGFuZCBwbGF5IGFnYWluIG9yIHN0b3BcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgaWYgKHNlbGYub3B0aW9ucy5lbmRUaW1lICYmIHNlbGYucGxheWVyLmdldEN1cnJlbnRUaW1lKCkgPj0gc2VsZi5vcHRpb25zLmVuZFRpbWUpIHtcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIGlmIChzZWxmLm9wdGlvbnMubG9vcCkge1xuICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIHNlbGYucGxheShzZWxmLm9wdGlvbnMuc3RhcnRUaW1lKTtcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIH0gZWxzZSB7XG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgc2VsZi5wYXVzZSgpO1xuICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgfVxuICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICB9XG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgfSwgMTUwKTtcbiAgICAgICAgICAgICAgICAgICAgICAgIH0gZWxzZSB7XG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgY2xlYXJJbnRlcnZhbCh5dFByb2dyZXNzSW50ZXJ2YWwpO1xuICAgICAgICAgICAgICAgICAgICAgICAgfVxuICAgICAgICAgICAgICAgICAgICB9LFxuICAgICAgICAgICAgICAgIH07XG5cbiAgICAgICAgICAgICAgICBjb25zdCBmaXJzdEluaXQgPSAhc2VsZi4kdmlkZW87XG4gICAgICAgICAgICAgICAgaWYgKGZpcnN0SW5pdCkge1xuICAgICAgICAgICAgICAgICAgICBjb25zdCBkaXYgPSBkb2N1bWVudC5jcmVhdGVFbGVtZW50KCdkaXYnKTtcbiAgICAgICAgICAgICAgICAgICAgZGl2LnNldEF0dHJpYnV0ZSgnaWQnLCBzZWxmLnBsYXllcklEKTtcbiAgICAgICAgICAgICAgICAgICAgaGlkZGVuRGl2LmFwcGVuZENoaWxkKGRpdik7XG4gICAgICAgICAgICAgICAgICAgIGRvY3VtZW50LmJvZHkuYXBwZW5kQ2hpbGQoaGlkZGVuRGl2KTtcbiAgICAgICAgICAgICAgICB9XG4gICAgICAgICAgICAgICAgc2VsZi5wbGF5ZXIgPSBzZWxmLnBsYXllciB8fCBuZXcgd2luZG93LllULlBsYXllcihzZWxmLnBsYXllcklELCBzZWxmLnBsYXllck9wdGlvbnMpO1xuICAgICAgICAgICAgICAgIGlmIChmaXJzdEluaXQpIHtcbiAgICAgICAgICAgICAgICAgICAgc2VsZi4kdmlkZW8gPSBkb2N1bWVudC5nZXRFbGVtZW50QnlJZChzZWxmLnBsYXllcklEKTtcblxuICAgICAgICAgICAgICAgICAgICAvLyBnZXQgdmlkZW8gd2lkdGggYW5kIGhlaWdodFxuICAgICAgICAgICAgICAgICAgICBzZWxmLnZpZGVvV2lkdGggPSBwYXJzZUludChzZWxmLiR2aWRlby5nZXRBdHRyaWJ1dGUoJ3dpZHRoJyksIDEwKSB8fCAxMjgwO1xuICAgICAgICAgICAgICAgICAgICBzZWxmLnZpZGVvSGVpZ2h0ID0gcGFyc2VJbnQoc2VsZi4kdmlkZW8uZ2V0QXR0cmlidXRlKCdoZWlnaHQnKSwgMTApIHx8IDcyMDtcbiAgICAgICAgICAgICAgICB9XG4gICAgICAgICAgICB9XG5cbiAgICAgICAgICAgIC8vIFZpbWVvXG4gICAgICAgICAgICBpZiAoc2VsZi50eXBlID09PSAndmltZW8nKSB7XG4gICAgICAgICAgICAgICAgc2VsZi5wbGF5ZXJPcHRpb25zID0ge1xuICAgICAgICAgICAgICAgICAgICBpZDogc2VsZi52aWRlb0lELFxuICAgICAgICAgICAgICAgICAgICBhdXRvcGF1c2U6IDAsXG4gICAgICAgICAgICAgICAgICAgIHRyYW5zcGFyZW50OiAwLFxuICAgICAgICAgICAgICAgICAgICBhdXRvcGxheTogc2VsZi5vcHRpb25zLmF1dG9wbGF5ID8gMSA6IDAsXG4gICAgICAgICAgICAgICAgICAgIGxvb3A6IHNlbGYub3B0aW9ucy5sb29wID8gMSA6IDAsXG4gICAgICAgICAgICAgICAgICAgIG11dGVkOiBzZWxmLm9wdGlvbnMubXV0ZSA/IDEgOiAwLFxuICAgICAgICAgICAgICAgIH07XG5cbiAgICAgICAgICAgICAgICBpZiAoc2VsZi5vcHRpb25zLnZvbHVtZSkge1xuICAgICAgICAgICAgICAgICAgICBzZWxmLnBsYXllck9wdGlvbnMudm9sdW1lID0gc2VsZi5vcHRpb25zLnZvbHVtZTtcbiAgICAgICAgICAgICAgICB9XG5cbiAgICAgICAgICAgICAgICAvLyBoaWRlIGNvbnRyb2xzXG4gICAgICAgICAgICAgICAgaWYgKCFzZWxmLm9wdGlvbnMuc2hvd0NvbnRvbHMpIHtcbiAgICAgICAgICAgICAgICAgICAgc2VsZi5wbGF5ZXJPcHRpb25zLmJhZGdlID0gMDtcbiAgICAgICAgICAgICAgICAgICAgc2VsZi5wbGF5ZXJPcHRpb25zLmJ5bGluZSA9IDA7XG4gICAgICAgICAgICAgICAgICAgIHNlbGYucGxheWVyT3B0aW9ucy5wb3J0cmFpdCA9IDA7XG4gICAgICAgICAgICAgICAgICAgIHNlbGYucGxheWVyT3B0aW9ucy50aXRsZSA9IDA7XG4gICAgICAgICAgICAgICAgfVxuXG5cbiAgICAgICAgICAgICAgICBpZiAoIXNlbGYuJHZpZGVvKSB7XG4gICAgICAgICAgICAgICAgICAgIGxldCBwbGF5ZXJPcHRpb25zU3RyaW5nID0gJyc7XG4gICAgICAgICAgICAgICAgICAgIE9iamVjdC5rZXlzKHNlbGYucGxheWVyT3B0aW9ucykuZm9yRWFjaCgoa2V5KSA9PiB7XG4gICAgICAgICAgICAgICAgICAgICAgICBpZiAocGxheWVyT3B0aW9uc1N0cmluZyAhPT0gJycpIHtcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICBwbGF5ZXJPcHRpb25zU3RyaW5nICs9ICcmJztcbiAgICAgICAgICAgICAgICAgICAgICAgIH1cbiAgICAgICAgICAgICAgICAgICAgICAgIHBsYXllck9wdGlvbnNTdHJpbmcgKz0gYCR7a2V5fT0ke2VuY29kZVVSSUNvbXBvbmVudChzZWxmLnBsYXllck9wdGlvbnNba2V5XSl9YDtcbiAgICAgICAgICAgICAgICAgICAgfSk7XG5cbiAgICAgICAgICAgICAgICAgICAgLy8gd2UgbmVlZCB0byBjcmVhdGUgaWZyYW1lIG1hbnVhbGx5IGJlY2F1c2Ugd2hlbiB3ZSBjcmVhdGUgaXQgdXNpbmcgQVBJXG4gICAgICAgICAgICAgICAgICAgIC8vIGpzIGV2ZW50cyB3b24ndCB0cmlnZ2VycyBhZnRlciBpZnJhbWUgbW92ZWQgdG8gYW5vdGhlciBwbGFjZVxuICAgICAgICAgICAgICAgICAgICBzZWxmLiR2aWRlbyA9IGRvY3VtZW50LmNyZWF0ZUVsZW1lbnQoJ2lmcmFtZScpO1xuICAgICAgICAgICAgICAgICAgICBzZWxmLiR2aWRlby5zZXRBdHRyaWJ1dGUoJ2lkJywgc2VsZi5wbGF5ZXJJRCk7XG4gICAgICAgICAgICAgICAgICAgIHNlbGYuJHZpZGVvLnNldEF0dHJpYnV0ZSgnc3JjJywgYGh0dHBzOi8vcGxheWVyLnZpbWVvLmNvbS92aWRlby8ke3NlbGYudmlkZW9JRH0/JHtwbGF5ZXJPcHRpb25zU3RyaW5nfWApO1xuICAgICAgICAgICAgICAgICAgICBzZWxmLiR2aWRlby5zZXRBdHRyaWJ1dGUoJ2ZyYW1lYm9yZGVyJywgJzAnKTtcbiAgICAgICAgICAgICAgICAgICAgc2VsZi4kdmlkZW8uc2V0QXR0cmlidXRlKCdtb3phbGxvd2Z1bGxzY3JlZW4nLCAnJyk7XG4gICAgICAgICAgICAgICAgICAgIHNlbGYuJHZpZGVvLnNldEF0dHJpYnV0ZSgnYWxsb3dmdWxsc2NyZWVuJywgJycpO1xuXG4gICAgICAgICAgICAgICAgICAgIGhpZGRlbkRpdi5hcHBlbmRDaGlsZChzZWxmLiR2aWRlbyk7XG4gICAgICAgICAgICAgICAgICAgIGRvY3VtZW50LmJvZHkuYXBwZW5kQ2hpbGQoaGlkZGVuRGl2KTtcbiAgICAgICAgICAgICAgICB9XG4gICAgICAgICAgICAgICAgc2VsZi5wbGF5ZXIgPSBzZWxmLnBsYXllciB8fCBuZXcgVmltZW8uUGxheWVyKHNlbGYuJHZpZGVvLCBzZWxmLnBsYXllck9wdGlvbnMpO1xuXG4gICAgICAgICAgICAgICAgLy8gc2V0IGN1cnJlbnQgdGltZSBmb3IgYXV0b3BsYXlcbiAgICAgICAgICAgICAgICBpZiAoc2VsZi5vcHRpb25zLnN0YXJ0VGltZSAmJiBzZWxmLm9wdGlvbnMuYXV0b3BsYXkpIHtcbiAgICAgICAgICAgICAgICAgICAgc2VsZi5wbGF5ZXIuc2V0Q3VycmVudFRpbWUoc2VsZi5vcHRpb25zLnN0YXJ0VGltZSk7XG4gICAgICAgICAgICAgICAgfVxuXG4gICAgICAgICAgICAgICAgLy8gZ2V0IHZpZGVvIHdpZHRoIGFuZCBoZWlnaHRcbiAgICAgICAgICAgICAgICBzZWxmLnBsYXllci5nZXRWaWRlb1dpZHRoKCkudGhlbigod2lkdGgpID0+IHtcbiAgICAgICAgICAgICAgICAgICAgc2VsZi52aWRlb1dpZHRoID0gd2lkdGggfHwgMTI4MDtcbiAgICAgICAgICAgICAgICB9KTtcbiAgICAgICAgICAgICAgICBzZWxmLnBsYXllci5nZXRWaWRlb0hlaWdodCgpLnRoZW4oKGhlaWdodCkgPT4ge1xuICAgICAgICAgICAgICAgICAgICBzZWxmLnZpZGVvSGVpZ2h0ID0gaGVpZ2h0IHx8IDcyMDtcbiAgICAgICAgICAgICAgICB9KTtcblxuICAgICAgICAgICAgICAgIC8vIGV2ZW50c1xuICAgICAgICAgICAgICAgIGxldCB2bVN0YXJ0ZWQ7XG4gICAgICAgICAgICAgICAgc2VsZi5wbGF5ZXIub24oJ3RpbWV1cGRhdGUnLCAoZSkgPT4ge1xuICAgICAgICAgICAgICAgICAgICBpZiAoIXZtU3RhcnRlZCkge1xuICAgICAgICAgICAgICAgICAgICAgICAgc2VsZi5maXJlKCdzdGFydGVkJywgZSk7XG4gICAgICAgICAgICAgICAgICAgICAgICB2bVN0YXJ0ZWQgPSAxO1xuICAgICAgICAgICAgICAgICAgICB9XG5cbiAgICAgICAgICAgICAgICAgICAgc2VsZi5maXJlKCd0aW1ldXBkYXRlJywgZSk7XG5cbiAgICAgICAgICAgICAgICAgICAgLy8gY2hlY2sgZm9yIGVuZCBvZiB2aWRlbyBhbmQgcGxheSBhZ2FpbiBvciBzdG9wXG4gICAgICAgICAgICAgICAgICAgIGlmIChzZWxmLm9wdGlvbnMuZW5kVGltZSkge1xuICAgICAgICAgICAgICAgICAgICAgICAgaWYgKHNlbGYub3B0aW9ucy5lbmRUaW1lICYmIGUuc2Vjb25kcyA+PSBzZWxmLm9wdGlvbnMuZW5kVGltZSkge1xuICAgICAgICAgICAgICAgICAgICAgICAgICAgIGlmIChzZWxmLm9wdGlvbnMubG9vcCkge1xuICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICBzZWxmLnBsYXkoc2VsZi5vcHRpb25zLnN0YXJ0VGltZSk7XG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgfSBlbHNlIHtcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgc2VsZi5wYXVzZSgpO1xuICAgICAgICAgICAgICAgICAgICAgICAgICAgIH1cbiAgICAgICAgICAgICAgICAgICAgICAgIH1cbiAgICAgICAgICAgICAgICAgICAgfVxuICAgICAgICAgICAgICAgIH0pO1xuICAgICAgICAgICAgICAgIHNlbGYucGxheWVyLm9uKCdwbGF5JywgKGUpID0+IHtcbiAgICAgICAgICAgICAgICAgICAgc2VsZi5maXJlKCdwbGF5JywgZSk7XG5cbiAgICAgICAgICAgICAgICAgICAgLy8gY2hlY2sgZm9yIHRoZSBzdGFydCB0aW1lIGFuZCBzdGFydCB3aXRoIGl0XG4gICAgICAgICAgICAgICAgICAgIGlmIChzZWxmLm9wdGlvbnMuc3RhcnRUaW1lICYmIGUuc2Vjb25kcyA9PT0gMCkge1xuICAgICAgICAgICAgICAgICAgICAgICAgc2VsZi5wbGF5KHNlbGYub3B0aW9ucy5zdGFydFRpbWUpO1xuICAgICAgICAgICAgICAgICAgICB9XG4gICAgICAgICAgICAgICAgfSk7XG4gICAgICAgICAgICAgICAgc2VsZi5wbGF5ZXIub24oJ3BhdXNlJywgKGUpID0+IHtcbiAgICAgICAgICAgICAgICAgICAgc2VsZi5maXJlKCdwYXVzZScsIGUpO1xuICAgICAgICAgICAgICAgIH0pO1xuICAgICAgICAgICAgICAgIHNlbGYucGxheWVyLm9uKCdlbmRlZCcsIChlKSA9PiB7XG4gICAgICAgICAgICAgICAgICAgIHNlbGYuZmlyZSgnZW5kZWQnLCBlKTtcbiAgICAgICAgICAgICAgICB9KTtcbiAgICAgICAgICAgICAgICBzZWxmLnBsYXllci5vbignbG9hZGVkJywgKGUpID0+IHtcbiAgICAgICAgICAgICAgICAgICAgc2VsZi5maXJlKCdyZWFkeScsIGUpO1xuICAgICAgICAgICAgICAgIH0pO1xuICAgICAgICAgICAgICAgIHNlbGYucGxheWVyLm9uKCd2b2x1bWVjaGFuZ2UnLCAoZSkgPT4ge1xuICAgICAgICAgICAgICAgICAgICBzZWxmLmZpcmUoJ3ZvbHVtZWNoYW5nZScsIGUpO1xuICAgICAgICAgICAgICAgIH0pO1xuICAgICAgICAgICAgfVxuXG4gICAgICAgICAgICAvLyBMb2NhbFxuICAgICAgICAgICAgZnVuY3Rpb24gYWRkU291cmNlVG9Mb2NhbChlbGVtZW50LCBzcmMsIHR5cGUpIHtcbiAgICAgICAgICAgICAgICBjb25zdCBzb3VyY2UgPSBkb2N1bWVudC5jcmVhdGVFbGVtZW50KCdzb3VyY2UnKTtcbiAgICAgICAgICAgICAgICBzb3VyY2Uuc3JjID0gc3JjO1xuICAgICAgICAgICAgICAgIHNvdXJjZS50eXBlID0gdHlwZTtcbiAgICAgICAgICAgICAgICBlbGVtZW50LmFwcGVuZENoaWxkKHNvdXJjZSk7XG4gICAgICAgICAgICB9XG4gICAgICAgICAgICBpZiAoc2VsZi50eXBlID09PSAnbG9jYWwnKSB7XG4gICAgICAgICAgICAgICAgaWYgKCFzZWxmLiR2aWRlbykge1xuICAgICAgICAgICAgICAgICAgICBzZWxmLiR2aWRlbyA9IGRvY3VtZW50LmNyZWF0ZUVsZW1lbnQoJ3ZpZGVvJyk7XG5cbiAgICAgICAgICAgICAgICAgICAgLy8gc2hvdyBjb250cm9sc1xuICAgICAgICAgICAgICAgICAgICBpZiAoc2VsZi5vcHRpb25zLnNob3dDb250b2xzKSB7XG4gICAgICAgICAgICAgICAgICAgICAgICBzZWxmLiR2aWRlby5jb250cm9scyA9IHRydWU7XG4gICAgICAgICAgICAgICAgICAgIH1cblxuICAgICAgICAgICAgICAgICAgICAvLyBtdXRlXG4gICAgICAgICAgICAgICAgICAgIGlmIChzZWxmLm9wdGlvbnMubXV0ZSkge1xuICAgICAgICAgICAgICAgICAgICAgICAgc2VsZi4kdmlkZW8ubXV0ZWQgPSB0cnVlO1xuICAgICAgICAgICAgICAgICAgICB9IGVsc2UgaWYgKHNlbGYuJHZpZGVvLnZvbHVtZSkge1xuICAgICAgICAgICAgICAgICAgICAgICAgc2VsZi4kdmlkZW8udm9sdW1lID0gc2VsZi5vcHRpb25zLnZvbHVtZSAvIDEwMDtcbiAgICAgICAgICAgICAgICAgICAgfVxuXG4gICAgICAgICAgICAgICAgICAgIC8vIGxvb3BcbiAgICAgICAgICAgICAgICAgICAgaWYgKHNlbGYub3B0aW9ucy5sb29wKSB7XG4gICAgICAgICAgICAgICAgICAgICAgICBzZWxmLiR2aWRlby5sb29wID0gdHJ1ZTtcbiAgICAgICAgICAgICAgICAgICAgfVxuXG4gICAgICAgICAgICAgICAgICAgIC8vIGF1dG9wbGF5IGVuYWJsZSBvbiBtb2JpbGUgZGV2aWNlc1xuICAgICAgICAgICAgICAgICAgICBzZWxmLiR2aWRlby5zZXRBdHRyaWJ1dGUoJ3BsYXlzaW5saW5lJywgJycpO1xuICAgICAgICAgICAgICAgICAgICBzZWxmLiR2aWRlby5zZXRBdHRyaWJ1dGUoJ3dlYmtpdC1wbGF5c2lubGluZScsICcnKTtcblxuICAgICAgICAgICAgICAgICAgICBzZWxmLiR2aWRlby5zZXRBdHRyaWJ1dGUoJ2lkJywgc2VsZi5wbGF5ZXJJRCk7XG4gICAgICAgICAgICAgICAgICAgIGhpZGRlbkRpdi5hcHBlbmRDaGlsZChzZWxmLiR2aWRlbyk7XG4gICAgICAgICAgICAgICAgICAgIGRvY3VtZW50LmJvZHkuYXBwZW5kQ2hpbGQoaGlkZGVuRGl2KTtcblxuICAgICAgICAgICAgICAgICAgICBPYmplY3Qua2V5cyhzZWxmLnZpZGVvSUQpLmZvckVhY2goKGtleSkgPT4ge1xuICAgICAgICAgICAgICAgICAgICAgICAgYWRkU291cmNlVG9Mb2NhbChzZWxmLiR2aWRlbywgc2VsZi52aWRlb0lEW2tleV0sIGB2aWRlby8ke2tleX1gKTtcbiAgICAgICAgICAgICAgICAgICAgfSk7XG4gICAgICAgICAgICAgICAgfVxuXG4gICAgICAgICAgICAgICAgc2VsZi5wbGF5ZXIgPSBzZWxmLnBsYXllciB8fCBzZWxmLiR2aWRlbztcblxuICAgICAgICAgICAgICAgIGxldCBsb2NTdGFydGVkO1xuICAgICAgICAgICAgICAgIHNlbGYucGxheWVyLmFkZEV2ZW50TGlzdGVuZXIoJ3BsYXlpbmcnLCAoZSkgPT4ge1xuICAgICAgICAgICAgICAgICAgICBpZiAoIWxvY1N0YXJ0ZWQpIHtcbiAgICAgICAgICAgICAgICAgICAgICAgIHNlbGYuZmlyZSgnc3RhcnRlZCcsIGUpO1xuICAgICAgICAgICAgICAgICAgICB9XG4gICAgICAgICAgICAgICAgICAgIGxvY1N0YXJ0ZWQgPSAxO1xuICAgICAgICAgICAgICAgIH0pO1xuICAgICAgICAgICAgICAgIHNlbGYucGxheWVyLmFkZEV2ZW50TGlzdGVuZXIoJ3RpbWV1cGRhdGUnLCBmdW5jdGlvbiAoZSkge1xuICAgICAgICAgICAgICAgICAgICBzZWxmLmZpcmUoJ3RpbWV1cGRhdGUnLCBlKTtcblxuICAgICAgICAgICAgICAgICAgICAvLyBjaGVjayBmb3IgZW5kIG9mIHZpZGVvIGFuZCBwbGF5IGFnYWluIG9yIHN0b3BcbiAgICAgICAgICAgICAgICAgICAgaWYgKHNlbGYub3B0aW9ucy5lbmRUaW1lKSB7XG4gICAgICAgICAgICAgICAgICAgICAgICBpZiAoc2VsZi5vcHRpb25zLmVuZFRpbWUgJiYgdGhpcy5jdXJyZW50VGltZSA+PSBzZWxmLm9wdGlvbnMuZW5kVGltZSkge1xuICAgICAgICAgICAgICAgICAgICAgICAgICAgIGlmIChzZWxmLm9wdGlvbnMubG9vcCkge1xuICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICBzZWxmLnBsYXkoc2VsZi5vcHRpb25zLnN0YXJ0VGltZSk7XG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgfSBlbHNlIHtcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgc2VsZi5wYXVzZSgpO1xuICAgICAgICAgICAgICAgICAgICAgICAgICAgIH1cbiAgICAgICAgICAgICAgICAgICAgICAgIH1cbiAgICAgICAgICAgICAgICAgICAgfVxuICAgICAgICAgICAgICAgIH0pO1xuICAgICAgICAgICAgICAgIHNlbGYucGxheWVyLmFkZEV2ZW50TGlzdGVuZXIoJ3BsYXknLCAoZSkgPT4ge1xuICAgICAgICAgICAgICAgICAgICBzZWxmLmZpcmUoJ3BsYXknLCBlKTtcbiAgICAgICAgICAgICAgICB9KTtcbiAgICAgICAgICAgICAgICBzZWxmLnBsYXllci5hZGRFdmVudExpc3RlbmVyKCdwYXVzZScsIChlKSA9PiB7XG4gICAgICAgICAgICAgICAgICAgIHNlbGYuZmlyZSgncGF1c2UnLCBlKTtcbiAgICAgICAgICAgICAgICB9KTtcbiAgICAgICAgICAgICAgICBzZWxmLnBsYXllci5hZGRFdmVudExpc3RlbmVyKCdlbmRlZCcsIChlKSA9PiB7XG4gICAgICAgICAgICAgICAgICAgIHNlbGYuZmlyZSgnZW5kZWQnLCBlKTtcbiAgICAgICAgICAgICAgICB9KTtcbiAgICAgICAgICAgICAgICBzZWxmLnBsYXllci5hZGRFdmVudExpc3RlbmVyKCdsb2FkZWRtZXRhZGF0YScsIGZ1bmN0aW9uICgpIHtcbiAgICAgICAgICAgICAgICAgICAgLy8gZ2V0IHZpZGVvIHdpZHRoIGFuZCBoZWlnaHRcbiAgICAgICAgICAgICAgICAgICAgc2VsZi52aWRlb1dpZHRoID0gdGhpcy52aWRlb1dpZHRoIHx8IDEyODA7XG4gICAgICAgICAgICAgICAgICAgIHNlbGYudmlkZW9IZWlnaHQgPSB0aGlzLnZpZGVvSGVpZ2h0IHx8IDcyMDtcblxuICAgICAgICAgICAgICAgICAgICBzZWxmLmZpcmUoJ3JlYWR5Jyk7XG5cbiAgICAgICAgICAgICAgICAgICAgLy8gYXV0b3BsYXlcbiAgICAgICAgICAgICAgICAgICAgaWYgKHNlbGYub3B0aW9ucy5hdXRvcGxheSkge1xuICAgICAgICAgICAgICAgICAgICAgICAgc2VsZi5wbGF5KHNlbGYub3B0aW9ucy5zdGFydFRpbWUpO1xuICAgICAgICAgICAgICAgICAgICB9XG4gICAgICAgICAgICAgICAgfSk7XG4gICAgICAgICAgICAgICAgc2VsZi5wbGF5ZXIuYWRkRXZlbnRMaXN0ZW5lcigndm9sdW1lY2hhbmdlJywgKGUpID0+IHtcbiAgICAgICAgICAgICAgICAgICAgc2VsZi5nZXRWb2x1bWUoKHZvbHVtZSkgPT4ge1xuICAgICAgICAgICAgICAgICAgICAgICAgc2VsZi5vcHRpb25zLnZvbHVtZSA9IHZvbHVtZTtcbiAgICAgICAgICAgICAgICAgICAgfSk7XG4gICAgICAgICAgICAgICAgICAgIHNlbGYuZmlyZSgndm9sdW1lY2hhbmdlJywgZSk7XG4gICAgICAgICAgICAgICAgfSk7XG4gICAgICAgICAgICB9XG4gICAgICAgICAgICBjYWxsYmFjayhzZWxmLiR2aWRlbyk7XG4gICAgICAgIH0pO1xuICAgIH1cblxuICAgIGluaXQoKSB7XG4gICAgICAgIGNvbnN0IHNlbGYgPSB0aGlzO1xuXG4gICAgICAgIHNlbGYucGxheWVySUQgPSBgVmlkZW9Xb3JrZXItJHtzZWxmLklEfWA7XG4gICAgfVxuXG4gICAgbG9hZEFQSSgpIHtcbiAgICAgICAgY29uc3Qgc2VsZiA9IHRoaXM7XG5cbiAgICAgICAgaWYgKFlvdXR1YmVBUElhZGRlZCAmJiBWaW1lb0FQSWFkZGVkKSB7XG4gICAgICAgICAgICByZXR1cm47XG4gICAgICAgIH1cblxuICAgICAgICBsZXQgc3JjID0gJyc7XG5cbiAgICAgICAgLy8gbG9hZCBZb3V0dWJlIEFQSVxuICAgICAgICBpZiAoc2VsZi50eXBlID09PSAneW91dHViZScgJiYgIVlvdXR1YmVBUElhZGRlZCkge1xuICAgICAgICAgICAgWW91dHViZUFQSWFkZGVkID0gMTtcbiAgICAgICAgICAgIHNyYyA9ICdodHRwczovL3d3dy55b3V0dWJlLmNvbS9pZnJhbWVfYXBpJztcbiAgICAgICAgfVxuXG4gICAgICAgIC8vIGxvYWQgVmltZW8gQVBJXG4gICAgICAgIGlmIChzZWxmLnR5cGUgPT09ICd2aW1lbycgJiYgIVZpbWVvQVBJYWRkZWQpIHtcbiAgICAgICAgICAgIFZpbWVvQVBJYWRkZWQgPSAxO1xuICAgICAgICAgICAgc3JjID0gJ2h0dHBzOi8vcGxheWVyLnZpbWVvLmNvbS9hcGkvcGxheWVyLmpzJztcbiAgICAgICAgfVxuXG4gICAgICAgIGlmICghc3JjKSB7XG4gICAgICAgICAgICByZXR1cm47XG4gICAgICAgIH1cblxuICAgICAgICAvLyBhZGQgc2NyaXB0IGluIGhlYWQgc2VjdGlvblxuICAgICAgICBsZXQgdGFnID0gZG9jdW1lbnQuY3JlYXRlRWxlbWVudCgnc2NyaXB0Jyk7XG4gICAgICAgIGxldCBoZWFkID0gZG9jdW1lbnQuZ2V0RWxlbWVudHNCeVRhZ05hbWUoJ2hlYWQnKVswXTtcbiAgICAgICAgdGFnLnNyYyA9IHNyYztcblxuICAgICAgICBoZWFkLmFwcGVuZENoaWxkKHRhZyk7XG5cbiAgICAgICAgaGVhZCA9IG51bGw7XG4gICAgICAgIHRhZyA9IG51bGw7XG4gICAgfVxuXG4gICAgb25BUElyZWFkeShjYWxsYmFjaykge1xuICAgICAgICBjb25zdCBzZWxmID0gdGhpcztcblxuICAgICAgICAvLyBZb3V0dWJlXG4gICAgICAgIGlmIChzZWxmLnR5cGUgPT09ICd5b3V0dWJlJykge1xuICAgICAgICAgICAgLy8gTGlzdGVuIGZvciBnbG9iYWwgWVQgcGxheWVyIGNhbGxiYWNrXG4gICAgICAgICAgICBpZiAoKHR5cGVvZiBZVCA9PT0gJ3VuZGVmaW5lZCcgfHwgWVQubG9hZGVkID09PSAwKSAmJiAhbG9hZGluZ1lvdXR1YmVQbGF5ZXIpIHtcbiAgICAgICAgICAgICAgICAvLyBQcmV2ZW50cyBSZWFkeSBldmVudCBmcm9tIGJlaW5nIGNhbGxlZCB0d2ljZVxuICAgICAgICAgICAgICAgIGxvYWRpbmdZb3V0dWJlUGxheWVyID0gMTtcblxuICAgICAgICAgICAgICAgIC8vIENyZWF0ZXMgZGVmZXJyZWQgc28sIG90aGVyIHBsYXllcnMga25vdyB3aGVuIHRvIHdhaXQuXG4gICAgICAgICAgICAgICAgd2luZG93Lm9uWW91VHViZUlmcmFtZUFQSVJlYWR5ID0gZnVuY3Rpb24gKCkge1xuICAgICAgICAgICAgICAgICAgICB3aW5kb3cub25Zb3VUdWJlSWZyYW1lQVBJUmVhZHkgPSBudWxsO1xuICAgICAgICAgICAgICAgICAgICBsb2FkaW5nWW91dHViZURlZmVyLnJlc29sdmUoJ2RvbmUnKTtcbiAgICAgICAgICAgICAgICAgICAgY2FsbGJhY2soKTtcbiAgICAgICAgICAgICAgICB9O1xuICAgICAgICAgICAgfSBlbHNlIGlmICh0eXBlb2YgWVQgPT09ICdvYmplY3QnICYmIFlULmxvYWRlZCA9PT0gMSkge1xuICAgICAgICAgICAgICAgIGNhbGxiYWNrKCk7XG4gICAgICAgICAgICB9IGVsc2Uge1xuICAgICAgICAgICAgICAgIGxvYWRpbmdZb3V0dWJlRGVmZXIuZG9uZSgoKSA9PiB7XG4gICAgICAgICAgICAgICAgICAgIGNhbGxiYWNrKCk7XG4gICAgICAgICAgICAgICAgfSk7XG4gICAgICAgICAgICB9XG4gICAgICAgIH1cblxuICAgICAgICAvLyBWaW1lb1xuICAgICAgICBpZiAoc2VsZi50eXBlID09PSAndmltZW8nKSB7XG4gICAgICAgICAgICBpZiAodHlwZW9mIFZpbWVvID09PSAndW5kZWZpbmVkJyAmJiAhbG9hZGluZ1ZpbWVvUGxheWVyKSB7XG4gICAgICAgICAgICAgICAgbG9hZGluZ1ZpbWVvUGxheWVyID0gMTtcbiAgICAgICAgICAgICAgICBjb25zdCB2aW1lb0ludGVydmFsID0gc2V0SW50ZXJ2YWwoKCkgPT4ge1xuICAgICAgICAgICAgICAgICAgICBpZiAodHlwZW9mIFZpbWVvICE9PSAndW5kZWZpbmVkJykge1xuICAgICAgICAgICAgICAgICAgICAgICAgY2xlYXJJbnRlcnZhbCh2aW1lb0ludGVydmFsKTtcbiAgICAgICAgICAgICAgICAgICAgICAgIGxvYWRpbmdWaW1lb0RlZmVyLnJlc29sdmUoJ2RvbmUnKTtcbiAgICAgICAgICAgICAgICAgICAgICAgIGNhbGxiYWNrKCk7XG4gICAgICAgICAgICAgICAgICAgIH1cbiAgICAgICAgICAgICAgICB9LCAyMCk7XG4gICAgICAgICAgICB9IGVsc2UgaWYgKHR5cGVvZiBWaW1lbyAhPT0gJ3VuZGVmaW5lZCcpIHtcbiAgICAgICAgICAgICAgICBjYWxsYmFjaygpO1xuICAgICAgICAgICAgfSBlbHNlIHtcbiAgICAgICAgICAgICAgICBsb2FkaW5nVmltZW9EZWZlci5kb25lKCgpID0+IHtcbiAgICAgICAgICAgICAgICAgICAgY2FsbGJhY2soKTtcbiAgICAgICAgICAgICAgICB9KTtcbiAgICAgICAgICAgIH1cbiAgICAgICAgfVxuXG4gICAgICAgIC8vIExvY2FsXG4gICAgICAgIGlmIChzZWxmLnR5cGUgPT09ICdsb2NhbCcpIHtcbiAgICAgICAgICAgIGNhbGxiYWNrKCk7XG4gICAgICAgIH1cbiAgICB9XG59XG4iXSwic291cmNlUm9vdCI6IiJ9