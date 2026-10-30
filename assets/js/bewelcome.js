import * as bootstrap from 'bootstrap'

import '../../public/script/common/common.js';
import 'cookieconsent/src/cookieconsent.js';

import '../scss/bewelcome.scss';
import 'cookieconsent/src/styles/animation.css';
import 'cookieconsent/src/styles/base.css';
import 'cookieconsent/src/styles/layout.css';
import 'cookieconsent/src/styles/media.css';
import 'cookieconsent/src/styles/themes/classic.css';
import 'cookieconsent/src/styles/themes/edgeless.css';
import '../scss/cookie-consent.scss';
import '@fortawesome/fontawesome-free/js/all.js';
import './collapsemenu.js';
import './member-menu-dropdown.js';
import './tom-select.js';
import './confirm.js';
import './footer.js';

window.bootstrap = bootstrap;


document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.toast').forEach(toastNode => {
        const toast = new window.bootstrap.Toast(toastNode);
        toast.show();
    });
});
// A country page (places/country.html.twig): a click on a member opens their preview in a drawer,
// as on the search. Loaded only there.
if (document.querySelector('.js-places-preview')) {
    import('./search/preview').then(({initMemberPreview}) => initMemberPreview());
}
// A member's preview alone in the side panel (from a trip): Read more and the pictures
const previewPanel = document.querySelector('.js-preview-panel');
if (previewPanel) {
    import('./search/preview').then(({initPreviewContent}) => initPreviewContent(previewPanel));
}
