<?php
/** 
 * Ajax Chat Controller
 * 
 * @package ajaxchat
 * @author lemon-head
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License (GPL)
 * @version $Id$
 */


class AjaxchatController extends RoxControllerBase {

	protected	$BW_Right;
	protected	$BW_Flag;
		
    function __construct() {
		$this->BW_Right= MOD_right::get();
		$this->BW_Flag= MOD_flag::get();
		parent::__construct();
	}

    public function index($args = false)   {


		// First check if the feature is closed
		if ($_SESSION["Param"]->FeatureAjaxChatClosed!='No')  {
			 if (isset($_SESSION["Username"])) { // If it is a logged member we are going to see if he is not in the autorisation list
				$ss=$_SESSION["Param"]->AjaxChatSpecialAllowedList ;
				$ss=str_replace(",",";",$ss) ; // We never now may be the wrong separator was used in the param table
				$ss=str_replace(" ",";",$ss) ; // We never now may be the wrong separator was used in the param table
				$SpecialList=explode(";",$ss) ;
				if (!in_array($_SESSION["Username"],$SpecialList)) { // If teh username is not inside the list of exception allowed 
					die ("Sorry Chat is closed for the moment") ; // to do : provide a view here for a nice message
					PPHP::PExit();
					break ;
				}
				else {
					echo " Warning chat is closed except for ",$ss ;
				}
			} 
			else {
  				die ("Sorry Chat is closed for the moment") ; // to do : provide a view here for a nice message
				PPHP::PExit();
				break ;
			}
		} // end of test "if feature is closed" 
				


//		echo "<br>", "MOD_flag::get()->HasFlag(\"NotAllowToWriteInChat\")=",				 MOD_flag::get()->HasFlag("NotAllowToWriteInChat"),"<br>";
		// is the user is logged, check if it is not a banned user
		if ((isset($_SESSION["IdMember"]) and ($this->BW_Flag->HasFlag("NotAllowToWriteInChat")))) {
			die ("You are not allowed to use the chat feature") ;
		}
				
        $request = $args->request;
		$IdRoom=isset($arg->post['IdRoom']) ? $arg->post['IdRoom'] : 1 ;
        $model = new AjaxchatModel($IdRoom);
        
        switch($keyword = isset($request[1]) ? $request[1] : false) {
            case 'docreateroom':
				$Title=$args->post['RoomTitle'] ;
				$Description=$args->post['RoomDescription'] ;
        		$model = new AjaxchatModel(0,$Title,$Description); // Recreate the model with a new room
                $page = new AjaxchatPage($model);
                break;
            case 'createaroom':
                $page = new AjaxchatPage($model,'createaroom');
                break;
            case 'deleteroom':
				$IdRoom=isset($request[2]) ? $request[2] : 0 ;
				$model->SetIdRoom($IdRoom) ;
				$model->DeleteRoom($IdRoom) ;
				$model->SetIdRoom(1) ; // Back to the main room
                $page = new AjaxchatPage($model);
                break;
            case 'cleanroom':
				$IdRoom=isset($request[2]) ? $request[2] : 0 ;
				$model->SetIdRoom($IdRoom) ;
				if ((($model->room->IdRoomOwner==$_SESSION['IdMember']) or ($model->IsAllowed("CleanRoom") ) )and ($model->room->RoomType=='Private')) {
					$model->CleanRoom() ;
					$page = new AjaxchatPage($model);
				}
				else {
					die("You are not allowed to invite in room #".$IdRoom) ;
				}
                break;
            case 'remove':
				$Username=isset($request[2]) ? $request[2] : "" ;
				$IdRoom=isset($request[3]) ? $request[3] : 0 ;
				$model->SetIdRoom($IdRoom) ;
				$model->RemovePeopleFromRoom($IdRoom,$Username) ;
                $page = new AjaxchatPage($model);
                break;
            case 'doinvite':
				$Username=isset($request[2]) ? $request[2] : "" ;
				$IdRoom=isset($request[3]) ? $request[3] : 0 ;
				$model->SetIdRoom($IdRoom) ;
				if ((($model->room->IdRoomOwner==$_SESSION['IdMember']) or ($model->IsAllowed("InviteAndKick") ) )and ($model->room->RoomType=='Private')) {
					$model->AddInRoom($Username) ;
					$page = new AjaxchatPage($model);
				}
				else {
					die("You are not allowed to invite in room #".$IdRoom) ;
				}
                break;
            case 'invite':
				$IdRoom=isset($request[2]) ? $request[2] : 0 ;
				$model->SetIdRoom($IdRoom) ;
				if ((($model->room->IdRoomOwner==$_SESSION['IdMember']) or ($model->IsAllowed("InviteAndKick") ) )and ($model->room->RoomType=='Private')) {
					$page = new AjaxchatPage($model,'invite');
				}
				else {
					die("You are not allowed to invite in room #".$IdRoom) ;
				}
                break;
            case 'weeks':
				$IdRoom=isset($request[2]) ? $request[2] : 1 ;
                $page = new AjaxchatPage($model);
				$model->SetIdRoom($IdRoom) ;
                $page->lookback_limit = $model->lookbackLimitWeeks();
                break;
            case 'months':
				$IdRoom=isset($request[2]) ? $request[2] : 1 ;
                $page = new AjaxchatPage($model);
				$model->SetIdRoom($IdRoom) ;
                $page->lookback_limit = $model->lookbackLimitMonths();
                break;
            case 'forever':
				$IdRoom=isset($request[2]) ? $request[2] : 1 ;
                $page = new AjaxchatPage($model);
				$model->SetIdRoom($IdRoom) ;
                $page->lookback_limit = $model->lookbackLimitForever();
                break;
            case 'room':
				$IdRoom=isset($request[2]) ? $request[2] : 1 ;
				$model->SetIdRoom($IdRoom) ;
                $page = new AjaxchatPage($model);
                $page->lookback_limit = $model->lookbackLimitDays();
				break ;
            case 'days':
				$IdRoom=isset($request[2]) ? $request[2] : 1 ;
				$model->SetIdRoom($IdRoom) ;
                $page = new AjaxchatPage($model);
                $page->lookback_limit = $model->lookbackLimitDays();
            default:
				$model->SetIdRoom(1) ;
                $page = new AjaxchatPage($model);
                $page->lookback_limit = $model->lookbackLimitHours();
//				$model->waitForMessagesInRoom(500); // This line is just her to see error result if any, it should be commentated
				break ;
        }
        $page->model = $model;
        PVars::getObj('page')->output_done = true;
        return $page;
    }
    
    
    public function json($args, $json_object)    {
        $post = $args->post;
        $request = $args->request;
        
        if (!isset($_SESSION['IdMember'])) {
            echo 'not logged in!';
            $json_object->mustlogin = true;
        } else switch ($keyword = isset($request[2]) ? $request[2] : false) {
            case 'send':
                $IdRoom = is_numeric($request[3]) ? $request[3] : 1;
        		$model = new AjaxchatModel($IdRoom);
				$words=$model->words ;
                // TODO: implement
                $text = $post['chat_message_text'];
                $new_message = $model->createMessageInRoom($_SESSION['IdMember'], $text);
                $new_message->text.= ' new';
				if (empty($new_message->Error)) {
					$json_object->messages = array($new_message);
				}
				else {
					$json_object->alerts=array($new_message->Error) ;
					$json_object->messages = array();
				}
                break;
            case 'update':
                $prev_message_id = is_numeric($request[3]) ? $request[3] : 0;
                $IdRoom = is_numeric($request[4]) ? $request[4] : 1;
        		$model = new AjaxchatModel($IdRoom);
				$words=$model->words ;
				$MessageActivity=$model->waitForMessagesInRoom($prev_message_id); ;
				$json_object->alerts=array($MessageActivity->alerts) ;
                $json_object->messages = $MessageActivity->Messages;
                $json_object->ListOfMembers = $MessageActivity->ListOfMembers;
				$json_object->ServerTime=$MessageActivity->ServerTime ;
				$json_object->ListOfPublicLink=$MessageActivity->ListOfPublicLink ;
				$json_object->ListOfPrivateLink=$MessageActivity->ListOfPrivateLink ;
				$json_object->NewIntervall=$MessageActivity->NewIntervall ;
				$json_object->AjaxChatDebuLevel=$_SESSION['Param']->AjaxChatDebuLevel ;
								
				if (!isset($_SESSION['WhoIsOnlineCount'])) {
					$json_object->IdLoggedMembers='init missing' ;
				}
				else {
					$json_object->IdLoggedMembers=$words->getBuffered('NbMembersOnline', $_SESSION['WhoIsOnlineCount']) ;
				}
                break;
            default:
                // ehm, not defined..
                // should not happen.
                echo __METHOD__;
        }
    }
    
    public function sendChatMessageCallback_disabled($args)    {
        $post_args = $args->post;
        $IdRoom = is_numeric($arg->post['IdRoom']) ? $arg->post['IdRoom'] : 1;
        $model = new AjaxchatModel($IdRoom);
//        $model->createMessageInRoom($this->model->IdRoom, $_SESSION['IdMember'], $post_args['chat_message_text']);
        $model->createMessageInRoom( $_SESSION['IdMember'], $post_args['chat_message_text']);
        PPHP::PExit();
    }
}
?>
