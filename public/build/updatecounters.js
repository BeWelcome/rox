(window["webpackJsonp"] = window["webpackJsonp"] || []).push([["updatecounters"],{

/***/ "./assets/js/updateCounters.js":
/*!*************************************!*\
  !*** ./assets/js/updateCounters.js ***!
  \*************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

/* WEBPACK VAR INJECTION */(function($) {function updateCounts() {
  var messageCount = $('#messageCount');
  var mcount = messageCount.data('count');
  $.ajax({
    type: 'POST',
    url: '/count/messages/unread',
    data: {
      current: mcount
    },
    dataType: 'json',
    success: function success(data) {
      if (data.oldCount != data.newCount) {
        $('#messageCount').replaceWith(data.html);
        $('#messageCount').tooltip('show');
      } else {
        $('#messageCount').tooltip('hide');
      }
    }
  });
  var requestCount = $('#requestCount');
  var rcount = requestCount.data('count');
  $.ajax({
    type: 'POST',
    url: '/count/requests/unread',
    data: {
      current: rcount
    },
    dataType: 'json',
    success: function success(data) {
      if (data.oldCount != data.newCount) {
        $('#requestCount').replaceWith(data.html);
        $('#requestCount').tooltip('show');
      } else {
        $('#requestCount').tooltip('hide');
      }
    }
  });
}

var interval = setInterval(function () {
  updateCounts();
}, 120 * 1000);
$(function () {
  $('[data-toggle="tooltip"]').tooltip();
});
/* WEBPACK VAR INJECTION */}.call(this, __webpack_require__(/*! jquery */ "./node_modules/jquery/dist/jquery.js-exposed")))

/***/ })

},[["./assets/js/updateCounters.js","runtime","bewelcome"]]]);
//# sourceMappingURL=data:application/json;charset=utf-8;base64,eyJ2ZXJzaW9uIjozLCJzb3VyY2VzIjpbIndlYnBhY2s6Ly8vLi9hc3NldHMvanMvdXBkYXRlQ291bnRlcnMuanMiXSwibmFtZXMiOlsidXBkYXRlQ291bnRzIiwibWVzc2FnZUNvdW50IiwiJCIsIm1jb3VudCIsImRhdGEiLCJhamF4IiwidHlwZSIsInVybCIsImN1cnJlbnQiLCJkYXRhVHlwZSIsInN1Y2Nlc3MiLCJvbGRDb3VudCIsIm5ld0NvdW50IiwicmVwbGFjZVdpdGgiLCJodG1sIiwidG9vbHRpcCIsInJlcXVlc3RDb3VudCIsInJjb3VudCIsImludGVydmFsIiwic2V0SW50ZXJ2YWwiXSwibWFwcGluZ3MiOiI7Ozs7Ozs7OztBQUFBLGtEQUFTQSxZQUFULEdBQXdCO0FBQ3BCLE1BQUlDLFlBQVksR0FBR0MsQ0FBQyxDQUFDLGVBQUQsQ0FBcEI7QUFDQSxNQUFJQyxNQUFNLEdBQUdGLFlBQVksQ0FBQ0csSUFBYixDQUFrQixPQUFsQixDQUFiO0FBQ0FGLEdBQUMsQ0FBQ0csSUFBRixDQUFPO0FBQ0hDLFFBQUksRUFBRSxNQURIO0FBRUhDLE9BQUcsRUFBRSx3QkFGRjtBQUdISCxRQUFJLEVBQUU7QUFDRkksYUFBTyxFQUFFTDtBQURQLEtBSEg7QUFNSE0sWUFBUSxFQUFFLE1BTlA7QUFPSEMsV0FBTyxFQUFFLGlCQUFVTixJQUFWLEVBQWdCO0FBQ3JCLFVBQUlBLElBQUksQ0FBQ08sUUFBTCxJQUFpQlAsSUFBSSxDQUFDUSxRQUExQixFQUFvQztBQUNoQ1YsU0FBQyxDQUFDLGVBQUQsQ0FBRCxDQUFtQlcsV0FBbkIsQ0FBK0JULElBQUksQ0FBQ1UsSUFBcEM7QUFDQVosU0FBQyxDQUFDLGVBQUQsQ0FBRCxDQUFtQmEsT0FBbkIsQ0FBMkIsTUFBM0I7QUFDSCxPQUhELE1BR087QUFDSGIsU0FBQyxDQUFDLGVBQUQsQ0FBRCxDQUFtQmEsT0FBbkIsQ0FBMkIsTUFBM0I7QUFDSDtBQUNKO0FBZEUsR0FBUDtBQWVBLE1BQUlDLFlBQVksR0FBR2QsQ0FBQyxDQUFDLGVBQUQsQ0FBcEI7QUFDQSxNQUFJZSxNQUFNLEdBQUdELFlBQVksQ0FBQ1osSUFBYixDQUFrQixPQUFsQixDQUFiO0FBQ0FGLEdBQUMsQ0FBQ0csSUFBRixDQUFPO0FBQ0hDLFFBQUksRUFBRSxNQURIO0FBRUhDLE9BQUcsRUFBRSx3QkFGRjtBQUdISCxRQUFJLEVBQUU7QUFDRkksYUFBTyxFQUFFUztBQURQLEtBSEg7QUFNSFIsWUFBUSxFQUFFLE1BTlA7QUFPSEMsV0FBTyxFQUFFLGlCQUFVTixJQUFWLEVBQWdCO0FBQ3JCLFVBQUlBLElBQUksQ0FBQ08sUUFBTCxJQUFpQlAsSUFBSSxDQUFDUSxRQUExQixFQUFvQztBQUNoQ1YsU0FBQyxDQUFDLGVBQUQsQ0FBRCxDQUFtQlcsV0FBbkIsQ0FBK0JULElBQUksQ0FBQ1UsSUFBcEM7QUFDQVosU0FBQyxDQUFDLGVBQUQsQ0FBRCxDQUFtQmEsT0FBbkIsQ0FBMkIsTUFBM0I7QUFDSCxPQUhELE1BR087QUFDSGIsU0FBQyxDQUFDLGVBQUQsQ0FBRCxDQUFtQmEsT0FBbkIsQ0FBMkIsTUFBM0I7QUFDSDtBQUNKO0FBZEUsR0FBUDtBQWVIOztBQUVELElBQUlHLFFBQVEsR0FBR0MsV0FBVyxDQUFDLFlBQVk7QUFBRW5CLGNBQVk7QUFBSyxDQUFoQyxFQUFrQyxNQUFNLElBQXhDLENBQTFCO0FBRUFFLENBQUMsQ0FBQyxZQUFZO0FBQ1ZBLEdBQUMsQ0FBQyx5QkFBRCxDQUFELENBQTZCYSxPQUE3QjtBQUNILENBRkEsQ0FBRCxDIiwiZmlsZSI6InVwZGF0ZWNvdW50ZXJzLmpzIiwic291cmNlc0NvbnRlbnQiOlsiZnVuY3Rpb24gdXBkYXRlQ291bnRzKCkge1xyXG4gICAgbGV0IG1lc3NhZ2VDb3VudCA9ICQoJyNtZXNzYWdlQ291bnQnKTtcclxuICAgIGxldCBtY291bnQgPSBtZXNzYWdlQ291bnQuZGF0YSgnY291bnQnKTtcclxuICAgICQuYWpheCh7XHJcbiAgICAgICAgdHlwZTogJ1BPU1QnLFxyXG4gICAgICAgIHVybDogJy9jb3VudC9tZXNzYWdlcy91bnJlYWQnLFxyXG4gICAgICAgIGRhdGE6IHtcclxuICAgICAgICAgICAgY3VycmVudDogbWNvdW50XHJcbiAgICAgICAgfSxcclxuICAgICAgICBkYXRhVHlwZTogJ2pzb24nLFxyXG4gICAgICAgIHN1Y2Nlc3M6IGZ1bmN0aW9uIChkYXRhKSB7XHJcbiAgICAgICAgICAgIGlmIChkYXRhLm9sZENvdW50ICE9IGRhdGEubmV3Q291bnQpIHtcclxuICAgICAgICAgICAgICAgICQoJyNtZXNzYWdlQ291bnQnKS5yZXBsYWNlV2l0aChkYXRhLmh0bWwpO1xyXG4gICAgICAgICAgICAgICAgJCgnI21lc3NhZ2VDb3VudCcpLnRvb2x0aXAoJ3Nob3cnKTtcclxuICAgICAgICAgICAgfSBlbHNlIHtcclxuICAgICAgICAgICAgICAgICQoJyNtZXNzYWdlQ291bnQnKS50b29sdGlwKCdoaWRlJyk7XHJcbiAgICAgICAgICAgIH1cclxuICAgICAgICB9fSk7XHJcbiAgICBsZXQgcmVxdWVzdENvdW50ID0gJCgnI3JlcXVlc3RDb3VudCcpO1xyXG4gICAgbGV0IHJjb3VudCA9IHJlcXVlc3RDb3VudC5kYXRhKCdjb3VudCcpO1xyXG4gICAgJC5hamF4KHtcclxuICAgICAgICB0eXBlOiAnUE9TVCcsXHJcbiAgICAgICAgdXJsOiAnL2NvdW50L3JlcXVlc3RzL3VucmVhZCcsXHJcbiAgICAgICAgZGF0YToge1xyXG4gICAgICAgICAgICBjdXJyZW50OiByY291bnRcclxuICAgICAgICB9LFxyXG4gICAgICAgIGRhdGFUeXBlOiAnanNvbicsXHJcbiAgICAgICAgc3VjY2VzczogZnVuY3Rpb24gKGRhdGEpIHtcclxuICAgICAgICAgICAgaWYgKGRhdGEub2xkQ291bnQgIT0gZGF0YS5uZXdDb3VudCkge1xyXG4gICAgICAgICAgICAgICAgJCgnI3JlcXVlc3RDb3VudCcpLnJlcGxhY2VXaXRoKGRhdGEuaHRtbCk7XHJcbiAgICAgICAgICAgICAgICAkKCcjcmVxdWVzdENvdW50JykudG9vbHRpcCgnc2hvdycpO1xyXG4gICAgICAgICAgICB9IGVsc2Uge1xyXG4gICAgICAgICAgICAgICAgJCgnI3JlcXVlc3RDb3VudCcpLnRvb2x0aXAoJ2hpZGUnKTtcclxuICAgICAgICAgICAgfVxyXG4gICAgICAgIH19KTtcclxufVxyXG5cclxubGV0IGludGVydmFsID0gc2V0SW50ZXJ2YWwoZnVuY3Rpb24gKCkgeyB1cGRhdGVDb3VudHMoKTsgfSwgMTIwICogMTAwMCk7XHJcblxyXG4kKGZ1bmN0aW9uICgpIHtcclxuICAgICQoJ1tkYXRhLXRvZ2dsZT1cInRvb2x0aXBcIl0nKS50b29sdGlwKClcclxufSlcclxuIl0sInNvdXJjZVJvb3QiOiIifQ==