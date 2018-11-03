(window["webpackJsonp"] = window["webpackJsonp"] || []).push([["backwards"],{

/***/ "./assets/js/backwards.js":
/*!********************************!*\
  !*** ./assets/js/backwards.js ***!
  \********************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

// assets/js/backwards.js
__webpack_require__(/*! html5shiv */ "./node_modules/html5shiv/dist/html5shiv.js");

__webpack_require__(/*! respond.js/dest/respond.src.js */ "./node_modules/respond.js/dest/respond.src.js");

/***/ }),

/***/ "./node_modules/html5shiv/dist/html5shiv.js":
/*!**************************************************!*\
  !*** ./node_modules/html5shiv/dist/html5shiv.js ***!
  \**************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

/**
* @preserve HTML5 Shiv 3.7.3 | @afarkas @jdalton @jon_neal @rem | MIT/GPL2 Licensed
*/
;(function(window, document) {
/*jshint evil:true */
  /** version */
  var version = '3.7.3-pre';

  /** Preset options */
  var options = window.html5 || {};

  /** Used to skip problem elements */
  var reSkip = /^<|^(?:button|map|select|textarea|object|iframe|option|optgroup)$/i;

  /** Not all elements can be cloned in IE **/
  var saveClones = /^(?:a|b|code|div|fieldset|h1|h2|h3|h4|h5|h6|i|label|li|ol|p|q|span|strong|style|table|tbody|td|th|tr|ul)$/i;

  /** Detect whether the browser supports default html5 styles */
  var supportsHtml5Styles;

  /** Name of the expando, to work with multiple documents or to re-shiv one document */
  var expando = '_html5shiv';

  /** The id for the the documents expando */
  var expanID = 0;

  /** Cached data for each document */
  var expandoData = {};

  /** Detect whether the browser supports unknown elements */
  var supportsUnknownElements;

  (function() {
    try {
        var a = document.createElement('a');
        a.innerHTML = '<xyz></xyz>';
        //if the hidden property is implemented we can assume, that the browser supports basic HTML5 Styles
        supportsHtml5Styles = ('hidden' in a);

        supportsUnknownElements = a.childNodes.length == 1 || (function() {
          // assign a false positive if unable to shiv
          (document.createElement)('a');
          var frag = document.createDocumentFragment();
          return (
            typeof frag.cloneNode == 'undefined' ||
            typeof frag.createDocumentFragment == 'undefined' ||
            typeof frag.createElement == 'undefined'
          );
        }());
    } catch(e) {
      // assign a false positive if detection fails => unable to shiv
      supportsHtml5Styles = true;
      supportsUnknownElements = true;
    }

  }());

  /*--------------------------------------------------------------------------*/

  /**
   * Creates a style sheet with the given CSS text and adds it to the document.
   * @private
   * @param {Document} ownerDocument The document.
   * @param {String} cssText The CSS text.
   * @returns {StyleSheet} The style element.
   */
  function addStyleSheet(ownerDocument, cssText) {
    var p = ownerDocument.createElement('p'),
        parent = ownerDocument.getElementsByTagName('head')[0] || ownerDocument.documentElement;

    p.innerHTML = 'x<style>' + cssText + '</style>';
    return parent.insertBefore(p.lastChild, parent.firstChild);
  }

  /**
   * Returns the value of `html5.elements` as an array.
   * @private
   * @returns {Array} An array of shived element node names.
   */
  function getElements() {
    var elements = html5.elements;
    return typeof elements == 'string' ? elements.split(' ') : elements;
  }

  /**
   * Extends the built-in list of html5 elements
   * @memberOf html5
   * @param {String|Array} newElements whitespace separated list or array of new element names to shiv
   * @param {Document} ownerDocument The context document.
   */
  function addElements(newElements, ownerDocument) {
    var elements = html5.elements;
    if(typeof elements != 'string'){
      elements = elements.join(' ');
    }
    if(typeof newElements != 'string'){
      newElements = newElements.join(' ');
    }
    html5.elements = elements +' '+ newElements;
    shivDocument(ownerDocument);
  }

   /**
   * Returns the data associated to the given document
   * @private
   * @param {Document} ownerDocument The document.
   * @returns {Object} An object of data.
   */
  function getExpandoData(ownerDocument) {
    var data = expandoData[ownerDocument[expando]];
    if (!data) {
        data = {};
        expanID++;
        ownerDocument[expando] = expanID;
        expandoData[expanID] = data;
    }
    return data;
  }

  /**
   * returns a shived element for the given nodeName and document
   * @memberOf html5
   * @param {String} nodeName name of the element
   * @param {Document} ownerDocument The context document.
   * @returns {Object} The shived element.
   */
  function createElement(nodeName, ownerDocument, data){
    if (!ownerDocument) {
        ownerDocument = document;
    }
    if(supportsUnknownElements){
        return ownerDocument.createElement(nodeName);
    }
    if (!data) {
        data = getExpandoData(ownerDocument);
    }
    var node;

    if (data.cache[nodeName]) {
        node = data.cache[nodeName].cloneNode();
    } else if (saveClones.test(nodeName)) {
        node = (data.cache[nodeName] = data.createElem(nodeName)).cloneNode();
    } else {
        node = data.createElem(nodeName);
    }

    // Avoid adding some elements to fragments in IE < 9 because
    // * Attributes like `name` or `type` cannot be set/changed once an element
    //   is inserted into a document/fragment
    // * Link elements with `src` attributes that are inaccessible, as with
    //   a 403 response, will cause the tab/window to crash
    // * Script elements appended to fragments will execute when their `src`
    //   or `text` property is set
    return node.canHaveChildren && !reSkip.test(nodeName) && !node.tagUrn ? data.frag.appendChild(node) : node;
  }

  /**
   * returns a shived DocumentFragment for the given document
   * @memberOf html5
   * @param {Document} ownerDocument The context document.
   * @returns {Object} The shived DocumentFragment.
   */
  function createDocumentFragment(ownerDocument, data){
    if (!ownerDocument) {
        ownerDocument = document;
    }
    if(supportsUnknownElements){
        return ownerDocument.createDocumentFragment();
    }
    data = data || getExpandoData(ownerDocument);
    var clone = data.frag.cloneNode(),
        i = 0,
        elems = getElements(),
        l = elems.length;
    for(;i<l;i++){
        clone.createElement(elems[i]);
    }
    return clone;
  }

  /**
   * Shivs the `createElement` and `createDocumentFragment` methods of the document.
   * @private
   * @param {Document|DocumentFragment} ownerDocument The document.
   * @param {Object} data of the document.
   */
  function shivMethods(ownerDocument, data) {
    if (!data.cache) {
        data.cache = {};
        data.createElem = ownerDocument.createElement;
        data.createFrag = ownerDocument.createDocumentFragment;
        data.frag = data.createFrag();
    }


    ownerDocument.createElement = function(nodeName) {
      //abort shiv
      if (!html5.shivMethods) {
          return data.createElem(nodeName);
      }
      return createElement(nodeName, ownerDocument, data);
    };

    ownerDocument.createDocumentFragment = Function('h,f', 'return function(){' +
      'var n=f.cloneNode(),c=n.createElement;' +
      'h.shivMethods&&(' +
        // unroll the `createElement` calls
        getElements().join().replace(/[\w\-:]+/g, function(nodeName) {
          data.createElem(nodeName);
          data.frag.createElement(nodeName);
          return 'c("' + nodeName + '")';
        }) +
      ');return n}'
    )(html5, data.frag);
  }

  /*--------------------------------------------------------------------------*/

  /**
   * Shivs the given document.
   * @memberOf html5
   * @param {Document} ownerDocument The document to shiv.
   * @returns {Document} The shived document.
   */
  function shivDocument(ownerDocument) {
    if (!ownerDocument) {
        ownerDocument = document;
    }
    var data = getExpandoData(ownerDocument);

    if (html5.shivCSS && !supportsHtml5Styles && !data.hasCSS) {
      data.hasCSS = !!addStyleSheet(ownerDocument,
        // corrects block display not defined in IE6/7/8/9
        'article,aside,dialog,figcaption,figure,footer,header,hgroup,main,nav,section{display:block}' +
        // adds styling not present in IE6/7/8/9
        'mark{background:#FF0;color:#000}' +
        // hides non-rendered elements
        'template{display:none}'
      );
    }
    if (!supportsUnknownElements) {
      shivMethods(ownerDocument, data);
    }
    return ownerDocument;
  }

  /*--------------------------------------------------------------------------*/

  /**
   * The `html5` object is exposed so that more elements can be shived and
   * existing shiving can be detected on iframes.
   * @type Object
   * @example
   *
   * // options can be changed before the script is included
   * html5 = { 'elements': 'mark section', 'shivCSS': false, 'shivMethods': false };
   */
  var html5 = {

    /**
     * An array or space separated string of node names of the elements to shiv.
     * @memberOf html5
     * @type Array|String
     */
    'elements': options.elements || 'abbr article aside audio bdi canvas data datalist details dialog figcaption figure footer header hgroup main mark meter nav output picture progress section summary template time video',

    /**
     * current version of html5shiv
     */
    'version': version,

    /**
     * A flag to indicate that the HTML5 style sheet should be inserted.
     * @memberOf html5
     * @type Boolean
     */
    'shivCSS': (options.shivCSS !== false),

    /**
     * Is equal to true if a browser supports creating unknown/HTML5 elements
     * @memberOf html5
     * @type boolean
     */
    'supportsUnknownElements': supportsUnknownElements,

    /**
     * A flag to indicate that the document's `createElement` and `createDocumentFragment`
     * methods should be overwritten.
     * @memberOf html5
     * @type Boolean
     */
    'shivMethods': (options.shivMethods !== false),

    /**
     * A string to describe the type of `html5` object ("default" or "default print").
     * @memberOf html5
     * @type String
     */
    'type': 'default',

    // shivs the document according to the specified `html5` object options
    'shivDocument': shivDocument,

    //creates a shived element
    createElement: createElement,

    //creates a shived documentFragment
    createDocumentFragment: createDocumentFragment,

    //extends list of elements
    addElements: addElements
  };

  /*--------------------------------------------------------------------------*/

  // expose html5
  window.html5 = html5;

  // shiv the document
  shivDocument(document);

  if( true && module.exports){
    module.exports = html5;
  }

}(typeof window !== "undefined" ? window : this, document));


/***/ }),

/***/ "./node_modules/respond.js/dest/respond.src.js":
/*!*****************************************************!*\
  !*** ./node_modules/respond.js/dest/respond.src.js ***!
  \*****************************************************/
/*! no static exports found */
/***/ (function(module, exports) {

/*! Respond.js v1.4.2: min/max-width media query polyfill
 * Copyright 2014 Scott Jehl
 * Licensed under MIT
 * http://j.mp/respondjs */

/*! matchMedia() polyfill - Test a CSS media type/query in JS. Authors & copyright (c) 2012: Scott Jehl, Paul Irish, Nicholas Zakas. Dual MIT/BSD license */
/*! NOTE: If you're already including a window.matchMedia polyfill via Modernizr or otherwise, you don't need this part */
(function(w) {
  "use strict";
  w.matchMedia = w.matchMedia || function(doc, undefined) {
    var bool, docElem = doc.documentElement, refNode = docElem.firstElementChild || docElem.firstChild, fakeBody = doc.createElement("body"), div = doc.createElement("div");
    div.id = "mq-test-1";
    div.style.cssText = "position:absolute;top:-100em";
    fakeBody.style.background = "none";
    fakeBody.appendChild(div);
    return function(q) {
      div.innerHTML = '&shy;<style media="' + q + '"> #mq-test-1 { width: 42px; }</style>';
      docElem.insertBefore(fakeBody, refNode);
      bool = div.offsetWidth === 42;
      docElem.removeChild(fakeBody);
      return {
        matches: bool,
        media: q
      };
    };
  }(w.document);
})(this);

(function(w) {
  "use strict";
  var respond = {};
  w.respond = respond;
  respond.update = function() {};
  var requestQueue = [], xmlHttp = function() {
    var xmlhttpmethod = false;
    try {
      xmlhttpmethod = new w.XMLHttpRequest();
    } catch (e) {
      xmlhttpmethod = new w.ActiveXObject("Microsoft.XMLHTTP");
    }
    return function() {
      return xmlhttpmethod;
    };
  }(), ajax = function(url, callback) {
    var req = xmlHttp();
    if (!req) {
      return;
    }
    req.open("GET", url, true);
    req.onreadystatechange = function() {
      if (req.readyState !== 4 || req.status !== 200 && req.status !== 304) {
        return;
      }
      callback(req.responseText);
    };
    if (req.readyState === 4) {
      return;
    }
    req.send(null);
  }, isUnsupportedMediaQuery = function(query) {
    return query.replace(respond.regex.minmaxwh, "").match(respond.regex.other);
  };
  respond.ajax = ajax;
  respond.queue = requestQueue;
  respond.unsupportedmq = isUnsupportedMediaQuery;
  respond.regex = {
    media: /@media[^\{]+\{([^\{\}]*\{[^\}\{]*\})+/gi,
    keyframes: /@(?:\-(?:o|moz|webkit)\-)?keyframes[^\{]+\{(?:[^\{\}]*\{[^\}\{]*\})+[^\}]*\}/gi,
    comments: /\/\*[^*]*\*+([^/][^*]*\*+)*\//gi,
    urls: /(url\()['"]?([^\/\)'"][^:\)'"]+)['"]?(\))/g,
    findStyles: /@media *([^\{]+)\{([\S\s]+?)$/,
    only: /(only\s+)?([a-zA-Z]+)\s?/,
    minw: /\(\s*min\-width\s*:\s*(\s*[0-9\.]+)(px|em)\s*\)/,
    maxw: /\(\s*max\-width\s*:\s*(\s*[0-9\.]+)(px|em)\s*\)/,
    minmaxwh: /\(\s*m(in|ax)\-(height|width)\s*:\s*(\s*[0-9\.]+)(px|em)\s*\)/gi,
    other: /\([^\)]*\)/g
  };
  respond.mediaQueriesSupported = w.matchMedia && w.matchMedia("only all") !== null && w.matchMedia("only all").matches;
  if (respond.mediaQueriesSupported) {
    return;
  }
  var doc = w.document, docElem = doc.documentElement, mediastyles = [], rules = [], appendedEls = [], parsedSheets = {}, resizeThrottle = 30, head = doc.getElementsByTagName("head")[0] || docElem, base = doc.getElementsByTagName("base")[0], links = head.getElementsByTagName("link"), lastCall, resizeDefer, eminpx, getEmValue = function() {
    var ret, div = doc.createElement("div"), body = doc.body, originalHTMLFontSize = docElem.style.fontSize, originalBodyFontSize = body && body.style.fontSize, fakeUsed = false;
    div.style.cssText = "position:absolute;font-size:1em;width:1em";
    if (!body) {
      body = fakeUsed = doc.createElement("body");
      body.style.background = "none";
    }
    docElem.style.fontSize = "100%";
    body.style.fontSize = "100%";
    body.appendChild(div);
    if (fakeUsed) {
      docElem.insertBefore(body, docElem.firstChild);
    }
    ret = div.offsetWidth;
    if (fakeUsed) {
      docElem.removeChild(body);
    } else {
      body.removeChild(div);
    }
    docElem.style.fontSize = originalHTMLFontSize;
    if (originalBodyFontSize) {
      body.style.fontSize = originalBodyFontSize;
    }
    ret = eminpx = parseFloat(ret);
    return ret;
  }, applyMedia = function(fromResize) {
    var name = "clientWidth", docElemProp = docElem[name], currWidth = doc.compatMode === "CSS1Compat" && docElemProp || doc.body[name] || docElemProp, styleBlocks = {}, lastLink = links[links.length - 1], now = new Date().getTime();
    if (fromResize && lastCall && now - lastCall < resizeThrottle) {
      w.clearTimeout(resizeDefer);
      resizeDefer = w.setTimeout(applyMedia, resizeThrottle);
      return;
    } else {
      lastCall = now;
    }
    for (var i in mediastyles) {
      if (mediastyles.hasOwnProperty(i)) {
        var thisstyle = mediastyles[i], min = thisstyle.minw, max = thisstyle.maxw, minnull = min === null, maxnull = max === null, em = "em";
        if (!!min) {
          min = parseFloat(min) * (min.indexOf(em) > -1 ? eminpx || getEmValue() : 1);
        }
        if (!!max) {
          max = parseFloat(max) * (max.indexOf(em) > -1 ? eminpx || getEmValue() : 1);
        }
        if (!thisstyle.hasquery || (!minnull || !maxnull) && (minnull || currWidth >= min) && (maxnull || currWidth <= max)) {
          if (!styleBlocks[thisstyle.media]) {
            styleBlocks[thisstyle.media] = [];
          }
          styleBlocks[thisstyle.media].push(rules[thisstyle.rules]);
        }
      }
    }
    for (var j in appendedEls) {
      if (appendedEls.hasOwnProperty(j)) {
        if (appendedEls[j] && appendedEls[j].parentNode === head) {
          head.removeChild(appendedEls[j]);
        }
      }
    }
    appendedEls.length = 0;
    for (var k in styleBlocks) {
      if (styleBlocks.hasOwnProperty(k)) {
        var ss = doc.createElement("style"), css = styleBlocks[k].join("\n");
        ss.type = "text/css";
        ss.media = k;
        head.insertBefore(ss, lastLink.nextSibling);
        if (ss.styleSheet) {
          ss.styleSheet.cssText = css;
        } else {
          ss.appendChild(doc.createTextNode(css));
        }
        appendedEls.push(ss);
      }
    }
  }, translate = function(styles, href, media) {
    var qs = styles.replace(respond.regex.comments, "").replace(respond.regex.keyframes, "").match(respond.regex.media), ql = qs && qs.length || 0;
    href = href.substring(0, href.lastIndexOf("/"));
    var repUrls = function(css) {
      return css.replace(respond.regex.urls, "$1" + href + "$2$3");
    }, useMedia = !ql && media;
    if (href.length) {
      href += "/";
    }
    if (useMedia) {
      ql = 1;
    }
    for (var i = 0; i < ql; i++) {
      var fullq, thisq, eachq, eql;
      if (useMedia) {
        fullq = media;
        rules.push(repUrls(styles));
      } else {
        fullq = qs[i].match(respond.regex.findStyles) && RegExp.$1;
        rules.push(RegExp.$2 && repUrls(RegExp.$2));
      }
      eachq = fullq.split(",");
      eql = eachq.length;
      for (var j = 0; j < eql; j++) {
        thisq = eachq[j];
        if (isUnsupportedMediaQuery(thisq)) {
          continue;
        }
        mediastyles.push({
          media: thisq.split("(")[0].match(respond.regex.only) && RegExp.$2 || "all",
          rules: rules.length - 1,
          hasquery: thisq.indexOf("(") > -1,
          minw: thisq.match(respond.regex.minw) && parseFloat(RegExp.$1) + (RegExp.$2 || ""),
          maxw: thisq.match(respond.regex.maxw) && parseFloat(RegExp.$1) + (RegExp.$2 || "")
        });
      }
    }
    applyMedia();
  }, makeRequests = function() {
    if (requestQueue.length) {
      var thisRequest = requestQueue.shift();
      ajax(thisRequest.href, function(styles) {
        translate(styles, thisRequest.href, thisRequest.media);
        parsedSheets[thisRequest.href] = true;
        w.setTimeout(function() {
          makeRequests();
        }, 0);
      });
    }
  }, ripCSS = function() {
    for (var i = 0; i < links.length; i++) {
      var sheet = links[i], href = sheet.href, media = sheet.media, isCSS = sheet.rel && sheet.rel.toLowerCase() === "stylesheet";
      if (!!href && isCSS && !parsedSheets[href]) {
        if (sheet.styleSheet && sheet.styleSheet.rawCssText) {
          translate(sheet.styleSheet.rawCssText, href, media);
          parsedSheets[href] = true;
        } else {
          if (!/^([a-zA-Z:]*\/\/)/.test(href) && !base || href.replace(RegExp.$1, "").split("/")[0] === w.location.host) {
            if (href.substring(0, 2) === "//") {
              href = w.location.protocol + href;
            }
            requestQueue.push({
              href: href,
              media: media
            });
          }
        }
      }
    }
    makeRequests();
  };
  ripCSS();
  respond.update = ripCSS;
  respond.getEmValue = getEmValue;
  function callMedia() {
    applyMedia(true);
  }
  if (w.addEventListener) {
    w.addEventListener("resize", callMedia, false);
  } else if (w.attachEvent) {
    w.attachEvent("onresize", callMedia);
  }
})(this);

/***/ })

},[["./assets/js/backwards.js","runtime"]]]);
//# sourceMappingURL=data:application/json;charset=utf-8;base64,eyJ2ZXJzaW9uIjozLCJzb3VyY2VzIjpbIndlYnBhY2s6Ly8vLi9hc3NldHMvanMvYmFja3dhcmRzLmpzIiwid2VicGFjazovLy8uL25vZGVfbW9kdWxlcy9odG1sNXNoaXYvZGlzdC9odG1sNXNoaXYuanMiLCJ3ZWJwYWNrOi8vLy4vbm9kZV9tb2R1bGVzL3Jlc3BvbmQuanMvZGVzdC9yZXNwb25kLnNyYy5qcyJdLCJuYW1lcyI6WyJyZXF1aXJlIl0sIm1hcHBpbmdzIjoiOzs7Ozs7Ozs7QUFBQTtBQUVBQSxtQkFBTyxDQUFDLDZEQUFELENBQVA7O0FBQ0FBLG1CQUFPLENBQUMscUZBQUQsQ0FBUCxDOzs7Ozs7Ozs7OztBQ0hBO0FBQ0E7QUFDQTtBQUNBLENBQUM7QUFDRDtBQUNBO0FBQ0E7O0FBRUE7QUFDQTs7QUFFQTtBQUNBOztBQUVBO0FBQ0E7O0FBRUE7QUFDQTs7QUFFQTtBQUNBOztBQUVBO0FBQ0E7O0FBRUE7QUFDQTs7QUFFQTtBQUNBOztBQUVBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTs7QUFFQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQSxTQUFTO0FBQ1QsS0FBSztBQUNMO0FBQ0E7QUFDQTtBQUNBOztBQUVBLEdBQUc7O0FBRUg7O0FBRUE7QUFDQTtBQUNBO0FBQ0EsYUFBYSxTQUFTO0FBQ3RCLGFBQWEsT0FBTztBQUNwQixlQUFlLFdBQVc7QUFDMUI7QUFDQTtBQUNBO0FBQ0E7O0FBRUE7QUFDQTtBQUNBOztBQUVBO0FBQ0E7QUFDQTtBQUNBLGVBQWUsTUFBTTtBQUNyQjtBQUNBO0FBQ0E7QUFDQTtBQUNBOztBQUVBO0FBQ0E7QUFDQTtBQUNBLGFBQWEsYUFBYTtBQUMxQixhQUFhLFNBQVM7QUFDdEI7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBOztBQUVBO0FBQ0E7QUFDQTtBQUNBLGFBQWEsU0FBUztBQUN0QixlQUFlLE9BQU87QUFDdEI7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTs7QUFFQTtBQUNBO0FBQ0E7QUFDQSxhQUFhLE9BQU87QUFDcEIsYUFBYSxTQUFTO0FBQ3RCLGVBQWUsT0FBTztBQUN0QjtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7O0FBRUE7QUFDQTtBQUNBLEtBQUs7QUFDTDtBQUNBLEtBQUs7QUFDTDtBQUNBOztBQUVBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTs7QUFFQTtBQUNBO0FBQ0E7QUFDQSxhQUFhLFNBQVM7QUFDdEIsZUFBZSxPQUFPO0FBQ3RCO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0EsU0FBUyxJQUFJO0FBQ2I7QUFDQTtBQUNBO0FBQ0E7O0FBRUE7QUFDQTtBQUNBO0FBQ0EsYUFBYSwwQkFBMEI7QUFDdkMsYUFBYSxPQUFPO0FBQ3BCO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7OztBQUdBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBOztBQUVBLDhFQUE4RTtBQUM5RSw2Q0FBNkM7QUFDN0M7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0EsU0FBUztBQUNULFNBQVMsU0FBUztBQUNsQjtBQUNBOztBQUVBOztBQUVBO0FBQ0E7QUFDQTtBQUNBLGFBQWEsU0FBUztBQUN0QixlQUFlLFNBQVM7QUFDeEI7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBOztBQUVBO0FBQ0E7QUFDQTtBQUNBLHNGQUFzRixjQUFjO0FBQ3BHO0FBQ0EsY0FBYyxnQkFBZ0IsV0FBVztBQUN6QztBQUNBLGtCQUFrQixhQUFhO0FBQy9CO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBOztBQUVBOztBQUVBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0EsY0FBYztBQUNkO0FBQ0E7O0FBRUE7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBOztBQUVBO0FBQ0E7QUFDQTtBQUNBOztBQUVBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTs7QUFFQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7O0FBRUE7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7O0FBRUE7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBOztBQUVBO0FBQ0E7O0FBRUE7QUFDQTs7QUFFQTtBQUNBOztBQUVBO0FBQ0E7QUFDQTs7QUFFQTs7QUFFQTtBQUNBOztBQUVBO0FBQ0E7O0FBRUEsS0FBSyxLQUF5QjtBQUM5QjtBQUNBOztBQUVBLENBQUM7Ozs7Ozs7Ozs7OztBQ3JVRDtBQUNBO0FBQ0E7QUFDQTs7QUFFQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBLDJDQUEyQztBQUMzQztBQUNBO0FBQ0E7QUFDQSw0QkFBNEIsc0NBQXNDLGFBQWEsRUFBRTtBQUNqRjtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0EsR0FBRztBQUNILENBQUM7O0FBRUQ7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0EsS0FBSztBQUNMO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQSxHQUFHO0FBQ0g7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0EsR0FBRztBQUNIO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBLHNCQUFzQixJQUFJLEtBQUssRUFBRSxJQUFJLElBQUksRUFBRSxJQUFJO0FBQy9DLHVEQUF1RCxJQUFJLE9BQU8sRUFBRSxJQUFJLElBQUksRUFBRSxJQUFJLE1BQU0sSUFBSTtBQUM1RjtBQUNBO0FBQ0EsOEJBQThCLEtBQUs7QUFDbkM7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQSx3SEFBd0g7QUFDeEg7QUFDQSwyQ0FBMkMsY0FBYztBQUN6RDtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBLEtBQUs7QUFDTDtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0EsR0FBRztBQUNILHdLQUF3SztBQUN4SztBQUNBO0FBQ0E7QUFDQTtBQUNBLEtBQUs7QUFDTDtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0EsU0FBUztBQUNUO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQSxHQUFHO0FBQ0g7QUFDQTtBQUNBO0FBQ0E7QUFDQSxLQUFLO0FBQ0w7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0EsbUJBQW1CLFFBQVE7QUFDM0I7QUFDQTtBQUNBO0FBQ0E7QUFDQSxPQUFPO0FBQ1A7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBLHFCQUFxQixTQUFTO0FBQzlCO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0EsU0FBUztBQUNUO0FBQ0E7QUFDQTtBQUNBLEdBQUc7QUFDSDtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBLFNBQVM7QUFDVCxPQUFPO0FBQ1A7QUFDQSxHQUFHO0FBQ0gsbUJBQW1CLGtCQUFrQjtBQUNyQztBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0EsU0FBUztBQUNUO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0EsYUFBYTtBQUNiO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQSxHQUFHO0FBQ0g7QUFDQTtBQUNBLENBQUMsUSIsImZpbGUiOiJiYWNrd2FyZHMuanMiLCJzb3VyY2VzQ29udGVudCI6WyIvLyBhc3NldHMvanMvYmFja3dhcmRzLmpzXHJcblxyXG5yZXF1aXJlKCdodG1sNXNoaXYnKTtcclxucmVxdWlyZSgncmVzcG9uZC5qcy9kZXN0L3Jlc3BvbmQuc3JjLmpzJyk7IiwiLyoqXG4qIEBwcmVzZXJ2ZSBIVE1MNSBTaGl2IDMuNy4zIHwgQGFmYXJrYXMgQGpkYWx0b24gQGpvbl9uZWFsIEByZW0gfCBNSVQvR1BMMiBMaWNlbnNlZFxuKi9cbjsoZnVuY3Rpb24od2luZG93LCBkb2N1bWVudCkge1xuLypqc2hpbnQgZXZpbDp0cnVlICovXG4gIC8qKiB2ZXJzaW9uICovXG4gIHZhciB2ZXJzaW9uID0gJzMuNy4zLXByZSc7XG5cbiAgLyoqIFByZXNldCBvcHRpb25zICovXG4gIHZhciBvcHRpb25zID0gd2luZG93Lmh0bWw1IHx8IHt9O1xuXG4gIC8qKiBVc2VkIHRvIHNraXAgcHJvYmxlbSBlbGVtZW50cyAqL1xuICB2YXIgcmVTa2lwID0gL148fF4oPzpidXR0b258bWFwfHNlbGVjdHx0ZXh0YXJlYXxvYmplY3R8aWZyYW1lfG9wdGlvbnxvcHRncm91cCkkL2k7XG5cbiAgLyoqIE5vdCBhbGwgZWxlbWVudHMgY2FuIGJlIGNsb25lZCBpbiBJRSAqKi9cbiAgdmFyIHNhdmVDbG9uZXMgPSAvXig/OmF8Ynxjb2RlfGRpdnxmaWVsZHNldHxoMXxoMnxoM3xoNHxoNXxoNnxpfGxhYmVsfGxpfG9sfHB8cXxzcGFufHN0cm9uZ3xzdHlsZXx0YWJsZXx0Ym9keXx0ZHx0aHx0cnx1bCkkL2k7XG5cbiAgLyoqIERldGVjdCB3aGV0aGVyIHRoZSBicm93c2VyIHN1cHBvcnRzIGRlZmF1bHQgaHRtbDUgc3R5bGVzICovXG4gIHZhciBzdXBwb3J0c0h0bWw1U3R5bGVzO1xuXG4gIC8qKiBOYW1lIG9mIHRoZSBleHBhbmRvLCB0byB3b3JrIHdpdGggbXVsdGlwbGUgZG9jdW1lbnRzIG9yIHRvIHJlLXNoaXYgb25lIGRvY3VtZW50ICovXG4gIHZhciBleHBhbmRvID0gJ19odG1sNXNoaXYnO1xuXG4gIC8qKiBUaGUgaWQgZm9yIHRoZSB0aGUgZG9jdW1lbnRzIGV4cGFuZG8gKi9cbiAgdmFyIGV4cGFuSUQgPSAwO1xuXG4gIC8qKiBDYWNoZWQgZGF0YSBmb3IgZWFjaCBkb2N1bWVudCAqL1xuICB2YXIgZXhwYW5kb0RhdGEgPSB7fTtcblxuICAvKiogRGV0ZWN0IHdoZXRoZXIgdGhlIGJyb3dzZXIgc3VwcG9ydHMgdW5rbm93biBlbGVtZW50cyAqL1xuICB2YXIgc3VwcG9ydHNVbmtub3duRWxlbWVudHM7XG5cbiAgKGZ1bmN0aW9uKCkge1xuICAgIHRyeSB7XG4gICAgICAgIHZhciBhID0gZG9jdW1lbnQuY3JlYXRlRWxlbWVudCgnYScpO1xuICAgICAgICBhLmlubmVySFRNTCA9ICc8eHl6PjwveHl6Pic7XG4gICAgICAgIC8vaWYgdGhlIGhpZGRlbiBwcm9wZXJ0eSBpcyBpbXBsZW1lbnRlZCB3ZSBjYW4gYXNzdW1lLCB0aGF0IHRoZSBicm93c2VyIHN1cHBvcnRzIGJhc2ljIEhUTUw1IFN0eWxlc1xuICAgICAgICBzdXBwb3J0c0h0bWw1U3R5bGVzID0gKCdoaWRkZW4nIGluIGEpO1xuXG4gICAgICAgIHN1cHBvcnRzVW5rbm93bkVsZW1lbnRzID0gYS5jaGlsZE5vZGVzLmxlbmd0aCA9PSAxIHx8IChmdW5jdGlvbigpIHtcbiAgICAgICAgICAvLyBhc3NpZ24gYSBmYWxzZSBwb3NpdGl2ZSBpZiB1bmFibGUgdG8gc2hpdlxuICAgICAgICAgIChkb2N1bWVudC5jcmVhdGVFbGVtZW50KSgnYScpO1xuICAgICAgICAgIHZhciBmcmFnID0gZG9jdW1lbnQuY3JlYXRlRG9jdW1lbnRGcmFnbWVudCgpO1xuICAgICAgICAgIHJldHVybiAoXG4gICAgICAgICAgICB0eXBlb2YgZnJhZy5jbG9uZU5vZGUgPT0gJ3VuZGVmaW5lZCcgfHxcbiAgICAgICAgICAgIHR5cGVvZiBmcmFnLmNyZWF0ZURvY3VtZW50RnJhZ21lbnQgPT0gJ3VuZGVmaW5lZCcgfHxcbiAgICAgICAgICAgIHR5cGVvZiBmcmFnLmNyZWF0ZUVsZW1lbnQgPT0gJ3VuZGVmaW5lZCdcbiAgICAgICAgICApO1xuICAgICAgICB9KCkpO1xuICAgIH0gY2F0Y2goZSkge1xuICAgICAgLy8gYXNzaWduIGEgZmFsc2UgcG9zaXRpdmUgaWYgZGV0ZWN0aW9uIGZhaWxzID0+IHVuYWJsZSB0byBzaGl2XG4gICAgICBzdXBwb3J0c0h0bWw1U3R5bGVzID0gdHJ1ZTtcbiAgICAgIHN1cHBvcnRzVW5rbm93bkVsZW1lbnRzID0gdHJ1ZTtcbiAgICB9XG5cbiAgfSgpKTtcblxuICAvKi0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tKi9cblxuICAvKipcbiAgICogQ3JlYXRlcyBhIHN0eWxlIHNoZWV0IHdpdGggdGhlIGdpdmVuIENTUyB0ZXh0IGFuZCBhZGRzIGl0IHRvIHRoZSBkb2N1bWVudC5cbiAgICogQHByaXZhdGVcbiAgICogQHBhcmFtIHtEb2N1bWVudH0gb3duZXJEb2N1bWVudCBUaGUgZG9jdW1lbnQuXG4gICAqIEBwYXJhbSB7U3RyaW5nfSBjc3NUZXh0IFRoZSBDU1MgdGV4dC5cbiAgICogQHJldHVybnMge1N0eWxlU2hlZXR9IFRoZSBzdHlsZSBlbGVtZW50LlxuICAgKi9cbiAgZnVuY3Rpb24gYWRkU3R5bGVTaGVldChvd25lckRvY3VtZW50LCBjc3NUZXh0KSB7XG4gICAgdmFyIHAgPSBvd25lckRvY3VtZW50LmNyZWF0ZUVsZW1lbnQoJ3AnKSxcbiAgICAgICAgcGFyZW50ID0gb3duZXJEb2N1bWVudC5nZXRFbGVtZW50c0J5VGFnTmFtZSgnaGVhZCcpWzBdIHx8IG93bmVyRG9jdW1lbnQuZG9jdW1lbnRFbGVtZW50O1xuXG4gICAgcC5pbm5lckhUTUwgPSAneDxzdHlsZT4nICsgY3NzVGV4dCArICc8L3N0eWxlPic7XG4gICAgcmV0dXJuIHBhcmVudC5pbnNlcnRCZWZvcmUocC5sYXN0Q2hpbGQsIHBhcmVudC5maXJzdENoaWxkKTtcbiAgfVxuXG4gIC8qKlxuICAgKiBSZXR1cm5zIHRoZSB2YWx1ZSBvZiBgaHRtbDUuZWxlbWVudHNgIGFzIGFuIGFycmF5LlxuICAgKiBAcHJpdmF0ZVxuICAgKiBAcmV0dXJucyB7QXJyYXl9IEFuIGFycmF5IG9mIHNoaXZlZCBlbGVtZW50IG5vZGUgbmFtZXMuXG4gICAqL1xuICBmdW5jdGlvbiBnZXRFbGVtZW50cygpIHtcbiAgICB2YXIgZWxlbWVudHMgPSBodG1sNS5lbGVtZW50cztcbiAgICByZXR1cm4gdHlwZW9mIGVsZW1lbnRzID09ICdzdHJpbmcnID8gZWxlbWVudHMuc3BsaXQoJyAnKSA6IGVsZW1lbnRzO1xuICB9XG5cbiAgLyoqXG4gICAqIEV4dGVuZHMgdGhlIGJ1aWx0LWluIGxpc3Qgb2YgaHRtbDUgZWxlbWVudHNcbiAgICogQG1lbWJlck9mIGh0bWw1XG4gICAqIEBwYXJhbSB7U3RyaW5nfEFycmF5fSBuZXdFbGVtZW50cyB3aGl0ZXNwYWNlIHNlcGFyYXRlZCBsaXN0IG9yIGFycmF5IG9mIG5ldyBlbGVtZW50IG5hbWVzIHRvIHNoaXZcbiAgICogQHBhcmFtIHtEb2N1bWVudH0gb3duZXJEb2N1bWVudCBUaGUgY29udGV4dCBkb2N1bWVudC5cbiAgICovXG4gIGZ1bmN0aW9uIGFkZEVsZW1lbnRzKG5ld0VsZW1lbnRzLCBvd25lckRvY3VtZW50KSB7XG4gICAgdmFyIGVsZW1lbnRzID0gaHRtbDUuZWxlbWVudHM7XG4gICAgaWYodHlwZW9mIGVsZW1lbnRzICE9ICdzdHJpbmcnKXtcbiAgICAgIGVsZW1lbnRzID0gZWxlbWVudHMuam9pbignICcpO1xuICAgIH1cbiAgICBpZih0eXBlb2YgbmV3RWxlbWVudHMgIT0gJ3N0cmluZycpe1xuICAgICAgbmV3RWxlbWVudHMgPSBuZXdFbGVtZW50cy5qb2luKCcgJyk7XG4gICAgfVxuICAgIGh0bWw1LmVsZW1lbnRzID0gZWxlbWVudHMgKycgJysgbmV3RWxlbWVudHM7XG4gICAgc2hpdkRvY3VtZW50KG93bmVyRG9jdW1lbnQpO1xuICB9XG5cbiAgIC8qKlxuICAgKiBSZXR1cm5zIHRoZSBkYXRhIGFzc29jaWF0ZWQgdG8gdGhlIGdpdmVuIGRvY3VtZW50XG4gICAqIEBwcml2YXRlXG4gICAqIEBwYXJhbSB7RG9jdW1lbnR9IG93bmVyRG9jdW1lbnQgVGhlIGRvY3VtZW50LlxuICAgKiBAcmV0dXJucyB7T2JqZWN0fSBBbiBvYmplY3Qgb2YgZGF0YS5cbiAgICovXG4gIGZ1bmN0aW9uIGdldEV4cGFuZG9EYXRhKG93bmVyRG9jdW1lbnQpIHtcbiAgICB2YXIgZGF0YSA9IGV4cGFuZG9EYXRhW293bmVyRG9jdW1lbnRbZXhwYW5kb11dO1xuICAgIGlmICghZGF0YSkge1xuICAgICAgICBkYXRhID0ge307XG4gICAgICAgIGV4cGFuSUQrKztcbiAgICAgICAgb3duZXJEb2N1bWVudFtleHBhbmRvXSA9IGV4cGFuSUQ7XG4gICAgICAgIGV4cGFuZG9EYXRhW2V4cGFuSURdID0gZGF0YTtcbiAgICB9XG4gICAgcmV0dXJuIGRhdGE7XG4gIH1cblxuICAvKipcbiAgICogcmV0dXJucyBhIHNoaXZlZCBlbGVtZW50IGZvciB0aGUgZ2l2ZW4gbm9kZU5hbWUgYW5kIGRvY3VtZW50XG4gICAqIEBtZW1iZXJPZiBodG1sNVxuICAgKiBAcGFyYW0ge1N0cmluZ30gbm9kZU5hbWUgbmFtZSBvZiB0aGUgZWxlbWVudFxuICAgKiBAcGFyYW0ge0RvY3VtZW50fSBvd25lckRvY3VtZW50IFRoZSBjb250ZXh0IGRvY3VtZW50LlxuICAgKiBAcmV0dXJucyB7T2JqZWN0fSBUaGUgc2hpdmVkIGVsZW1lbnQuXG4gICAqL1xuICBmdW5jdGlvbiBjcmVhdGVFbGVtZW50KG5vZGVOYW1lLCBvd25lckRvY3VtZW50LCBkYXRhKXtcbiAgICBpZiAoIW93bmVyRG9jdW1lbnQpIHtcbiAgICAgICAgb3duZXJEb2N1bWVudCA9IGRvY3VtZW50O1xuICAgIH1cbiAgICBpZihzdXBwb3J0c1Vua25vd25FbGVtZW50cyl7XG4gICAgICAgIHJldHVybiBvd25lckRvY3VtZW50LmNyZWF0ZUVsZW1lbnQobm9kZU5hbWUpO1xuICAgIH1cbiAgICBpZiAoIWRhdGEpIHtcbiAgICAgICAgZGF0YSA9IGdldEV4cGFuZG9EYXRhKG93bmVyRG9jdW1lbnQpO1xuICAgIH1cbiAgICB2YXIgbm9kZTtcblxuICAgIGlmIChkYXRhLmNhY2hlW25vZGVOYW1lXSkge1xuICAgICAgICBub2RlID0gZGF0YS5jYWNoZVtub2RlTmFtZV0uY2xvbmVOb2RlKCk7XG4gICAgfSBlbHNlIGlmIChzYXZlQ2xvbmVzLnRlc3Qobm9kZU5hbWUpKSB7XG4gICAgICAgIG5vZGUgPSAoZGF0YS5jYWNoZVtub2RlTmFtZV0gPSBkYXRhLmNyZWF0ZUVsZW0obm9kZU5hbWUpKS5jbG9uZU5vZGUoKTtcbiAgICB9IGVsc2Uge1xuICAgICAgICBub2RlID0gZGF0YS5jcmVhdGVFbGVtKG5vZGVOYW1lKTtcbiAgICB9XG5cbiAgICAvLyBBdm9pZCBhZGRpbmcgc29tZSBlbGVtZW50cyB0byBmcmFnbWVudHMgaW4gSUUgPCA5IGJlY2F1c2VcbiAgICAvLyAqIEF0dHJpYnV0ZXMgbGlrZSBgbmFtZWAgb3IgYHR5cGVgIGNhbm5vdCBiZSBzZXQvY2hhbmdlZCBvbmNlIGFuIGVsZW1lbnRcbiAgICAvLyAgIGlzIGluc2VydGVkIGludG8gYSBkb2N1bWVudC9mcmFnbWVudFxuICAgIC8vICogTGluayBlbGVtZW50cyB3aXRoIGBzcmNgIGF0dHJpYnV0ZXMgdGhhdCBhcmUgaW5hY2Nlc3NpYmxlLCBhcyB3aXRoXG4gICAgLy8gICBhIDQwMyByZXNwb25zZSwgd2lsbCBjYXVzZSB0aGUgdGFiL3dpbmRvdyB0byBjcmFzaFxuICAgIC8vICogU2NyaXB0IGVsZW1lbnRzIGFwcGVuZGVkIHRvIGZyYWdtZW50cyB3aWxsIGV4ZWN1dGUgd2hlbiB0aGVpciBgc3JjYFxuICAgIC8vICAgb3IgYHRleHRgIHByb3BlcnR5IGlzIHNldFxuICAgIHJldHVybiBub2RlLmNhbkhhdmVDaGlsZHJlbiAmJiAhcmVTa2lwLnRlc3Qobm9kZU5hbWUpICYmICFub2RlLnRhZ1VybiA/IGRhdGEuZnJhZy5hcHBlbmRDaGlsZChub2RlKSA6IG5vZGU7XG4gIH1cblxuICAvKipcbiAgICogcmV0dXJucyBhIHNoaXZlZCBEb2N1bWVudEZyYWdtZW50IGZvciB0aGUgZ2l2ZW4gZG9jdW1lbnRcbiAgICogQG1lbWJlck9mIGh0bWw1XG4gICAqIEBwYXJhbSB7RG9jdW1lbnR9IG93bmVyRG9jdW1lbnQgVGhlIGNvbnRleHQgZG9jdW1lbnQuXG4gICAqIEByZXR1cm5zIHtPYmplY3R9IFRoZSBzaGl2ZWQgRG9jdW1lbnRGcmFnbWVudC5cbiAgICovXG4gIGZ1bmN0aW9uIGNyZWF0ZURvY3VtZW50RnJhZ21lbnQob3duZXJEb2N1bWVudCwgZGF0YSl7XG4gICAgaWYgKCFvd25lckRvY3VtZW50KSB7XG4gICAgICAgIG93bmVyRG9jdW1lbnQgPSBkb2N1bWVudDtcbiAgICB9XG4gICAgaWYoc3VwcG9ydHNVbmtub3duRWxlbWVudHMpe1xuICAgICAgICByZXR1cm4gb3duZXJEb2N1bWVudC5jcmVhdGVEb2N1bWVudEZyYWdtZW50KCk7XG4gICAgfVxuICAgIGRhdGEgPSBkYXRhIHx8IGdldEV4cGFuZG9EYXRhKG93bmVyRG9jdW1lbnQpO1xuICAgIHZhciBjbG9uZSA9IGRhdGEuZnJhZy5jbG9uZU5vZGUoKSxcbiAgICAgICAgaSA9IDAsXG4gICAgICAgIGVsZW1zID0gZ2V0RWxlbWVudHMoKSxcbiAgICAgICAgbCA9IGVsZW1zLmxlbmd0aDtcbiAgICBmb3IoO2k8bDtpKyspe1xuICAgICAgICBjbG9uZS5jcmVhdGVFbGVtZW50KGVsZW1zW2ldKTtcbiAgICB9XG4gICAgcmV0dXJuIGNsb25lO1xuICB9XG5cbiAgLyoqXG4gICAqIFNoaXZzIHRoZSBgY3JlYXRlRWxlbWVudGAgYW5kIGBjcmVhdGVEb2N1bWVudEZyYWdtZW50YCBtZXRob2RzIG9mIHRoZSBkb2N1bWVudC5cbiAgICogQHByaXZhdGVcbiAgICogQHBhcmFtIHtEb2N1bWVudHxEb2N1bWVudEZyYWdtZW50fSBvd25lckRvY3VtZW50IFRoZSBkb2N1bWVudC5cbiAgICogQHBhcmFtIHtPYmplY3R9IGRhdGEgb2YgdGhlIGRvY3VtZW50LlxuICAgKi9cbiAgZnVuY3Rpb24gc2hpdk1ldGhvZHMob3duZXJEb2N1bWVudCwgZGF0YSkge1xuICAgIGlmICghZGF0YS5jYWNoZSkge1xuICAgICAgICBkYXRhLmNhY2hlID0ge307XG4gICAgICAgIGRhdGEuY3JlYXRlRWxlbSA9IG93bmVyRG9jdW1lbnQuY3JlYXRlRWxlbWVudDtcbiAgICAgICAgZGF0YS5jcmVhdGVGcmFnID0gb3duZXJEb2N1bWVudC5jcmVhdGVEb2N1bWVudEZyYWdtZW50O1xuICAgICAgICBkYXRhLmZyYWcgPSBkYXRhLmNyZWF0ZUZyYWcoKTtcbiAgICB9XG5cblxuICAgIG93bmVyRG9jdW1lbnQuY3JlYXRlRWxlbWVudCA9IGZ1bmN0aW9uKG5vZGVOYW1lKSB7XG4gICAgICAvL2Fib3J0IHNoaXZcbiAgICAgIGlmICghaHRtbDUuc2hpdk1ldGhvZHMpIHtcbiAgICAgICAgICByZXR1cm4gZGF0YS5jcmVhdGVFbGVtKG5vZGVOYW1lKTtcbiAgICAgIH1cbiAgICAgIHJldHVybiBjcmVhdGVFbGVtZW50KG5vZGVOYW1lLCBvd25lckRvY3VtZW50LCBkYXRhKTtcbiAgICB9O1xuXG4gICAgb3duZXJEb2N1bWVudC5jcmVhdGVEb2N1bWVudEZyYWdtZW50ID0gRnVuY3Rpb24oJ2gsZicsICdyZXR1cm4gZnVuY3Rpb24oKXsnICtcbiAgICAgICd2YXIgbj1mLmNsb25lTm9kZSgpLGM9bi5jcmVhdGVFbGVtZW50OycgK1xuICAgICAgJ2guc2hpdk1ldGhvZHMmJignICtcbiAgICAgICAgLy8gdW5yb2xsIHRoZSBgY3JlYXRlRWxlbWVudGAgY2FsbHNcbiAgICAgICAgZ2V0RWxlbWVudHMoKS5qb2luKCkucmVwbGFjZSgvW1xcd1xcLTpdKy9nLCBmdW5jdGlvbihub2RlTmFtZSkge1xuICAgICAgICAgIGRhdGEuY3JlYXRlRWxlbShub2RlTmFtZSk7XG4gICAgICAgICAgZGF0YS5mcmFnLmNyZWF0ZUVsZW1lbnQobm9kZU5hbWUpO1xuICAgICAgICAgIHJldHVybiAnYyhcIicgKyBub2RlTmFtZSArICdcIiknO1xuICAgICAgICB9KSArXG4gICAgICAnKTtyZXR1cm4gbn0nXG4gICAgKShodG1sNSwgZGF0YS5mcmFnKTtcbiAgfVxuXG4gIC8qLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0qL1xuXG4gIC8qKlxuICAgKiBTaGl2cyB0aGUgZ2l2ZW4gZG9jdW1lbnQuXG4gICAqIEBtZW1iZXJPZiBodG1sNVxuICAgKiBAcGFyYW0ge0RvY3VtZW50fSBvd25lckRvY3VtZW50IFRoZSBkb2N1bWVudCB0byBzaGl2LlxuICAgKiBAcmV0dXJucyB7RG9jdW1lbnR9IFRoZSBzaGl2ZWQgZG9jdW1lbnQuXG4gICAqL1xuICBmdW5jdGlvbiBzaGl2RG9jdW1lbnQob3duZXJEb2N1bWVudCkge1xuICAgIGlmICghb3duZXJEb2N1bWVudCkge1xuICAgICAgICBvd25lckRvY3VtZW50ID0gZG9jdW1lbnQ7XG4gICAgfVxuICAgIHZhciBkYXRhID0gZ2V0RXhwYW5kb0RhdGEob3duZXJEb2N1bWVudCk7XG5cbiAgICBpZiAoaHRtbDUuc2hpdkNTUyAmJiAhc3VwcG9ydHNIdG1sNVN0eWxlcyAmJiAhZGF0YS5oYXNDU1MpIHtcbiAgICAgIGRhdGEuaGFzQ1NTID0gISFhZGRTdHlsZVNoZWV0KG93bmVyRG9jdW1lbnQsXG4gICAgICAgIC8vIGNvcnJlY3RzIGJsb2NrIGRpc3BsYXkgbm90IGRlZmluZWQgaW4gSUU2LzcvOC85XG4gICAgICAgICdhcnRpY2xlLGFzaWRlLGRpYWxvZyxmaWdjYXB0aW9uLGZpZ3VyZSxmb290ZXIsaGVhZGVyLGhncm91cCxtYWluLG5hdixzZWN0aW9ue2Rpc3BsYXk6YmxvY2t9JyArXG4gICAgICAgIC8vIGFkZHMgc3R5bGluZyBub3QgcHJlc2VudCBpbiBJRTYvNy84LzlcbiAgICAgICAgJ21hcmt7YmFja2dyb3VuZDojRkYwO2NvbG9yOiMwMDB9JyArXG4gICAgICAgIC8vIGhpZGVzIG5vbi1yZW5kZXJlZCBlbGVtZW50c1xuICAgICAgICAndGVtcGxhdGV7ZGlzcGxheTpub25lfSdcbiAgICAgICk7XG4gICAgfVxuICAgIGlmICghc3VwcG9ydHNVbmtub3duRWxlbWVudHMpIHtcbiAgICAgIHNoaXZNZXRob2RzKG93bmVyRG9jdW1lbnQsIGRhdGEpO1xuICAgIH1cbiAgICByZXR1cm4gb3duZXJEb2N1bWVudDtcbiAgfVxuXG4gIC8qLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0qL1xuXG4gIC8qKlxuICAgKiBUaGUgYGh0bWw1YCBvYmplY3QgaXMgZXhwb3NlZCBzbyB0aGF0IG1vcmUgZWxlbWVudHMgY2FuIGJlIHNoaXZlZCBhbmRcbiAgICogZXhpc3Rpbmcgc2hpdmluZyBjYW4gYmUgZGV0ZWN0ZWQgb24gaWZyYW1lcy5cbiAgICogQHR5cGUgT2JqZWN0XG4gICAqIEBleGFtcGxlXG4gICAqXG4gICAqIC8vIG9wdGlvbnMgY2FuIGJlIGNoYW5nZWQgYmVmb3JlIHRoZSBzY3JpcHQgaXMgaW5jbHVkZWRcbiAgICogaHRtbDUgPSB7ICdlbGVtZW50cyc6ICdtYXJrIHNlY3Rpb24nLCAnc2hpdkNTUyc6IGZhbHNlLCAnc2hpdk1ldGhvZHMnOiBmYWxzZSB9O1xuICAgKi9cbiAgdmFyIGh0bWw1ID0ge1xuXG4gICAgLyoqXG4gICAgICogQW4gYXJyYXkgb3Igc3BhY2Ugc2VwYXJhdGVkIHN0cmluZyBvZiBub2RlIG5hbWVzIG9mIHRoZSBlbGVtZW50cyB0byBzaGl2LlxuICAgICAqIEBtZW1iZXJPZiBodG1sNVxuICAgICAqIEB0eXBlIEFycmF5fFN0cmluZ1xuICAgICAqL1xuICAgICdlbGVtZW50cyc6IG9wdGlvbnMuZWxlbWVudHMgfHwgJ2FiYnIgYXJ0aWNsZSBhc2lkZSBhdWRpbyBiZGkgY2FudmFzIGRhdGEgZGF0YWxpc3QgZGV0YWlscyBkaWFsb2cgZmlnY2FwdGlvbiBmaWd1cmUgZm9vdGVyIGhlYWRlciBoZ3JvdXAgbWFpbiBtYXJrIG1ldGVyIG5hdiBvdXRwdXQgcGljdHVyZSBwcm9ncmVzcyBzZWN0aW9uIHN1bW1hcnkgdGVtcGxhdGUgdGltZSB2aWRlbycsXG5cbiAgICAvKipcbiAgICAgKiBjdXJyZW50IHZlcnNpb24gb2YgaHRtbDVzaGl2XG4gICAgICovXG4gICAgJ3ZlcnNpb24nOiB2ZXJzaW9uLFxuXG4gICAgLyoqXG4gICAgICogQSBmbGFnIHRvIGluZGljYXRlIHRoYXQgdGhlIEhUTUw1IHN0eWxlIHNoZWV0IHNob3VsZCBiZSBpbnNlcnRlZC5cbiAgICAgKiBAbWVtYmVyT2YgaHRtbDVcbiAgICAgKiBAdHlwZSBCb29sZWFuXG4gICAgICovXG4gICAgJ3NoaXZDU1MnOiAob3B0aW9ucy5zaGl2Q1NTICE9PSBmYWxzZSksXG5cbiAgICAvKipcbiAgICAgKiBJcyBlcXVhbCB0byB0cnVlIGlmIGEgYnJvd3NlciBzdXBwb3J0cyBjcmVhdGluZyB1bmtub3duL0hUTUw1IGVsZW1lbnRzXG4gICAgICogQG1lbWJlck9mIGh0bWw1XG4gICAgICogQHR5cGUgYm9vbGVhblxuICAgICAqL1xuICAgICdzdXBwb3J0c1Vua25vd25FbGVtZW50cyc6IHN1cHBvcnRzVW5rbm93bkVsZW1lbnRzLFxuXG4gICAgLyoqXG4gICAgICogQSBmbGFnIHRvIGluZGljYXRlIHRoYXQgdGhlIGRvY3VtZW50J3MgYGNyZWF0ZUVsZW1lbnRgIGFuZCBgY3JlYXRlRG9jdW1lbnRGcmFnbWVudGBcbiAgICAgKiBtZXRob2RzIHNob3VsZCBiZSBvdmVyd3JpdHRlbi5cbiAgICAgKiBAbWVtYmVyT2YgaHRtbDVcbiAgICAgKiBAdHlwZSBCb29sZWFuXG4gICAgICovXG4gICAgJ3NoaXZNZXRob2RzJzogKG9wdGlvbnMuc2hpdk1ldGhvZHMgIT09IGZhbHNlKSxcblxuICAgIC8qKlxuICAgICAqIEEgc3RyaW5nIHRvIGRlc2NyaWJlIHRoZSB0eXBlIG9mIGBodG1sNWAgb2JqZWN0IChcImRlZmF1bHRcIiBvciBcImRlZmF1bHQgcHJpbnRcIikuXG4gICAgICogQG1lbWJlck9mIGh0bWw1XG4gICAgICogQHR5cGUgU3RyaW5nXG4gICAgICovXG4gICAgJ3R5cGUnOiAnZGVmYXVsdCcsXG5cbiAgICAvLyBzaGl2cyB0aGUgZG9jdW1lbnQgYWNjb3JkaW5nIHRvIHRoZSBzcGVjaWZpZWQgYGh0bWw1YCBvYmplY3Qgb3B0aW9uc1xuICAgICdzaGl2RG9jdW1lbnQnOiBzaGl2RG9jdW1lbnQsXG5cbiAgICAvL2NyZWF0ZXMgYSBzaGl2ZWQgZWxlbWVudFxuICAgIGNyZWF0ZUVsZW1lbnQ6IGNyZWF0ZUVsZW1lbnQsXG5cbiAgICAvL2NyZWF0ZXMgYSBzaGl2ZWQgZG9jdW1lbnRGcmFnbWVudFxuICAgIGNyZWF0ZURvY3VtZW50RnJhZ21lbnQ6IGNyZWF0ZURvY3VtZW50RnJhZ21lbnQsXG5cbiAgICAvL2V4dGVuZHMgbGlzdCBvZiBlbGVtZW50c1xuICAgIGFkZEVsZW1lbnRzOiBhZGRFbGVtZW50c1xuICB9O1xuXG4gIC8qLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0qL1xuXG4gIC8vIGV4cG9zZSBodG1sNVxuICB3aW5kb3cuaHRtbDUgPSBodG1sNTtcblxuICAvLyBzaGl2IHRoZSBkb2N1bWVudFxuICBzaGl2RG9jdW1lbnQoZG9jdW1lbnQpO1xuXG4gIGlmKHR5cGVvZiBtb2R1bGUgPT0gJ29iamVjdCcgJiYgbW9kdWxlLmV4cG9ydHMpe1xuICAgIG1vZHVsZS5leHBvcnRzID0gaHRtbDU7XG4gIH1cblxufSh0eXBlb2Ygd2luZG93ICE9PSBcInVuZGVmaW5lZFwiID8gd2luZG93IDogdGhpcywgZG9jdW1lbnQpKTtcbiIsIi8qISBSZXNwb25kLmpzIHYxLjQuMjogbWluL21heC13aWR0aCBtZWRpYSBxdWVyeSBwb2x5ZmlsbFxyXG4gKiBDb3B5cmlnaHQgMjAxNCBTY290dCBKZWhsXHJcbiAqIExpY2Vuc2VkIHVuZGVyIE1JVFxyXG4gKiBodHRwOi8vai5tcC9yZXNwb25kanMgKi9cclxuXHJcbi8qISBtYXRjaE1lZGlhKCkgcG9seWZpbGwgLSBUZXN0IGEgQ1NTIG1lZGlhIHR5cGUvcXVlcnkgaW4gSlMuIEF1dGhvcnMgJiBjb3B5cmlnaHQgKGMpIDIwMTI6IFNjb3R0IEplaGwsIFBhdWwgSXJpc2gsIE5pY2hvbGFzIFpha2FzLiBEdWFsIE1JVC9CU0QgbGljZW5zZSAqL1xyXG4vKiEgTk9URTogSWYgeW91J3JlIGFscmVhZHkgaW5jbHVkaW5nIGEgd2luZG93Lm1hdGNoTWVkaWEgcG9seWZpbGwgdmlhIE1vZGVybml6ciBvciBvdGhlcndpc2UsIHlvdSBkb24ndCBuZWVkIHRoaXMgcGFydCAqL1xyXG4oZnVuY3Rpb24odykge1xyXG4gIFwidXNlIHN0cmljdFwiO1xyXG4gIHcubWF0Y2hNZWRpYSA9IHcubWF0Y2hNZWRpYSB8fCBmdW5jdGlvbihkb2MsIHVuZGVmaW5lZCkge1xyXG4gICAgdmFyIGJvb2wsIGRvY0VsZW0gPSBkb2MuZG9jdW1lbnRFbGVtZW50LCByZWZOb2RlID0gZG9jRWxlbS5maXJzdEVsZW1lbnRDaGlsZCB8fCBkb2NFbGVtLmZpcnN0Q2hpbGQsIGZha2VCb2R5ID0gZG9jLmNyZWF0ZUVsZW1lbnQoXCJib2R5XCIpLCBkaXYgPSBkb2MuY3JlYXRlRWxlbWVudChcImRpdlwiKTtcclxuICAgIGRpdi5pZCA9IFwibXEtdGVzdC0xXCI7XHJcbiAgICBkaXYuc3R5bGUuY3NzVGV4dCA9IFwicG9zaXRpb246YWJzb2x1dGU7dG9wOi0xMDBlbVwiO1xyXG4gICAgZmFrZUJvZHkuc3R5bGUuYmFja2dyb3VuZCA9IFwibm9uZVwiO1xyXG4gICAgZmFrZUJvZHkuYXBwZW5kQ2hpbGQoZGl2KTtcclxuICAgIHJldHVybiBmdW5jdGlvbihxKSB7XHJcbiAgICAgIGRpdi5pbm5lckhUTUwgPSAnJnNoeTs8c3R5bGUgbWVkaWE9XCInICsgcSArICdcIj4gI21xLXRlc3QtMSB7IHdpZHRoOiA0MnB4OyB9PC9zdHlsZT4nO1xyXG4gICAgICBkb2NFbGVtLmluc2VydEJlZm9yZShmYWtlQm9keSwgcmVmTm9kZSk7XHJcbiAgICAgIGJvb2wgPSBkaXYub2Zmc2V0V2lkdGggPT09IDQyO1xyXG4gICAgICBkb2NFbGVtLnJlbW92ZUNoaWxkKGZha2VCb2R5KTtcclxuICAgICAgcmV0dXJuIHtcclxuICAgICAgICBtYXRjaGVzOiBib29sLFxyXG4gICAgICAgIG1lZGlhOiBxXHJcbiAgICAgIH07XHJcbiAgICB9O1xyXG4gIH0ody5kb2N1bWVudCk7XHJcbn0pKHRoaXMpO1xyXG5cclxuKGZ1bmN0aW9uKHcpIHtcclxuICBcInVzZSBzdHJpY3RcIjtcclxuICB2YXIgcmVzcG9uZCA9IHt9O1xyXG4gIHcucmVzcG9uZCA9IHJlc3BvbmQ7XHJcbiAgcmVzcG9uZC51cGRhdGUgPSBmdW5jdGlvbigpIHt9O1xyXG4gIHZhciByZXF1ZXN0UXVldWUgPSBbXSwgeG1sSHR0cCA9IGZ1bmN0aW9uKCkge1xyXG4gICAgdmFyIHhtbGh0dHBtZXRob2QgPSBmYWxzZTtcclxuICAgIHRyeSB7XHJcbiAgICAgIHhtbGh0dHBtZXRob2QgPSBuZXcgdy5YTUxIdHRwUmVxdWVzdCgpO1xyXG4gICAgfSBjYXRjaCAoZSkge1xyXG4gICAgICB4bWxodHRwbWV0aG9kID0gbmV3IHcuQWN0aXZlWE9iamVjdChcIk1pY3Jvc29mdC5YTUxIVFRQXCIpO1xyXG4gICAgfVxyXG4gICAgcmV0dXJuIGZ1bmN0aW9uKCkge1xyXG4gICAgICByZXR1cm4geG1saHR0cG1ldGhvZDtcclxuICAgIH07XHJcbiAgfSgpLCBhamF4ID0gZnVuY3Rpb24odXJsLCBjYWxsYmFjaykge1xyXG4gICAgdmFyIHJlcSA9IHhtbEh0dHAoKTtcclxuICAgIGlmICghcmVxKSB7XHJcbiAgICAgIHJldHVybjtcclxuICAgIH1cclxuICAgIHJlcS5vcGVuKFwiR0VUXCIsIHVybCwgdHJ1ZSk7XHJcbiAgICByZXEub25yZWFkeXN0YXRlY2hhbmdlID0gZnVuY3Rpb24oKSB7XHJcbiAgICAgIGlmIChyZXEucmVhZHlTdGF0ZSAhPT0gNCB8fCByZXEuc3RhdHVzICE9PSAyMDAgJiYgcmVxLnN0YXR1cyAhPT0gMzA0KSB7XHJcbiAgICAgICAgcmV0dXJuO1xyXG4gICAgICB9XHJcbiAgICAgIGNhbGxiYWNrKHJlcS5yZXNwb25zZVRleHQpO1xyXG4gICAgfTtcclxuICAgIGlmIChyZXEucmVhZHlTdGF0ZSA9PT0gNCkge1xyXG4gICAgICByZXR1cm47XHJcbiAgICB9XHJcbiAgICByZXEuc2VuZChudWxsKTtcclxuICB9LCBpc1Vuc3VwcG9ydGVkTWVkaWFRdWVyeSA9IGZ1bmN0aW9uKHF1ZXJ5KSB7XHJcbiAgICByZXR1cm4gcXVlcnkucmVwbGFjZShyZXNwb25kLnJlZ2V4Lm1pbm1heHdoLCBcIlwiKS5tYXRjaChyZXNwb25kLnJlZ2V4Lm90aGVyKTtcclxuICB9O1xyXG4gIHJlc3BvbmQuYWpheCA9IGFqYXg7XHJcbiAgcmVzcG9uZC5xdWV1ZSA9IHJlcXVlc3RRdWV1ZTtcclxuICByZXNwb25kLnVuc3VwcG9ydGVkbXEgPSBpc1Vuc3VwcG9ydGVkTWVkaWFRdWVyeTtcclxuICByZXNwb25kLnJlZ2V4ID0ge1xyXG4gICAgbWVkaWE6IC9AbWVkaWFbXlxce10rXFx7KFteXFx7XFx9XSpcXHtbXlxcfVxce10qXFx9KSsvZ2ksXHJcbiAgICBrZXlmcmFtZXM6IC9AKD86XFwtKD86b3xtb3p8d2Via2l0KVxcLSk/a2V5ZnJhbWVzW15cXHtdK1xceyg/OlteXFx7XFx9XSpcXHtbXlxcfVxce10qXFx9KStbXlxcfV0qXFx9L2dpLFxyXG4gICAgY29tbWVudHM6IC9cXC9cXCpbXipdKlxcKisoW14vXVteKl0qXFwqKykqXFwvL2dpLFxyXG4gICAgdXJsczogLyh1cmxcXCgpWydcIl0/KFteXFwvXFwpJ1wiXVteOlxcKSdcIl0rKVsnXCJdPyhcXCkpL2csXHJcbiAgICBmaW5kU3R5bGVzOiAvQG1lZGlhICooW15cXHtdKylcXHsoW1xcU1xcc10rPykkLyxcclxuICAgIG9ubHk6IC8ob25seVxccyspPyhbYS16QS1aXSspXFxzPy8sXHJcbiAgICBtaW53OiAvXFwoXFxzKm1pblxcLXdpZHRoXFxzKjpcXHMqKFxccypbMC05XFwuXSspKHB4fGVtKVxccypcXCkvLFxyXG4gICAgbWF4dzogL1xcKFxccyptYXhcXC13aWR0aFxccyo6XFxzKihcXHMqWzAtOVxcLl0rKShweHxlbSlcXHMqXFwpLyxcclxuICAgIG1pbm1heHdoOiAvXFwoXFxzKm0oaW58YXgpXFwtKGhlaWdodHx3aWR0aClcXHMqOlxccyooXFxzKlswLTlcXC5dKykocHh8ZW0pXFxzKlxcKS9naSxcclxuICAgIG90aGVyOiAvXFwoW15cXCldKlxcKS9nXHJcbiAgfTtcclxuICByZXNwb25kLm1lZGlhUXVlcmllc1N1cHBvcnRlZCA9IHcubWF0Y2hNZWRpYSAmJiB3Lm1hdGNoTWVkaWEoXCJvbmx5IGFsbFwiKSAhPT0gbnVsbCAmJiB3Lm1hdGNoTWVkaWEoXCJvbmx5IGFsbFwiKS5tYXRjaGVzO1xyXG4gIGlmIChyZXNwb25kLm1lZGlhUXVlcmllc1N1cHBvcnRlZCkge1xyXG4gICAgcmV0dXJuO1xyXG4gIH1cclxuICB2YXIgZG9jID0gdy5kb2N1bWVudCwgZG9jRWxlbSA9IGRvYy5kb2N1bWVudEVsZW1lbnQsIG1lZGlhc3R5bGVzID0gW10sIHJ1bGVzID0gW10sIGFwcGVuZGVkRWxzID0gW10sIHBhcnNlZFNoZWV0cyA9IHt9LCByZXNpemVUaHJvdHRsZSA9IDMwLCBoZWFkID0gZG9jLmdldEVsZW1lbnRzQnlUYWdOYW1lKFwiaGVhZFwiKVswXSB8fCBkb2NFbGVtLCBiYXNlID0gZG9jLmdldEVsZW1lbnRzQnlUYWdOYW1lKFwiYmFzZVwiKVswXSwgbGlua3MgPSBoZWFkLmdldEVsZW1lbnRzQnlUYWdOYW1lKFwibGlua1wiKSwgbGFzdENhbGwsIHJlc2l6ZURlZmVyLCBlbWlucHgsIGdldEVtVmFsdWUgPSBmdW5jdGlvbigpIHtcclxuICAgIHZhciByZXQsIGRpdiA9IGRvYy5jcmVhdGVFbGVtZW50KFwiZGl2XCIpLCBib2R5ID0gZG9jLmJvZHksIG9yaWdpbmFsSFRNTEZvbnRTaXplID0gZG9jRWxlbS5zdHlsZS5mb250U2l6ZSwgb3JpZ2luYWxCb2R5Rm9udFNpemUgPSBib2R5ICYmIGJvZHkuc3R5bGUuZm9udFNpemUsIGZha2VVc2VkID0gZmFsc2U7XHJcbiAgICBkaXYuc3R5bGUuY3NzVGV4dCA9IFwicG9zaXRpb246YWJzb2x1dGU7Zm9udC1zaXplOjFlbTt3aWR0aDoxZW1cIjtcclxuICAgIGlmICghYm9keSkge1xyXG4gICAgICBib2R5ID0gZmFrZVVzZWQgPSBkb2MuY3JlYXRlRWxlbWVudChcImJvZHlcIik7XHJcbiAgICAgIGJvZHkuc3R5bGUuYmFja2dyb3VuZCA9IFwibm9uZVwiO1xyXG4gICAgfVxyXG4gICAgZG9jRWxlbS5zdHlsZS5mb250U2l6ZSA9IFwiMTAwJVwiO1xyXG4gICAgYm9keS5zdHlsZS5mb250U2l6ZSA9IFwiMTAwJVwiO1xyXG4gICAgYm9keS5hcHBlbmRDaGlsZChkaXYpO1xyXG4gICAgaWYgKGZha2VVc2VkKSB7XHJcbiAgICAgIGRvY0VsZW0uaW5zZXJ0QmVmb3JlKGJvZHksIGRvY0VsZW0uZmlyc3RDaGlsZCk7XHJcbiAgICB9XHJcbiAgICByZXQgPSBkaXYub2Zmc2V0V2lkdGg7XHJcbiAgICBpZiAoZmFrZVVzZWQpIHtcclxuICAgICAgZG9jRWxlbS5yZW1vdmVDaGlsZChib2R5KTtcclxuICAgIH0gZWxzZSB7XHJcbiAgICAgIGJvZHkucmVtb3ZlQ2hpbGQoZGl2KTtcclxuICAgIH1cclxuICAgIGRvY0VsZW0uc3R5bGUuZm9udFNpemUgPSBvcmlnaW5hbEhUTUxGb250U2l6ZTtcclxuICAgIGlmIChvcmlnaW5hbEJvZHlGb250U2l6ZSkge1xyXG4gICAgICBib2R5LnN0eWxlLmZvbnRTaXplID0gb3JpZ2luYWxCb2R5Rm9udFNpemU7XHJcbiAgICB9XHJcbiAgICByZXQgPSBlbWlucHggPSBwYXJzZUZsb2F0KHJldCk7XHJcbiAgICByZXR1cm4gcmV0O1xyXG4gIH0sIGFwcGx5TWVkaWEgPSBmdW5jdGlvbihmcm9tUmVzaXplKSB7XHJcbiAgICB2YXIgbmFtZSA9IFwiY2xpZW50V2lkdGhcIiwgZG9jRWxlbVByb3AgPSBkb2NFbGVtW25hbWVdLCBjdXJyV2lkdGggPSBkb2MuY29tcGF0TW9kZSA9PT0gXCJDU1MxQ29tcGF0XCIgJiYgZG9jRWxlbVByb3AgfHwgZG9jLmJvZHlbbmFtZV0gfHwgZG9jRWxlbVByb3AsIHN0eWxlQmxvY2tzID0ge30sIGxhc3RMaW5rID0gbGlua3NbbGlua3MubGVuZ3RoIC0gMV0sIG5vdyA9IG5ldyBEYXRlKCkuZ2V0VGltZSgpO1xyXG4gICAgaWYgKGZyb21SZXNpemUgJiYgbGFzdENhbGwgJiYgbm93IC0gbGFzdENhbGwgPCByZXNpemVUaHJvdHRsZSkge1xyXG4gICAgICB3LmNsZWFyVGltZW91dChyZXNpemVEZWZlcik7XHJcbiAgICAgIHJlc2l6ZURlZmVyID0gdy5zZXRUaW1lb3V0KGFwcGx5TWVkaWEsIHJlc2l6ZVRocm90dGxlKTtcclxuICAgICAgcmV0dXJuO1xyXG4gICAgfSBlbHNlIHtcclxuICAgICAgbGFzdENhbGwgPSBub3c7XHJcbiAgICB9XHJcbiAgICBmb3IgKHZhciBpIGluIG1lZGlhc3R5bGVzKSB7XHJcbiAgICAgIGlmIChtZWRpYXN0eWxlcy5oYXNPd25Qcm9wZXJ0eShpKSkge1xyXG4gICAgICAgIHZhciB0aGlzc3R5bGUgPSBtZWRpYXN0eWxlc1tpXSwgbWluID0gdGhpc3N0eWxlLm1pbncsIG1heCA9IHRoaXNzdHlsZS5tYXh3LCBtaW5udWxsID0gbWluID09PSBudWxsLCBtYXhudWxsID0gbWF4ID09PSBudWxsLCBlbSA9IFwiZW1cIjtcclxuICAgICAgICBpZiAoISFtaW4pIHtcclxuICAgICAgICAgIG1pbiA9IHBhcnNlRmxvYXQobWluKSAqIChtaW4uaW5kZXhPZihlbSkgPiAtMSA/IGVtaW5weCB8fCBnZXRFbVZhbHVlKCkgOiAxKTtcclxuICAgICAgICB9XHJcbiAgICAgICAgaWYgKCEhbWF4KSB7XHJcbiAgICAgICAgICBtYXggPSBwYXJzZUZsb2F0KG1heCkgKiAobWF4LmluZGV4T2YoZW0pID4gLTEgPyBlbWlucHggfHwgZ2V0RW1WYWx1ZSgpIDogMSk7XHJcbiAgICAgICAgfVxyXG4gICAgICAgIGlmICghdGhpc3N0eWxlLmhhc3F1ZXJ5IHx8ICghbWlubnVsbCB8fCAhbWF4bnVsbCkgJiYgKG1pbm51bGwgfHwgY3VycldpZHRoID49IG1pbikgJiYgKG1heG51bGwgfHwgY3VycldpZHRoIDw9IG1heCkpIHtcclxuICAgICAgICAgIGlmICghc3R5bGVCbG9ja3NbdGhpc3N0eWxlLm1lZGlhXSkge1xyXG4gICAgICAgICAgICBzdHlsZUJsb2Nrc1t0aGlzc3R5bGUubWVkaWFdID0gW107XHJcbiAgICAgICAgICB9XHJcbiAgICAgICAgICBzdHlsZUJsb2Nrc1t0aGlzc3R5bGUubWVkaWFdLnB1c2gocnVsZXNbdGhpc3N0eWxlLnJ1bGVzXSk7XHJcbiAgICAgICAgfVxyXG4gICAgICB9XHJcbiAgICB9XHJcbiAgICBmb3IgKHZhciBqIGluIGFwcGVuZGVkRWxzKSB7XHJcbiAgICAgIGlmIChhcHBlbmRlZEVscy5oYXNPd25Qcm9wZXJ0eShqKSkge1xyXG4gICAgICAgIGlmIChhcHBlbmRlZEVsc1tqXSAmJiBhcHBlbmRlZEVsc1tqXS5wYXJlbnROb2RlID09PSBoZWFkKSB7XHJcbiAgICAgICAgICBoZWFkLnJlbW92ZUNoaWxkKGFwcGVuZGVkRWxzW2pdKTtcclxuICAgICAgICB9XHJcbiAgICAgIH1cclxuICAgIH1cclxuICAgIGFwcGVuZGVkRWxzLmxlbmd0aCA9IDA7XHJcbiAgICBmb3IgKHZhciBrIGluIHN0eWxlQmxvY2tzKSB7XHJcbiAgICAgIGlmIChzdHlsZUJsb2Nrcy5oYXNPd25Qcm9wZXJ0eShrKSkge1xyXG4gICAgICAgIHZhciBzcyA9IGRvYy5jcmVhdGVFbGVtZW50KFwic3R5bGVcIiksIGNzcyA9IHN0eWxlQmxvY2tzW2tdLmpvaW4oXCJcXG5cIik7XHJcbiAgICAgICAgc3MudHlwZSA9IFwidGV4dC9jc3NcIjtcclxuICAgICAgICBzcy5tZWRpYSA9IGs7XHJcbiAgICAgICAgaGVhZC5pbnNlcnRCZWZvcmUoc3MsIGxhc3RMaW5rLm5leHRTaWJsaW5nKTtcclxuICAgICAgICBpZiAoc3Muc3R5bGVTaGVldCkge1xyXG4gICAgICAgICAgc3Muc3R5bGVTaGVldC5jc3NUZXh0ID0gY3NzO1xyXG4gICAgICAgIH0gZWxzZSB7XHJcbiAgICAgICAgICBzcy5hcHBlbmRDaGlsZChkb2MuY3JlYXRlVGV4dE5vZGUoY3NzKSk7XHJcbiAgICAgICAgfVxyXG4gICAgICAgIGFwcGVuZGVkRWxzLnB1c2goc3MpO1xyXG4gICAgICB9XHJcbiAgICB9XHJcbiAgfSwgdHJhbnNsYXRlID0gZnVuY3Rpb24oc3R5bGVzLCBocmVmLCBtZWRpYSkge1xyXG4gICAgdmFyIHFzID0gc3R5bGVzLnJlcGxhY2UocmVzcG9uZC5yZWdleC5jb21tZW50cywgXCJcIikucmVwbGFjZShyZXNwb25kLnJlZ2V4LmtleWZyYW1lcywgXCJcIikubWF0Y2gocmVzcG9uZC5yZWdleC5tZWRpYSksIHFsID0gcXMgJiYgcXMubGVuZ3RoIHx8IDA7XHJcbiAgICBocmVmID0gaHJlZi5zdWJzdHJpbmcoMCwgaHJlZi5sYXN0SW5kZXhPZihcIi9cIikpO1xyXG4gICAgdmFyIHJlcFVybHMgPSBmdW5jdGlvbihjc3MpIHtcclxuICAgICAgcmV0dXJuIGNzcy5yZXBsYWNlKHJlc3BvbmQucmVnZXgudXJscywgXCIkMVwiICsgaHJlZiArIFwiJDIkM1wiKTtcclxuICAgIH0sIHVzZU1lZGlhID0gIXFsICYmIG1lZGlhO1xyXG4gICAgaWYgKGhyZWYubGVuZ3RoKSB7XHJcbiAgICAgIGhyZWYgKz0gXCIvXCI7XHJcbiAgICB9XHJcbiAgICBpZiAodXNlTWVkaWEpIHtcclxuICAgICAgcWwgPSAxO1xyXG4gICAgfVxyXG4gICAgZm9yICh2YXIgaSA9IDA7IGkgPCBxbDsgaSsrKSB7XHJcbiAgICAgIHZhciBmdWxscSwgdGhpc3EsIGVhY2hxLCBlcWw7XHJcbiAgICAgIGlmICh1c2VNZWRpYSkge1xyXG4gICAgICAgIGZ1bGxxID0gbWVkaWE7XHJcbiAgICAgICAgcnVsZXMucHVzaChyZXBVcmxzKHN0eWxlcykpO1xyXG4gICAgICB9IGVsc2Uge1xyXG4gICAgICAgIGZ1bGxxID0gcXNbaV0ubWF0Y2gocmVzcG9uZC5yZWdleC5maW5kU3R5bGVzKSAmJiBSZWdFeHAuJDE7XHJcbiAgICAgICAgcnVsZXMucHVzaChSZWdFeHAuJDIgJiYgcmVwVXJscyhSZWdFeHAuJDIpKTtcclxuICAgICAgfVxyXG4gICAgICBlYWNocSA9IGZ1bGxxLnNwbGl0KFwiLFwiKTtcclxuICAgICAgZXFsID0gZWFjaHEubGVuZ3RoO1xyXG4gICAgICBmb3IgKHZhciBqID0gMDsgaiA8IGVxbDsgaisrKSB7XHJcbiAgICAgICAgdGhpc3EgPSBlYWNocVtqXTtcclxuICAgICAgICBpZiAoaXNVbnN1cHBvcnRlZE1lZGlhUXVlcnkodGhpc3EpKSB7XHJcbiAgICAgICAgICBjb250aW51ZTtcclxuICAgICAgICB9XHJcbiAgICAgICAgbWVkaWFzdHlsZXMucHVzaCh7XHJcbiAgICAgICAgICBtZWRpYTogdGhpc3Euc3BsaXQoXCIoXCIpWzBdLm1hdGNoKHJlc3BvbmQucmVnZXgub25seSkgJiYgUmVnRXhwLiQyIHx8IFwiYWxsXCIsXHJcbiAgICAgICAgICBydWxlczogcnVsZXMubGVuZ3RoIC0gMSxcclxuICAgICAgICAgIGhhc3F1ZXJ5OiB0aGlzcS5pbmRleE9mKFwiKFwiKSA+IC0xLFxyXG4gICAgICAgICAgbWludzogdGhpc3EubWF0Y2gocmVzcG9uZC5yZWdleC5taW53KSAmJiBwYXJzZUZsb2F0KFJlZ0V4cC4kMSkgKyAoUmVnRXhwLiQyIHx8IFwiXCIpLFxyXG4gICAgICAgICAgbWF4dzogdGhpc3EubWF0Y2gocmVzcG9uZC5yZWdleC5tYXh3KSAmJiBwYXJzZUZsb2F0KFJlZ0V4cC4kMSkgKyAoUmVnRXhwLiQyIHx8IFwiXCIpXHJcbiAgICAgICAgfSk7XHJcbiAgICAgIH1cclxuICAgIH1cclxuICAgIGFwcGx5TWVkaWEoKTtcclxuICB9LCBtYWtlUmVxdWVzdHMgPSBmdW5jdGlvbigpIHtcclxuICAgIGlmIChyZXF1ZXN0UXVldWUubGVuZ3RoKSB7XHJcbiAgICAgIHZhciB0aGlzUmVxdWVzdCA9IHJlcXVlc3RRdWV1ZS5zaGlmdCgpO1xyXG4gICAgICBhamF4KHRoaXNSZXF1ZXN0LmhyZWYsIGZ1bmN0aW9uKHN0eWxlcykge1xyXG4gICAgICAgIHRyYW5zbGF0ZShzdHlsZXMsIHRoaXNSZXF1ZXN0LmhyZWYsIHRoaXNSZXF1ZXN0Lm1lZGlhKTtcclxuICAgICAgICBwYXJzZWRTaGVldHNbdGhpc1JlcXVlc3QuaHJlZl0gPSB0cnVlO1xyXG4gICAgICAgIHcuc2V0VGltZW91dChmdW5jdGlvbigpIHtcclxuICAgICAgICAgIG1ha2VSZXF1ZXN0cygpO1xyXG4gICAgICAgIH0sIDApO1xyXG4gICAgICB9KTtcclxuICAgIH1cclxuICB9LCByaXBDU1MgPSBmdW5jdGlvbigpIHtcclxuICAgIGZvciAodmFyIGkgPSAwOyBpIDwgbGlua3MubGVuZ3RoOyBpKyspIHtcclxuICAgICAgdmFyIHNoZWV0ID0gbGlua3NbaV0sIGhyZWYgPSBzaGVldC5ocmVmLCBtZWRpYSA9IHNoZWV0Lm1lZGlhLCBpc0NTUyA9IHNoZWV0LnJlbCAmJiBzaGVldC5yZWwudG9Mb3dlckNhc2UoKSA9PT0gXCJzdHlsZXNoZWV0XCI7XHJcbiAgICAgIGlmICghIWhyZWYgJiYgaXNDU1MgJiYgIXBhcnNlZFNoZWV0c1tocmVmXSkge1xyXG4gICAgICAgIGlmIChzaGVldC5zdHlsZVNoZWV0ICYmIHNoZWV0LnN0eWxlU2hlZXQucmF3Q3NzVGV4dCkge1xyXG4gICAgICAgICAgdHJhbnNsYXRlKHNoZWV0LnN0eWxlU2hlZXQucmF3Q3NzVGV4dCwgaHJlZiwgbWVkaWEpO1xyXG4gICAgICAgICAgcGFyc2VkU2hlZXRzW2hyZWZdID0gdHJ1ZTtcclxuICAgICAgICB9IGVsc2Uge1xyXG4gICAgICAgICAgaWYgKCEvXihbYS16QS1aOl0qXFwvXFwvKS8udGVzdChocmVmKSAmJiAhYmFzZSB8fCBocmVmLnJlcGxhY2UoUmVnRXhwLiQxLCBcIlwiKS5zcGxpdChcIi9cIilbMF0gPT09IHcubG9jYXRpb24uaG9zdCkge1xyXG4gICAgICAgICAgICBpZiAoaHJlZi5zdWJzdHJpbmcoMCwgMikgPT09IFwiLy9cIikge1xyXG4gICAgICAgICAgICAgIGhyZWYgPSB3LmxvY2F0aW9uLnByb3RvY29sICsgaHJlZjtcclxuICAgICAgICAgICAgfVxyXG4gICAgICAgICAgICByZXF1ZXN0UXVldWUucHVzaCh7XHJcbiAgICAgICAgICAgICAgaHJlZjogaHJlZixcclxuICAgICAgICAgICAgICBtZWRpYTogbWVkaWFcclxuICAgICAgICAgICAgfSk7XHJcbiAgICAgICAgICB9XHJcbiAgICAgICAgfVxyXG4gICAgICB9XHJcbiAgICB9XHJcbiAgICBtYWtlUmVxdWVzdHMoKTtcclxuICB9O1xyXG4gIHJpcENTUygpO1xyXG4gIHJlc3BvbmQudXBkYXRlID0gcmlwQ1NTO1xyXG4gIHJlc3BvbmQuZ2V0RW1WYWx1ZSA9IGdldEVtVmFsdWU7XHJcbiAgZnVuY3Rpb24gY2FsbE1lZGlhKCkge1xyXG4gICAgYXBwbHlNZWRpYSh0cnVlKTtcclxuICB9XHJcbiAgaWYgKHcuYWRkRXZlbnRMaXN0ZW5lcikge1xyXG4gICAgdy5hZGRFdmVudExpc3RlbmVyKFwicmVzaXplXCIsIGNhbGxNZWRpYSwgZmFsc2UpO1xyXG4gIH0gZWxzZSBpZiAody5hdHRhY2hFdmVudCkge1xyXG4gICAgdy5hdHRhY2hFdmVudChcIm9ucmVzaXplXCIsIGNhbGxNZWRpYSk7XHJcbiAgfVxyXG59KSh0aGlzKTsiXSwic291cmNlUm9vdCI6IiJ9