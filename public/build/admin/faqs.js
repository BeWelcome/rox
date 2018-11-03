(window["webpackJsonp"] = window["webpackJsonp"] || []).push([["admin/faqs"],{

/***/ "./assets/js/admin/faqs.js":
/*!*********************************!*\
  !*** ./assets/js/admin/faqs.js ***!
  \*********************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

/* WEBPACK VAR INJECTION */(function($) {$(function () {
  $("#faqs").sortable({
    axis: 'y',
    update: function update(event, ui) {
      var data = $(this).sortable("serialize", {
        key: "faq"
      });
      $("#form_sortOrder").val(data);
    }
  });
});
/* WEBPACK VAR INJECTION */}.call(this, __webpack_require__(/*! jquery */ "./node_modules/jquery/dist/jquery.js-exposed")))

/***/ })

},[["./assets/js/admin/faqs.js","runtime","bewelcome"]]]);
//# sourceMappingURL=data:application/json;charset=utf-8;base64,eyJ2ZXJzaW9uIjozLCJzb3VyY2VzIjpbIndlYnBhY2s6Ly8vLi9hc3NldHMvanMvYWRtaW4vZmFxcy5qcyJdLCJuYW1lcyI6WyIkIiwic29ydGFibGUiLCJheGlzIiwidXBkYXRlIiwiZXZlbnQiLCJ1aSIsImRhdGEiLCJrZXkiLCJ2YWwiXSwibWFwcGluZ3MiOiI7Ozs7Ozs7OztBQUFBQSwwQ0FBQyxDQUFFLFlBQVc7QUFDVkEsR0FBQyxDQUFFLE9BQUYsQ0FBRCxDQUFhQyxRQUFiLENBQXNCO0FBQ2xCQyxRQUFJLEVBQUUsR0FEWTtBQUVsQkMsVUFBTSxFQUFFLGdCQUFVQyxLQUFWLEVBQWlCQyxFQUFqQixFQUFxQjtBQUN6QixVQUFJQyxJQUFJLEdBQUdOLENBQUMsQ0FBQyxJQUFELENBQUQsQ0FBUUMsUUFBUixDQUFrQixXQUFsQixFQUErQjtBQUFFTSxXQUFHLEVBQUc7QUFBUixPQUEvQixDQUFYO0FBQ0FQLE9BQUMsQ0FBQyxpQkFBRCxDQUFELENBQXFCUSxHQUFyQixDQUF5QkYsSUFBekI7QUFDSDtBQUxpQixHQUF0QjtBQU9ILENBUkEsQ0FBRCxDIiwiZmlsZSI6ImFkbWluL2ZhcXMuanMiLCJzb3VyY2VzQ29udGVudCI6WyIkKCBmdW5jdGlvbigpIHtcclxuICAgICQoIFwiI2ZhcXNcIiApLnNvcnRhYmxlKHtcclxuICAgICAgICBheGlzOiAneScsXHJcbiAgICAgICAgdXBkYXRlOiBmdW5jdGlvbiAoZXZlbnQsIHVpKSB7XHJcbiAgICAgICAgICAgIHZhciBkYXRhID0gJCh0aGlzKS5zb3J0YWJsZSggXCJzZXJpYWxpemVcIiwgeyBrZXkgOiBcImZhcVwiIH0gKTtcclxuICAgICAgICAgICAgJChcIiNmb3JtX3NvcnRPcmRlclwiKS52YWwoZGF0YSk7XHJcbiAgICAgICAgfVxyXG4gICAgfSk7XHJcbn0pOyJdLCJzb3VyY2VSb290IjoiIn0=