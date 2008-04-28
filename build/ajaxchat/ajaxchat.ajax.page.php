<?php
/** Ajax Chat
 * 
 * @package ajaxchat
 * @author lemon-head
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License (GPL)
 * @version $Id$
 */

class AjaxchatAjaxPage extends AbstractBasePage
{
    public function render()
    {
        $posts = $this->model->getMessagesInRoom(1, false);
        foreach ($posts as $post) {
            echo '<div>';
            echo $post->username . ': ' . $post->text;
            echo '</div>';
        }
    }
}


?>