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
    public function index($args = false)
    {
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
