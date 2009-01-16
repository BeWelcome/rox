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
    private $_model;
		
    public function __construct($model) {
      $this->_model =& $model;
      parent::__construct();
    }
    

    protected function getPageTitle()
    {
        return 'Chat - BeWelcome';
    }
    
    
    protected function teaserHeadline()
    {
        echo 'Chat room #'.$this->_model->IdRoom;
    }
    
    
    protected function column_col3()
    {
        $words = $this->words;
        $ww = $this->ww;
        $wwsilent = $this->wwsilent;
        $wwscript = $this->wwscript;
        if (isset($_SESSION['Username'])) {
						$StrFeedBackAllowance=$this->_model->FeedBackAllowance() ;
            if ($StrFeedBackAllowance=="") { // If no message forbids to enter the room
            	$lookback_limit = $this->lookback_limit;
							$IdRoom=$this->_model->IdRoom ;
            	require SCRIPT_BASE.'build/ajaxchat/template.php';
						}
						else {
            	require SCRIPT_BASE.'build/ajaxchat/canotenter.php';
						}
            
        } else {
            
            $loginWidget = $this->layoutkit->createWidget('LoginFormWidget');
            $loginWidget->render();
            
        }
    }
    
    public function leftSidebar()
    {
        $words = new MOD_words();
//				<p><div id="IdDebugArea">0</div></p>
    ?>
				<p><div id="IdServerTime"></div></p>
        <p><?=$words->getFormatted('ChatPeopleOnlineHere','<span id="IdNbOnline">0</span>'); ?></p>
				<div id="PeopleInRoom"></div>
    <?php
    }
}


?>