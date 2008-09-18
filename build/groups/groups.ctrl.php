<?php

/**
 * This controller is called when the request is 'groups/...'
 */
class GroupsController extends RoxControllerBase   
{

    public function __construct()
    {
        parent::__construct();
        $this->_model = new GroupsModel();
    }
    
    public function __destruct()
    {
        unset($this->_model);
    }
	
    public function index()
    {
        $request = PRequest::get()->request;
        $model = new GroupsModel();
        if (!isset($request[1])) {
            $page = new GroupsOverviewPage();
        } else if (is_numeric(
            $group_id = array_shift(explode('-', $request[1]))
        )) {
            // by default, the $request[1] is the group id + name
            if (!$group = $model->findGroup($group_id)) {
                // group does not exist. redirect to groups overview page or search
                $this->_redirect('groups');
            } else {
                $model->setGroupVisit($group_id);
                $page = $this->_getGroupPage($group, $request);
            }
        } else switch ($request[1]) {
            case 'search':
                $page = new GroupsSearchPage();
                $page->setSearchQuery($search_query);
                break;
            case 'new':
                if (isset($_SESSION['IdMember'])) {
                    $page = new GroupsCreationPage();
                }
                else { 
                    $page = new GroupsOverviewPage();
                }
                break;
            default:
                $this->_redirect('groups');
        }
        $page->setModel($model);
        return $page;
    }
    
    
    private function _getGroupPage($group, $request)
    {
        if (!isset($request[2])) {
            $page = new GroupStartPage();
        } else switch ($request[2]) {
                // which group subpage is requested?
            case 'join':
                if (!isset($request[3])) {
                    $page = new GroupJoinPage();
                } else switch($request[3]) {
                    case 'yes':
                        $user_id = $_SESSION['IdMember'];
                        $group->memberJoin($user_id);
                        $page = new GroupStartPage();
                        // TODO: set a message for 'group not joined'
                        break;
                    case 'no':
                        $page = new GroupStartPage();
                        // TODO: set a message for 'group not joined'
                    default:
                        $this->_redirect('groups/'.$request[1].'/join');
                }
                break;
            case 'leave':
                if (!isset($request[3])) {
                    $page = new GroupLeavePage();
                } else switch($request[3]) {
                    case 'yes':
                        $user_id = $_SESSION['IdMember'];
                        $group->memberLeave($user_id);
                        $page = new GroupStartPage();
                        // TODO: set a message for 'group not joined'
                        break;
                    case 'no':
                        $page = new GroupStartPage();
                        // TODO: set a message for 'group not joined'
                    default:
                        $this->_redirect('groups/'.$request[1].'/leave');
                }
                break;
            case 'members':
                $page = new GroupMembersPage();
                break;
            default:
                $page = new GroupStartPage();
        }
        $page->setGroup($group);
        return $page;
    }
    
    private function _redirect($rel_url)
    {
        /*
        echo PVars::getObj('env')->baseuri.'<br>';
        echo PVars::getObj('env')->baseuri.implode('/', PRequest::get()->request).'<br>';
        echo PVars::getObj('env')->baseuri.$rel_url;
        */
        header('Location: '.PVars::getObj('env')->baseuri.$rel_url);
        PPHP::PExit();
    }
    
   /**
     * Callback function for createGroup page
     *
     * @param Object $args
     * @param Object $action 
     * @param Object $mem_redirect memory for the page after redirect
     * @param Object $mem_resend memory for resending the form
     * @return string relative request for redirect
     */
    public function createGroupCallback($args, $action, $mem_redirect, $mem_resend)
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
            $model = new Group('');
			$result = new ReadOnlyObject($model->createGroupSendOrComplain($args->post));
            //$result = new ReadOnlyObject($group->memberJoin($args->post));            
            if (count($result->problems) > 0) {
                $mem_redirect->problems = $result->problems;
            } else {
                // sending message was successful
                $mem_resend->already_sent_as = $result->message_id;
                return "groups/sent";
            }
        }
        
        return implode('/', $args->request);
    }
}


?>