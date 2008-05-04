<?php
/** Ajax Chat
 * 
 * @package ajaxchat
 * @author lemon-head
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License (GPL)
 * @version $Id$
 */

class AjaxchatPage extends PageWithActiveSkin
{
    protected function getPageTitle()
    {
        return 'Chat - BeWelcome';
    }
    
    
    protected function teaserHeadline()
    {
        echo 'Ajax Chat';
    }
    
    
    protected function column_col3()
    {
        echo '<h3>This is the AJAX Chat</h3>';
        // echo '<p><pre>';
        // print_r($this->model->getMessagesInRoom(1, false));
        // echo '</pre></p>';
        
        if (isset($_SESSION['Username'])) {
            
            $lookback_limit = $this->lookback_limit;
            require SCRIPT_BASE.'build/ajaxchat/template.php';
            
        } else {
            
            $loginWidget = $this->layoutkit->createWidget('LoginFormWidget');
            $loginWidget->render();
            
        }
    }
}


?>