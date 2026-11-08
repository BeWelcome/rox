import { Dropdown } from 'bootstrap';

const wideQuery = window.matchMedia('(min-width: 1200px)');

function syncTopbarSheetScrollLock() {
  const shouldLock = !wideQuery.matches && document.querySelector('#main_menu .dropdown-menu.show:not(.c-topbar-notices)');
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

// The member's menu: "Copy link" copies the profile address and says so for a moment; the menu
// stays open (the click doesn't reach Bootstrap's handler on the document, which closes it).
const shareButton = document.querySelector('#member_menu .js-member-share');
if (shareButton) {
  const copy = (text) => {
    if (navigator.clipboard && window.isSecureContext) {
      return navigator.clipboard.writeText(text);
    }
    // Without the clipboard API (plain http): the old way
    const field = document.createElement('textarea');
    field.value = text;
    field.setAttribute('readonly', '');
    field.style.position = 'fixed';
    field.style.opacity = '0';
    document.body.append(field);
    field.select();
    const done = document.execCommand('copy');
    field.remove();
    return done ? Promise.resolve() : Promise.reject();
  };
  shareButton.addEventListener('click', (event) => {
    event.preventDefault();
    event.stopPropagation();
    const label = shareButton.querySelector('.js-member-share-label');
    copy(shareButton.dataset.url).then(() => {
      if (!label) {
        return;
      }
      clearTimeout(shareButton.resetTimer);
      label.dataset.text = label.dataset.text || label.textContent;
      label.textContent = label.dataset.done || label.dataset.text;
      shareButton.classList.add('is-done');
      shareButton.resetTimer = setTimeout(() => {
        label.textContent = label.dataset.text;
        shareButton.classList.remove('is-done');
      }, 1800);
    }).catch(() => {});
  });
}
