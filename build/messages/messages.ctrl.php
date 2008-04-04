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
    public function index($args = false, $memory = false)
    {
        $request = $args->request;
        $model = new MessagesModel();
        
        // look if the user is logged in.
        if (!isset($_SESSION['IdMember'])) {
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
                    $page = new MessagesDraftsboxPage();
                    break;
                case 'compose':
                    if (!isset($request[2])) {
                        $page = new MessagesInboxPage();
                    } else if (!$member = $model->getMember($request[2])) {
                        $page = new MessagesInboxPage();
                    } else {
                        $page = new ComposeMessagePage();
                        $page->receiver = $member;
                        if ($memory) {
                            $page->setMemory($memory);
                            if (isset($extra_args['fieldvalues'])) {
                                $page->setFormValues($extra_args['fieldvalues']);
                            }
                            if (isset($extra_args['expired'])) {
                                $page->setExpired();
                            }
                            if (isset($extra_args['already_sent_as'])) {
                                $page->setAlreadySentAs($extra_args['already_sent_as']);
                            }
                            if (isset($extra_args['problems'])) {
                                $page->setProblems($extra_args['problems']);
                            }
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
                        $page->inject('contact_member', $member);
                    }
                    break;
                default:
                    if (!is_numeric($request[1])) {
                        // the request does not mean a message id
                        $page = new MessagesInboxPage();
                    } else if (!$message = $model->getMessage($request[1])) {
                        // no message with that id found
                        $page = new MessagesInboxPage();
                    } else {
                        if (!isset($request[2])) {
                            $page = new ReadMessagePage();
                        } else switch ($request[2]) {
                            case 'edit':
                                $page = new EditMessagePage();
                                break;
                            case 'reply':
                                $page = new ReplyMessagePage();
                                break;
                            case 'sent':
                                $page = new MessageSentPage();
                                break;
                            case 'read':
                            default:
                                $page = new ReadMessagePage();
                        }
                        $page->message = $message;
                    }
            }
            
            $page->setModel($model);
        }
        // finally display the page.
        // the render() method will call other methods to render the page.
        return $page;
    }
    
    
    
    
    public function sendMessageCallback($args, $count, $memory)
    {
        $memory->iwashere = 'iwashere';
        $memory->post = $args->post;
        
        if (!APP_User::loggedIn()) {
            // not logged in. show again after login!
        } else if ($count < 0) {
            // session has expired while user was typing.
            $memory->expired = true;
        } else if ($count > 0) {
            // form has been already processed!
            /*
            if ($memory->already_sent_as) {
                // message has already been sent
            } else {
                // maybe the last time there was something wrong.
                // TODO: a better error page!
            }
            */
        } else {
            // form is sent for the first time
            $model = new MessagesModel();
            $result = new ReadOnlyObject($model->sendOrComplain($args->post));
            
            if (count($result->problems) > 0) {
                $memory->problems = $result->problems;
            } else if (!is_numeric($result->message_id)) {
                echo __METHOD__ . ' - message_id is not numeric: ' . $result->message_id;
            } else {
                // sending message was successful
                $memory->already_sent_as = $result->message_id;
                return "messages/$result->message_id/sent";
            }
        }
        
        return implode('/', $args->request);
    }
}


?>