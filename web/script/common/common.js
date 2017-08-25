// require jQuery normally
const $ = require('jquery');

// create global $ and jQuery variables
global.$ = global.jQuery = $;

/**
 * Creating "BWRox" namespace.
 */
function BWRox() {
};

/**
 * Write debug output to browser console
 *
 * @param {string} message Text to write to console
 */
BWRox.prototype.debug = function(message) {
  if (typeof bwroxConfig != 'undefined' && bwroxConfig.debug == '1') {
    console.debug.apply(console, arguments);
  }
};

/**
 * Write info output to browser console
 *
 * @param {string} message Text to write to console
 */
BWRox.prototype.info = function(message) {
  if (typeof bwroxConfig != 'undefined' && bwroxConfig.info == '1') {
    console.info.apply(console, arguments);
  }
};

/**
 * Write warn output to browser console
 *
 * @param {string} message Text to write to console
 */
BWRox.prototype.warn = function(message) {
  if (typeof bwroxConfig != 'undefined' && bwroxConfig.warn == '1') {
    console.warn.apply(console, arguments);
  }
};

/**
 * Write error output to browser console
 *
 * @param {string} message Text to write to console
 */
BWRox.prototype.error = function(message) {
  if (typeof bwroxConfig != 'undefined' && bwroxConfig.error == '1') {
    console.error.apply(console, arguments);
  }
};

/**
 * Write log output to browser console
 *
 * @param {string} message Text to write to console
 */
BWRox.prototype.log = function(message) {
  if (typeof bwroxConfig != 'undefined' && bwroxConfig.log == '1') {
    console.log.apply(console, arguments);
  }
};

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
    highlightMe: function(element,check) {
        if (check == true) {
            new Effect.Highlight(element, { startcolor: '#ffffff', endcolor: '#ffff99', restorecolor: '#ffff99' });
            return true;
        } else {
            new Effect.Highlight(element, { startcolor: '#ffff99', endcolor: '#ffffff', restorecolor: '#ffffff' });
            return true;
        }
    },
    checkall: function(formname,checkname,thestate) {
        var el_collection=eval("document.forms."+formname+"."+checkname)
        for (c=0;c<el_collection.length;c++) {
        el_collection[c].checked=thestate
        }
    },
    selectAll: function(obj) {
        var checkBoxes = document.getElementsByClassName('input_check'); 
        var checker = document.getElementsByClassName('checker');
        for (i = 0; i < checker.length; i++) { 
            checker[i].checked = obj.checked;
        }
        for (i = 0; i < checkBoxes.length; i++) { 
            if (obj.checked == true) {
                checkBoxes[i].checked = true; // this checks all the boxes 
                //common.highlightMe(checkBoxes[i].parentNode.parentNode, true);
            } else { 
                checkBoxes[i].checked = false; // this unchecks all the boxes 
                //common.highlightMe(checkBoxes[i].parentNode.parentNode, false);
            } 
        } 
    },
    // build regular expression object to find empty string or any number of spaces
    checkEmpty: function(TextObject) {
        var blankRE=/^\s*$/;
        if(blankRE.test(TextObject.value)) {
            return false;
        } else return true;
    },
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
