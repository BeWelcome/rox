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
        $words = $this->words;
        $ww = $this->ww;
        $wwsilent = $this->wwsilent;
        $wwscript = $this->wwscript;
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