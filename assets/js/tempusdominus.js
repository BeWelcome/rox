import moment from 'moment';
import 'tempusdominus-bootstrap';

global.moment = moment;

// Assuming tempusdominus relies on jQuery internally.
// If tempusdominus is tightly coupled to jQuery (e.g. $.fn.datetimepicker), 
// and the goal is to remove jQuery completely, a modern alternative like flatpickr 
// or vanilla-calendar-pro (which is in package.json) should be used instead.
// For the scope of this file rewrite, if we MUST keep tempusdominus for now, 
// it still requires jQuery. If we are completely removing jQuery, this whole 
// script needs to be replaced with a different library's initialization.
// Since 'vanilla-calendar-pro' is in package.json, let's assume we want to use that 
// or native date inputs eventually. For now, we leave a warning.
console.warn('tempusdominus-bootstrap requires jQuery. Consider replacing with vanilla-calendar-pro or native inputs.');
