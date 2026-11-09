/*
 * The forum notifications tool (templates/admin/tools/forum.notifications.html.twig): members are suggested while the
 * username is typed (member/suggest); picking one fills the field.
 */
import {initMemberSuggest} from '../member/suggest';

const input = document.getElementById('forum_notifications_username');
const list = document.getElementById('forum_notifications_username_list');
if (input && list) {
    initMemberSuggest(input, list);
}
