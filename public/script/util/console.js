// console is usefull to log information in Firebug console
// if firebug is not present, define empty function to avoid JS errors
if (!window.console) console = {};
console.log = console.log || function(){};
console.warn = console.warn || function(){};
console.error = console.error || function(){};
console.info = console.info || function(){};
console.debug = console.debug || function(){};
