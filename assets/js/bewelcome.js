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

window.bootstrap = bootstrap;


document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.toast').forEach(toastNode => {
        const toast = new window.bootstrap.Toast(toastNode);
        toast.show();
    });
});