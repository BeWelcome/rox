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
        
        if (!isset($_SESSION['IdMember'])) {
            echo 'not logged in!';
            $json_object->mustlogin = true;
        } else switch ($keyword = isset($request[2]) ? $request[2] : false) {
            case 'send':
                // TODO: implement
                $text = $args->post['chat_message_text'];
                $new_message = $model->createMessageInRoom(1, $_SESSION['IdMember'], $text);
                $json_object->messages = array($new_message);
                break;
            case 'update':
                $json_object->new_lookback_limit = $model->getNowTime('-0 0:0:1');
                $lookback_limit = isset($args->request[3]) ? $args->request[3].' .' : '000 .';
                for ($i=0; $i<7; ++$i) {
                    if (count($messages = $model->getMessagesInRoom(1, $lookback_limit)) >= 1) {
                        break;
                    }
                    usleep(200);
                }
                $json_object->messages = $messages;
                break;
            default:
                // ehm, not defined..
                // should not happen.
                echo __METHOD__;
        }
    }
    
    
    public function sendChatMessageCallback($args)
    {
        $post_args = $args->post;
        $model = new AjaxchatModel();
        $model->createMessageInRoom(1, $_SESSION['IdMember'], $post_args['chat_message_text']);
        PPHP::PExit();
    }
}
?>
