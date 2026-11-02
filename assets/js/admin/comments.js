/*
 * The comments tool of the volunteers (templates/admin/comment/comments.html.twig): the tabs scroll sideways on
 * phones, and Delete asks first (a MicroModal dialog, opened by data-micromodal-trigger).
 */
import MicroModal from 'micromodal';
import {initTabsScrollers} from '../tabs-scroller';

initTabsScrollers();
MicroModal.init({ awaitCloseAnimation: true });
