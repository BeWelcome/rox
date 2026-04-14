import $ from 'jquery';

/** Close the top-bar dropdown whose menu contains this close control (profile, search, community full sheets). */
function hideDropdownFromCloseButton(closeButton) {
  const $toggle = $(closeButton).closest('ul.dropdown-menu').prev('.dropdown-toggle');
  if ($toggle.length) {
    $toggle.dropdown('hide');
  }
}

$(document).on('click', '.member-menu__close', function (e) {
  e.preventDefault();
  e.stopPropagation();
  hideDropdownFromCloseButton(this);
});

/** Close any open #main_menu dropdown when crossing to xl (sheets → compact popovers). */
const wideQuery = window.matchMedia('(min-width: 1200px)');
function closeMainMenuDropdownsIfWide() {
  if (!wideQuery.matches) {
    return;
  }
  document.querySelectorAll('#main_menu .nav-item.dropdown.show > .dropdown-toggle').forEach((el) => {
    $(el).dropdown('hide');
  });
}
if (typeof wideQuery.addEventListener === 'function') {
  wideQuery.addEventListener('change', closeMainMenuDropdownsIfWide);
} else if (typeof wideQuery.addListener === 'function') {
  wideQuery.addListener(closeMainMenuDropdownsIfWide);
}
