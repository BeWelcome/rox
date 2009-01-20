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
		
		private $template ;
    public function __construct($model,$mytemplate='') {
			$this->template=$mytemplate ;
      $this->_model =& $model;
      parent::__construct();
    }
    

    protected function getPageTitle()
    {
        return 'Chat - BeWelcome';
    }
    
    
    protected function teaserHeadline()
    {
				if (isset($this->_model->room->RoomTitle)) {
					echo $this->words->fTrad($this->_model->room->RoomTitle) ;
				}
				else {
        	echo "Room creation in progress" ;
				}
    }
    
    
    protected function column_col3()    {
        $words = $this->words;
        $ww = $this->ww;
        $wwsilent = $this->wwsilent;
        $wwscript = $this->wwscript;
        if (isset($_SESSION['Username'])) {
				
						if ($this->template=='createaroom') {
								$callbackId=15 ; // Arbitrary value, I don't know what it is used for (jyh)
            		require SCRIPT_BASE.'build/ajaxchat/createaroom.php';
						}
						elseif ($this->template=='invite') {
								$list=$this->_model->BuildPossibleGuestList() ;
            		require SCRIPT_BASE.'build/ajaxchat/inviteinroom.php';
						}
						elseif ($this->template=='docreateroom') {
            		$lookback_limit = $this->lookback_limit;
								$IdRoom=$this->_model->IdRoom ;
								if ($IdRoom<=1) {
									$StrFeedBackAllowance="Creation has failed You Must Give a Title/Name for a room" ;
            			require SCRIPT_BASE.'build/ajaxchat/canotenter.php';
								}
								else {
            			require SCRIPT_BASE.'build/ajaxchat/template.php';
								}
						}
						elseif ($this->template=='doinvite') {
            		$lookback_limit = $this->lookback_limit;
								$IdRoom=$this->_model->IdRoom ;
            		require SCRIPT_BASE.'build/ajaxchat/template.php';
						}
						else {
						
							$StrFeedBackAllowance=$this->_model->FeedBackAllowance() ;
            	if ($StrFeedBackAllowance=="") { // If no message forbids to enter the room
            		$lookback_limit = $this->lookback_limit;
								$IdRoom=$this->_model->IdRoom ;
            		require SCRIPT_BASE.'build/ajaxchat/template.php';
							}
							else {
            		require SCRIPT_BASE.'build/ajaxchat/canotenter.php';
							}
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

        <p><b><?=$words->getFormatted('ChatPeopleOnlineHere','<span id="IdNbOnline">init ...</span>'); ?></b>
				<span id="PeopleInRoom"></span></p>

        <p><b><?=$words->getFormatted('ChatPublicRooms'); ?></b>
				<span id="PublicRoomList"></span></p>

        <p><span id="PrivateRoomHeaderTitle"></span>
				<span id="PrivateRoomList"></span></p>
				<?php
					if (isset($_SESSION['IdMember'])) {
				?>
					<p><b>Actions</b><br /><a href="chat/createaroom"><?=$words->getFormatted('ChatCreateRooLink')?></a>
    <?php
					if (($this->_model->room->IdRoomOwner==$_SESSION['IdMember']) and ($this->_model->room->RoomType=='Private')) {
				?>
					<br /><a href="chat/invite/<?=$this->_model->room->id?>"> <?=$words->getFormatted('ChatInviteHere')?></a></p>
				<?php
					}
				}
    }
}


?>