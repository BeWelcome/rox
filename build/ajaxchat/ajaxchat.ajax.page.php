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
        header('Content-type: application/json');
        echo json_encode($this->messages);
        /*
        foreach ($posts as $post) {
            echo '<div>';
            echo $post->username . ': ' . $post->text;
            echo '</div>';
        }
        */
    }
}


?>