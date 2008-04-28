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
        
        if (!isset($request[1])) {
            // normal chat page
            $page = new AjaxchatPage();
        } else switch($request[1]) {
            case 'ajax':
                // ajax xml
                $page = $this->createAjaxPage($args);
                break;
            default:
                $page = new AjaxchatPage();
        }
        $page->model = $model;
        PVars::getObj('page')->output_done = true;
        return $page;
    }
    
    
    protected function createAjaxPage($args)
    {
        return new AjaxchatAjaxPage();
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