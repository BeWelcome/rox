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
