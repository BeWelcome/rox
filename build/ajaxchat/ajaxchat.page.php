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
        echo '
<h3>This is the AJAX Chat</h3>
'
        ;
        // echo '<p><pre>';
        // print_r($this->model->getMessagesInRoom(1, false));
        // echo '</pre></p>';
        
        echo '
Show backwards history: 
<a href="ajaxchat/days">days</a>,
<a href="ajaxchat/weeks">weeks</a>,
<a href="ajaxchat/months">months</a> or
<a href="ajaxchat/forever">forever</a> ?
'
        ;
        
        if (isset($_SESSION['Username'])) {
            
            $lookback_limit = $this->lookback_limit;
            require SCRIPT_BASE.'build/ajaxchat/template.php';
            
        } else {
            
            $loginWidget = $this->layoutkit->createWidget('LoginFormWidget');
            $loginWidget->render();
            
        }
    }
    
    public function leftSidebar()
    {
        $words = new MOD_words();
    ?>
        <p><?=$words->getFormatted('ChatPeopleOnlineInvite',MOD_online::get()->howManyMembersOnline()); ?></p>
    <?php
    }
}


?>