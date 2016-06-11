<?php

/**
 * Messages controller
 *
 * @package hellouniverse
 * @author Andreas (lemon-head)
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License (GPL)
 * @version $Id$
 */
class MessagesController extends RoxControllerBase
{
    /**
     * decide which page to show.
     * This method is called from RoxFrontRouter
     * 
     */
    /**
     * decide which page to show
     * This method is called from RoxFrontRouter
     *
     * @param unknown_type $args request, post and get
     * @param unknown_type $memory_image message from redirect
     * @return unknown
     */
    public function index($args = false)
    {
        $request = $args->request;
        $model = new MessagesModel();

        // look if the user is logged in.
        if (!$this->_session->has( 'IdMember' )) {
            $page = new MessagesMustloginPage();
            $page->setRedirectURL(implode('/',$request));
        } else {
            // look at the request
            if (!isset($request[1])) {
                // simple, ugly page
                $page = new MessagesInboxPage();
            } else switch ($request[1]) {
                case 'received':
                case 'inbox':
                case 'sent':
                case 'outbox':
                case 'spam':
                case 'drafts':
                    switch ($request[1]) {
                        case 'received':
                        case 'inbox':
                            $page = new MessagesInboxPage();
                            break;
                        case 'sent':
                        case 'outbox':
                            $page = new MessagesSentboxPage();
                            break;
                        case 'spam':
                            $page = new MessagesSpamboxPage();
                            break;
                        case 'drafts':
                        default:
                            $page = new MessagesDraftsboxPage();
                    }
                    $page->active_page = $this->getPageNumber($request, 2);
                    break;
                case 'compose':
                    if (!($logged_member = $model->getLoggedInMember()))  { // We only request the Sender to be logged in
                        $page = new ContactNotPossible();
                    }
                    else if (!isset($request[2])) { // $request[2] should be the member who is going to receive the message
                        $page = new MessagesInboxPage();
                    } else if (!$member = $model->getMember($request[2])) {
                        $page = new MessagesInboxPage();
                    } else {
                        if ($member->Status <> 'Active' && $member->Status <> 'ActiveHidden' && $member->Status <> 'OutOfRemind') {
                            $page = new ComposeMessageProhibitedPage();
                        }   else {
                            $page = new ComposeMessagePage();
                            $page->sender = $model->getLoggedInMember();
                            $page->receiver = $member;
                        }
                    }
                    break;
                case 'with':
                    if (!isset($request[2])) {
                        $page = new MessagesInboxPage();
                    } else if (!$member = $model->getMember($request[2])) {
                        $page = new MessagesInboxPage();
                    } else {
                        $page = new MessagesContactboxPage();
                        $page->contact_member = $member;
                    }
                    $page->active_page = $this->getPageNumber($request, 3);
                    break;
                default:
                    if (!is_numeric($request[1])) {
                        // the request does not mean a message id
                        $page = new MessagesInboxPage();
                    } else if (!$message = $model->getMessage($request[1])) {
                        // no message with that id found
                        $page = new MessagesInboxPage();
                    } else {
                        if (empty($message->WhenFirstRead) || $message->WhenFirstRead == '0000-00-00 00:00:00') {
                            //Only mark as read when the receiver reads the message, not when the message is presented to the Sender with url /messages/77/sent
                            $MessagedReader = $model->getLoggedInMember();
                            if ($MessagedReader->getPKValue() == $message->IdReceiver) {
                                $model->markMessage($message->id);
                            }
                        }
                        if (!isset($request[2])) {
                            $page = new ReadMessagePage();
                        } else switch ($request[2]) { 
                            //message action
                            case 'delete':
                                $model->deleteMessage($request[1]);
                                $page = new MessagesInboxPage();
                                break;
                            case 'edit':
                                $page = new EditMessagePage();
                                $page->sender = $model->getLoggedInMember();
                                break;
                            case 'reply':
                                $page = new ReplyMessagePage();
                                $page->sender = $model->getLoggedInMember();
                                break;
                            case 'sent':
                                $page = new MessageSentPage();
                                break;
                            case 'spam':
                                $model->moveMessage($message->id,'Spam');
                                $model->updateSpamInfo($message->id, 'SpamSayMember', 'NotSpam', $message->SpamInfo);
                                $page = new MessagesInboxPage();
                                break;
                            case 'nospam':
                                $model->moveMessage($message->id,'Normal');
                                $model->updateSpamInfo($message->id, 'NotSpam', array('SpamSayMember', 'SpamSayChecker'), $message->SpamInfo);
                                $page = new MessagesInboxPage();
                                break;
                            case 'read':
                            default:
                                $page = new ReadMessagePage();
                            }
                            $page->message = $message;
                    }
                    
            }
            
            $page->setModel($model);
            if (isset($_GET['sort']))
                $page->sort_element = $_GET['sort'];
            if (isset($_GET['dir']))
                $page->sort_dir = $_GET['dir'];
        }
        // finally display the page.
        // the render() method will call other methods to render the page.
        return $page;
    }
    
    
    protected function getPageNumber($request, $req_index)
    { 
        if (!isset($request[$req_index])) {
            return 1;
        } else {
            $s = $request[$req_index];
            if (is_numeric($s)) {
                return $s;
            } else if (preg_match_all('/page([0-9]+)/i', $s, $regs)) {
                return $regs[1][0];
            } else if (preg_match_all('/p([0-9]+)/i', $s, $regs)) {
                return $regs[1][0];
            } else {
                // not the right format for page
                return 1;
            }
        }
    }
    
    
    /**
     * Enter description here...
     *
     * @param Object $args
     * @param Object $action 
     * @param Object $mem_redirect memory for the page after redirect
     * @param Object $mem_resend memory for resending the form
     * @return string relative request for redirect
     */
    public function sendMessageCallback($args, $action, $mem_redirect, $mem_resend)
    {
        $count = $action->count;
        $redirect_req = $action->redirect_req;
        
        $mem_redirect->post = $args->post;
        
        if (!APP_User::loggedIn()) {
            // not logged in.
            // the login form will be shown after the automatic redirect
            // after successful login, the message is recovered.
        } else if ($count < 0) {
            // session has expired while user was typing.
            $mem_redirect->expired = true;
        } else if ($mem_resend->already_sent_as) {
            // form has already been processed, with the message sent!
            // for a new message, the user needs a new form.
            // tell the redirected page which message has been already sent!
            $mem_redirect->already_sent_as = $mem_resend->already_sent_as;
        } else {
            if ($count > 0) {
                // form has been already processed $count times,
                // but the last time it was not successful.
                // so, we can send again
                // but tell the page how many times it had failed before
                $mem_redirect->fail_count = $count;
            } else {
                // first time to try sending the form
            }
            
            // now finally try to send it.
            $vars = $args->post;
            if (!empty($args->request[1]))
            {
                $vars['reply_to_id'] = $args->request[1];
            }
            $model = new MessagesModel();
            $result = new ReadOnlyObject($model->sendOrComplain($vars));
            
            if (count($result->problems) > 0) {
                $mem_redirect->problems = $result->problems;
            } else if (!is_numeric($result->message_id)) {
                // this should usually not happen.
                echo __METHOD__ . ' - message_id is not numeric: ' . $result->message_id;
            } else {
                // sending message was successful
                $mem_resend->already_sent_as = $result->message_id;
                return "messages/$result->message_id/sent";
            }
        }
        
        return implode('/', $args->request);
    }
    
    /**
     * callback for deleting/updating messages
     *
     * @param object $args
     * @param object $action
     * @param object $mem_redirect
     * @param object $mem_resend
     * @access public
     * @return string
     */
    public function mailBoxCallback($args, $action, $mem_redirect, $mem_resend)
    {
        $BW_Rights = new MOD_right();
        $layoutkit = 
        $count = $action->count;
        $return = $args->req;

        if (!APP_User::loggedIn() /*|| !$BW_Rights->hasRight('Admin') */)
        {
            return $return;
        }

        if ($count < 0)
        {
            $mem_redirect->expired = true;
            return $return;
        }

        if ($mem_resend->already_sent_as)
        {
            $mem_redirect->already_sent_as = $mem_resend->already_sent_as;
            return $return;
        }

        // now finally try to send it.
        $words = new MOD_words();
        $model = new MessagesModel();
        $post = $args->post;
        if (isset($post['message-mark']) && count($post['message-mark']) > 0 && isset($post['submit_multi']))
        {
            foreach ($post['message-mark'] as $message) {
                if (!$m = $model->getMessage($message)) {
                    $mem_redirect->problems = true;
                    return $return;
                }
                elseif ($post['submit_multi'] == 'delete')
                    $result = $model->deleteMessage($m->id);
                elseif ($post['submit_multi'] == 'markasread')
                    $result = $model->markMessage($m->id);
                elseif ($post['submit_multi'] == 'markasspam') {
                    $result = $model->moveMessage($m->id,'Spam');
                    $model->updateSpamInfo($m->id, 'SpamSayMember', 'NotSpam', $m->SpamInfo);
                } elseif ($post['submit_multi'] == 'nospam') {
                    $result = $model->moveMessage($m->id,'Normal');
                    $model->updateSpamInfo($m->id, 'NotSpam', array('SpamSayMember', 'SpamSayChecker'), $m->SpamInfo);
                } else {
                    $mem_redirect->problems = true;
                    return $return;
                }

            }
        } else {
            $result = false;
        }
        $mem_redirect->result = $result;
        return $return;
    }
}
