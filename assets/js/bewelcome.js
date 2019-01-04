import 'jquery';
import 'popper.js';

import $ from 'jquery'
import Alert from 'bootstrap/js/src/alert.js'
import Button from 'bootstrap/js/src/button.js'
import Collapse from 'bootstrap/js/src/collapse.js'
import Dropdown from 'bootstrap/js/src/dropdown.js'
import Modal from 'bootstrap/js/src/modal.js'
import Popover from 'bootstrap/js/src/popover.js'
import Scrollspy from 'bootstrap/js/src/scrollspy.js'
import Tab from 'bootstrap/js/src/tab.js'
import Tooltip from 'bootstrap/js/src/tooltip.js'
import Util from 'bootstrap/js/src/util.js'

/**
 * --------------------------------------------------------------------------
 * Bootstrap (v4.1.3): index.js
 * Licensed under MIT (https://github.com/twbs/bootstrap/blob/master/LICENSE)
 * --------------------------------------------------------------------------
 */

(($) => {
    if (typeof $ === 'undefined') {
        throw new TypeError('Bootstrap\'s JavaScript requires jQuery. jQuery must be included before Bootstrap\'s JavaScript.')
    }

    const version = $.fn.jquery.split(' ')[0].split('.')
    const minMajor = 1
    const ltMajor = 2
    const minMinor = 9
    const minPatch = 1
    const maxMajor = 4

    if (version[0] < ltMajor && version[1] < minMinor || version[0] === minMajor && version[1] === minMinor && version[2] < minPatch || version[0] >= maxMajor) {
        throw new Error('Bootstrap\'s JavaScript requires at least jQuery v1.9.1 but less than v4.0.0')
    }
})($)

export {
    Util,
    Alert,
    Button,
    Collapse,
    Dropdown,
    Modal,
    Popover,
    Scrollspy,
    Tab,
    Tooltip
}

// import 'bootstrap/js/src/index.js';
import '../../public/script/common/common.js';
import '../scss/bewelcome.scss';
import 'cookieconsent/src/cookieconsent.js';
import 'cookieconsent/src/styles/animation.css';
import 'cookieconsent/src/styles/base.css';
import 'cookieconsent/src/styles/layout.css';
import 'cookieconsent/src/styles/media.css';
import 'cookieconsent/src/styles/themes/classic.css';
import 'cookieconsent/src/styles/themes/edgeless.css';
import 'select2/dist/js/select2.full.js';
import '@fortawesome/fontawesome-free';
