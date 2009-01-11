<?php
/** 
 * Ajax Chat Controller
 * 
 * @package ajaxchat
 * @author lemon-head
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License (GPL)
 * @version $Id$
 */


class AjaxchatController extends RoxControllerBase
{

		protected	$BW_Right;
		protected	$BW_Flag;
		
    function __construct() {
				$this->BW_Right= MOD_right::get();
				$this->BW_Flag= MOD_flag::get();
			  parent::__construct();
		}

    public function index($args = false)
    {


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
				


//				echo "<br>", "MOD_flag::get()->HasFlag(\"NotAllowToWriteInChat\")=",				 MOD_flag::get()->HasFlag("NotAllowToWriteInChat"),"<br>";
				// is the user is logged, check if it is not a banned user
				if ((isset($_SESSION["IdMember"]) and ($this->BW_Flag->HasFlag("NotAllowToWriteInChat")))) {
					die ("You are not allowed to use the chat feature") ;
				}
				
        $request = $args->request;
        $model = new AjaxchatModel();
        
        switch($keyword = isset($request[1]) ? $request[1] : false) {
            case 'weeks':
                $page = new AjaxchatPage();
                $page->lookback_limit = $model->lookbackLimitWeeks();
                break;
            case 'months':
                $page = new AjaxchatPage();
                $page->lookback_limit = $model->lookbackLimitMonths();
                break;
            case 'forever':
                $page = new AjaxchatPage();
                $page->lookback_limit = $model->lookbackLimitForever();
                break;
            case 'days':
            default:
                $page = new AjaxchatPage();
                $page->lookback_limit = $model->lookbackLimitDays();
        }
        $page->model = $model;
        PVars::getObj('page')->output_done = true;
        return $page;
    }
    
    
    public function json($args, $json_object)
    {
        $model = new AjaxchatModel();
        $request = $args->request;
        $post = $args->post;
        
        if (!isset($_SESSION['IdMember'])) {
            echo 'not logged in!';
            $json_object->mustlogin = true;
        } else switch ($keyword = isset($request[2]) ? $request[2] : false) {
            case 'send':
                // TODO: implement
                $text = $post['chat_message_text'];
                $new_message = $model->createMessageInRoom(1, $_SESSION['IdMember'], $text);
                $new_message->text.= ' new';
                $json_object->messages = array($new_message);
                break;
            case 'update':
                $prev_message_id = is_numeric($args->request[3]) ? $args->request[3] : 0;
								$MessageActivity=$model->waitForMessagesInRoom(1, $prev_message_id); ;
                $json_object->messages = $MessageActivity->Messages;
                $json_object->ListOfMembers = $MessageActivity->ListOfMembers;
								$json_object->created2=$MessageActivity->created2 ;
                break;
            default:
                // ehm, not defined..
                // should not happen.
                echo __METHOD__;
        }
    }
    
    
    public function sendChatMessageCallback_disabled($args)
    {
        $post_args = $args->post;
        $model = new AjaxchatModel();
        $model->createMessageInRoom(1, $_SESSION['IdMember'], $post_args['chat_message_text']);
        PPHP::PExit();
    }
}
?>
