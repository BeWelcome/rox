$(function () {
    registerOnClickEvent();
});

// ------------------------------------------------------- //
// Multi Level dropdowns
// ------------------------------------------------------ //
function registerOnClickEvent() {
    $("ul.dropdown-menu [data-toggle='dropdown']").on("click", function (event) {

        $(this).siblings().toggleClass("show");

        if (!$(this).next().hasClass('show')) {
            $(this).parents('.dropdown-menu').first().find('.show').removeClass("show");
        }
        $(this).parents('li.nav-item.dropdown.show').on('hidden.bs.dropdown', function (e) {
            $('.dropdown-submenu .show').removeClass("show");
        });

    });

}
