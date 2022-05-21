$('[data-toggle="dropdown"]').bootstrapDropdownHover({
    clickBehavior: 'sticky',
    hideTimeout: 1000
});

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

const gapSize = 20;

var autocollapse_menu = function (resizing) {
    const hamburger = document.getElementById('hamburger');
    const hamburgerMenu = document.getElementById('hamburger_menu');
    const staticMenu = document.getElementById('static_menu');
    const collapsingMenu = document.getElementById('collapsing_menu');

    // if resizing move all menu items back into the collapsing menu and start from there
    // This also takes care of vanishing elements due to smaller viewports (like username and text on logo)
    let hiddenItems = hamburgerMenu.childNodes;
    if (hiddenItems.length !== 0) {
        while(hamburgerMenu.childNodes.length !== 0) {
            const menuItemToMove = hamburgerMenu.childNodes[0];
            collapsingMenu.appendChild(menuItemToMove);
            menuItemToMove.classList.remove('dropdown-submenu');
            menuItemToMove.classList.add('dropdown');
        }
    }

    let dimensionsStatic = staticMenu.getBoundingClientRect();
    let dimensionsCollapse = collapsingMenu.getBoundingClientRect();

    if (dimensionsStatic.left - dimensionsCollapse.right < gapSize) {
        hamburger.classList.remove('d-none');

        while (dimensionsStatic.left - dimensionsCollapse.right < gapSize) {
            //  add child to dropdown
            const menuItems = document.querySelectorAll('#collapsing_menu > li:not(:first-child)');
            const count = menuItems.length;
            const menuItemToMove = menuItems[count - 1];
            menuItemToMove.classList.remove('dropdown');
            menuItemToMove.classList.add('dropdown-submenu');
            menuItemToMove.classList.add('dropdown-menu-right');

            // insert in front of the first item in the hamburger menu
            hamburgerMenu.insertBefore(menuItemToMove, hamburgerMenu.firstChild);

            dimensionsStatic = staticMenu.getBoundingClientRect();
            dimensionsCollapse = collapsingMenu.getBoundingClientRect();
        }
    } else {
        hiddenItems = hamburgerMenu.childNodes;

        if (hiddenItems.length === 0) {
            hamburger.classList.add('d-none');
        }

        while (dimensionsStatic.left - dimensionsCollapse.right >= gapSize && hiddenItems.length !== 0) {
            const menuItems = hamburgerMenu.childNodes;
            const menuItemToMove = menuItems[0];
            menuItemToMove.classList.remove('dropdown-submenu');
            menuItemToMove.classList.remove('dropdown-menu-right');
            menuItemToMove.classList.add('dropdown');

            collapsingMenu.appendChild(menuItemToMove);

            dimensionsStatic = staticMenu.getBoundingClientRect();
            dimensionsCollapse = collapsingMenu.getBoundingClientRect();

            hiddenItems = document.querySelectorAll('#hamburger_menu > li');
        }

        if (dimensionsStatic.left - dimensionsCollapse.right < gapSize) {
            autocollapse_menu();
        }
    }
    registerOnClickEvent();
}

$(document).ready(function () {
    // when the page has loaded
    autocollapse_menu(false);

    // when the window is resized
    $(window).on('resize', function () {
        autocollapse_menu(true);
    });
});
