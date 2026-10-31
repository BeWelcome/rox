import { Dropdown } from 'bootstrap';

const wideQuery = window.matchMedia('(min-width: 1200px)');

function syncTopbarSheetScrollLock() {
  const shouldLock = !wideQuery.matches && document.querySelector('#main_menu .dropdown-menu.show');
  document.body.classList.toggle('topbar-sheet-open', Boolean(shouldLock));
}

/** Close the top-bar dropdown whose menu contains this close control (profile, search, community full sheets). */
function hideDropdownFromCloseButton(closeButton) {
  const menu = closeButton.closest('ul.dropdown-menu');
  const toggle = menu ? menu.previousElementSibling : null;
  if (toggle && toggle.classList.contains('dropdown-toggle')) {
    Dropdown.getOrCreateInstance(toggle).hide();
  }
}

document.addEventListener('click', (event) => {
  const closeButton = event.target.closest('.member-menu__close');
  if (!closeButton) {
    return;
  }
  event.preventDefault();
  event.stopPropagation();
  hideDropdownFromCloseButton(closeButton);
});

/** Close any open #main_menu dropdown when crossing to xl (sheets → compact popovers). */
function closeMainMenuDropdownsIfWide() {
  if (!wideQuery.matches) {
    return;
  }
  document.querySelectorAll('#main_menu .nav-item.dropdown.show > .dropdown-toggle, #main_menu .nav-item.dropdown > .dropdown-toggle.show').forEach((toggle) => {
    Dropdown.getOrCreateInstance(toggle).hide();
  });
  syncTopbarSheetScrollLock();
}

// Bootstrap fires the dropdown events on the toggle; they bubble up to the document
['shown.bs.dropdown', 'hidden.bs.dropdown'].forEach((type) => {
  document.addEventListener(type, (event) => {
    if (event.target.closest('#main_menu .nav-item.dropdown')) {
      syncTopbarSheetScrollLock();
    }
  });
});

wideQuery.addEventListener('change', closeMainMenuDropdownsIfWide);
