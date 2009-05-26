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
	
    public function index($args)
    {
        $request = PRequest::get()->request;
        if (!$this->args_vars)
        {
            $this->args_vars = $args;
        }
        if (!isset($request[1]))
        {
            $page = new GroupsOverviewPage();
            $page->model = $this->_model;
            $page->member = $this->_model->getLoggedInMember();
            $page->featured_groups = $this->_model->findAllGroups(0,5);
            $page->my_groups = $this->_model->getMyGroups();
            return $page;
        }
        
        if (is_numeric($group_id = array_shift(explode('-', $request[1]))))
        {
            // by default, the $request[1] is the group id + name
            if (!$group = $this->_model->findGroup($group_id))
            {
                // group does not exist. redirect to groups overview page or search
                $this->_redirect('groups');
            }
            elseif ($group->Type == 'NeedInvitation' && !$this->_model->getLoggedInMember())
            {
                $this->_redirect('groups');
            }
            else
            {
                $this->_model->setGroupVisit($group_id);
                $page = $this->_getGroupPage($group, $request);
            }
        }
        else switch ($request[1])
        {
            // TODO: create better, more modular handling of images
            case 'thumbimg':
                PRequest::ignoreCurrentRequest();
                if (empty($request[2]))
                    PPHP::PExit();
                $this->_model->thumbImg($request[2]);
                break;
                
            case 'realimg':
                PRequest::ignoreCurrentRequest();
                if (empty($request[2]))
                    PPHP::PExit();
                $this->_model->realImg($request[2]);
                break;

            case 'search':
                $terms = ((isset($_GET['GroupsSearchInput'])) ? $_GET['GroupsSearchInput'] : '');
                $resultpage = ((isset($_GET['Page'])) ? $_GET['Page'] : 0);
                $order = ((isset($_GET['Order'])) ? $_GET['Order'] : 'nameasc');
                $page = new GroupsSearchPage();
                $page->search_result = $this->_model->findGroups($terms, $resultpage, $order);
                $page->result_page = $resultpage;
                $page->result_order = $order;
                $page->search_terms = $terms;
                break;
            case 'new':
                if (isset($_SESSION['IdMember']))
                {
                    $page = new GroupsCreationPage();
                }
                else
                {
                    $this->_redirect('#');
                }
                break;
            case 'mygroups':
                if (isset($_SESSION['IdMember']))
                {
                    $page = new GroupsMyGroupsPage();
                    $page->search_result = $this->_model->getMyGroups();
                }
                else
                {
                    $this->_redirect('#');
                }
                break;
            case 'featured':
                $page = new GroupsFeaturedPage();
                $order = ((isset($_GET['Order'])) ? $_GET['Order'] : 'nameasc');
                $page->search_result = $this->_model->findGroups(null,null,$order);
                $page->result_order = $order;
                break;
            default:
                $this->_redirect('groups');
        }
        $page->member = $this->_model->getLoggedInMember();
        $page->model = $this->_model;
        return $page;
    }



    /**
     * Handle various group actions - based on request[2]
     *
     * @param object $group - group entity
     * @param string $request - action to carry out
     * @access private
     * @return object $page
     */
    private function _getGroupPage($group, $request)
    {
        if (empty($request[2]) || !($method = $request[2]) || !method_exists($this, $method))
        {
            $page = new GroupStartPage();
            $page->group = $group;
            return $page;
        }

        $page = call_user_func(array($this, $method), $group, $request);
        $page->group = $group;
        return $page;
    }

//{{{ group action functions, called from getGroupPage
    /**
     * displays a page allowing a group owner to invite members to the group
     *
     * @access public
     * @return object
     */
    public function invitemembers($group, $request)
    {
        if (empty($this->args_vars->get['username']) || ($group->Status != 'Public' && $this->_model->getLoggedInMember()->getPKValue() != $group->getGroupOwner()->getPkValue()))
        {
            $this->_redirect('groups');
        }
        $members = $this->_model->findMembersByName($group, $this->args_vars->get['username']);
        $page = new GroupInvitePage;
        $page->search_result = $members;
        return $page;
    }

    /**
     * no-js method for inviting a member
     *
     * @access public
     * @return object
     */
    public function invitemember($group, $request)
    {
        if (empty($request[3]) || ($group->Status != 'Public' && $this->_model->getLoggedInMember()->getPKValue() != $group->getGroupOwner()->getPkValue()))
        {
            $this->redirect('groups');
        }
        $page = new GroupStartPage;
        if ($this->_model->inviteMember($group, $request[3]))
        {
            $this->_model->sendInvitation($group, $request[3], $this->_model->getLoggedInMember());
            $page->memberinvited = true;
        }
        else
        {
            $page->memberinvited = false;
        }
        return $page;
    }

    /**
     * ajax method for searching for members to send group invites to
     *
     * @param object $group - group entity
     * @param string $request - action to carry out
     * @access private
     */
    private function membersearchajax($group, $request)
    {
        header('Content-Type: text/plain; encoding=utf-8');
        if (isset($request[3]))
        {
            $members = $this->_model->findMembersByName($group, $request[3]);
            $output = array();
            foreach ($members as $member)
            {
                $output[$member->Username] = $member->getPKValue();
            }
            echo json_encode($output);
            exit;
        }
        else
        {
            header('Status: 500 Fudged it');
            exit;
        }
    }

    /**
     * ajax method for inviting a member
     *
     * @param object $group - group entity
     * @param string $request - action to carry out
     * @access private
     */
    private function invitememberajax($group, $request)
    {
        header('Content-Type: text/plain; encoding=utf-8');
        if (isset($request[3]))
        {
            if ($this->_model->inviteMember($group, $request[3]))
            {
                $this->_model->sendInvitation($group, $request[3], $this->_model->getLoggedInMember());
                echo "success";
            }
            else
            {
                echo "fail";
            }
            exit;
        }
        else
        {
            header('Status: 500 Fudged it');
            exit;
        }
    }

    /**
     * called when a member accepts an invitation to join a group
     *
     * @param object $group - group entity
     * @param string $request - action to carry out
     * @access private
     */
    private function acceptinvitation($group, $request)
    {
        if (!$this->_model->getLoggedInMember() || !$this->_model->memberAcceptedInvitation($group, $request[3]))
        {
            $this->_redirect('groups/');
        }
        $page = new GroupStartPage();
        $page->setMessage('GroupsJoinSuccess');
        return $page;
    }

    /**
     * called when a member accepts an invitation to join a group
     *
     * @param object $group - group entity
     * @param string $request - action to carry out
     * @access private
     */
    private function declineinvitation($group, $request)
    {
        if ($this->_model->getLoggedInMember())
        {
            $this->_model->memberDeclinedInvitation($group, $request[3]);
        }
        $this->_redirect('groups/');
    }

    /**
     * bans a member from a group, so they can't join up again
     *
     * @param object $group - group entity
     * @param string $request - action to carry out
     * @access private
     * @return object $page
     */
    private function banmember($group, $request)
    {
        if (!$this->_model->getLoggedInMember() || !$this->_model->canAccessGroupAdmin($group) || empty($request[3]))
        {
            $this->_redirect('groups/');
        }

        $this->_model->banGroupMember($group, $request[3], true);

        return new GroupStartPage();
    }

    /**
     * kicks a member from a group, by just taking them out of the group
     *
     * @param object $group - group entity
     * @param string $request - action to carry out
     * @access private
     * @return object $page
     */
    private function kickmember($group, $request)
    {
        if (!$this->_model->getLoggedInMember() || !$this->_model->canAccessGroupAdmin($group) || empty($request[3]))
        {
            $this->_redirect('groups/');
        }

        $this->_model->banGroupMember($group, $request[3], false);

        return new GroupStartPage();
    }

    /**
     * accepts a member to group
     *
     * @param object $group - group entity
     * @param string $request - action to carry out
     * @access private
     * @return object $page
     */
    private function acceptmember($group, $request)
    {
        if (!$this->_model->getLoggedInMember() || !$this->_model->canAccessGroupAdmin($group) || empty($request[3]))
        {
            $this->_redirect('groups/');
        }

        $this->_model->acceptGroupMember($group, $request[3]);

        return new GroupStartPage();
    }


    /**
     * handles member administration page
     *
     * @param object $group - group entity
     * @param string $request - action to carry out
     * @access private
     * @return object $page
     */
    private function memberadministration($group, $request)
    {
        if (!$this->_model->getLoggedInMember() || !$this->_model->canAccessGroupAdmin($group))
        {
            $this->_redirect('groups/');
        }

        return new GroupMemberAdministrationPage();
    }


    /**
     * handles member joining a group
     *
     * @param object $group - group entity
     * @param string $request - action to carry out
     * @access private
     * @return object $page
     */
    private function join($group, $request)
    {
        if (!($member = $this->_model->getLoggedInMember()))
        {
            $this->_redirect('groups/');
        }

        if (isset($request[3]) && strtolower($request[3]) == 'true')
        {
            $page = new GroupStartPage();

            (($this->_model->joinGroup($member, $group)) ? $page->setMessage('GroupsJoinSuccess') : $page->setMessage('GroupsJoinFail'));
        }
        else
        {
            $page = new GroupJoinPage();
        }
        return $page;
    }

    /**
     * handles member leaving a group
     *
     * @param object $group - group entity
     * @param string $request - action to carry out
     * @access private
     * @return object $page
     */
    private function leave($group, $request)
    {
        if (!($member = $this->_model->getLoggedInMember()))
        {
            $this->_redirect('groups/');
        }

        if (isset($request[3]) && strtolower($request[3]) == 'true')
        {
            $page = new GroupStartPage();
            (($this->_model->leaveGroup($member, $group)) ? $page->setMessage('GroupsLeaveSuccess') : $page->setMessage('GroupsLeaveFail'));
        }
        else
        {
            $page = new GroupLeavePage();
        }
        return $page;
    }

    /**
     * handles member settings page
     *
     * @param object $group - group entity
     * @param string $request - action to carry out
     * @access private
     * @return object $page
     */
    private function membersettings($group, $request)
    {
        if (!$this->_model->getLoggedInMember())
        {
            $this->_redirect('groups/');
        }

        $page = ((isset($request[3]) && strtolower($request[3]) == 'true') ? new GroupStartPage() : new GroupMemberSettingsPage());
        return $page;
    }

    /**
     * handles group settings page
     *
     * @param object $group - group entity
     * @param string $request - action to carry out
     * @access private
     * @return object $page
     */
    private function groupsettings($group, $request)
    {
        if (!$this->_model->getLoggedInMember() || !$this->_model->canAccessGroupAdmin($group))
        {
            $this->_redirect('groups/');
        }

        $page = ((isset($request[3]) && strtolower($request[3]) == 'true') ? new GroupStartPage() : new GroupSettingsPage());
        return $page;
    }

    /**
     * handles group deletion page
     *
     * @param object $group - group entity
     * @param string $request - action to carry out
     * @access private
     * @return object $page
     */
    private function delete($group, $request)
    {
        if (!$this->_model->getLoggedInMember()  || !$this->_model->canAccessGroupDelete($group))
        {
            $this->_redirect('groups/');
        }

        if (isset($request[3]) && strtolower($request[3]) == 'true')
        {
            $this->_model->deleteGroup($group);
            $this->_redirect('groups/');
        }
        else
        {
            $page = new GroupDeletePage();
        }

        return $page;
    }

    /**
     * handles showing group forum page
     *
     * @param object $group - group entity
     * @param string $request - action to carry out
     * @access private
     * @return object $page
     */
    private function forum($group, $request)
    {
        return new GroupForumPage();
    }

    /**
     * handles showing group members page
     *
     * @param object $group - group entity
     * @param string $request - action to carry out
     * @access private
     * @return object $page
     */
    private function members($group, $request)
    {
        return new GroupMembersPage();
    }

    /**
     * handles showing group wiki page
     *
     * @param object $group - group entity
     * @param string $request - action to carry out
     * @access private
     * @return object $page
     */
    private function wiki($group, $request)
    {
        return new GroupWikiPage();
    }
//}}}

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

        $return = implode('/', $args->request);


        if (!APP_User::loggedIn())
        {
            // not logged in.
            // the login form will be shown after the automatic redirect
            // after successful login, the message is recovered.
            return $return;
        }
        
        if ($count < 0)
        {
            // session has expired while user was typing.
            $mem_redirect->expired = true;
            return $return;
        }

        if ($mem_resend->already_sent_as)
        {
            // form has already been processed, with the message sent!
            // for a new message, the user needs a new form.
            // tell the redirected page which message has been already sent!
            $mem_redirect->already_sent_as = $mem_resend->already_sent_as;
            return $return;
        }

        if ($count > 0)
        {
            // form has been already processed $count times,
            // but the last time it was not successful.
            // so, we can send again
            // but tell the page how many times it had failed before
            $mem_redirect->fail_count = $count;
        }

        //TODO: handle problems and creation of groups
        //TODO: figure out what the hell this function is supposed to do: group send or group creation??
        // now finally try to send it.
		$result = $this->_model->createGroup($args->post);
        if (count($result['problems']) > 0)
        {
            $mem_redirect->problems = $result['problems'];
            $mem_redirect->post = $args->post;
            return $return;
        }
        else
        {
            // sending message was successful
//            $mem_resend->already_sent_as = $result->message_id;
            // TODO: return message of success in creating a group
            return "groups/" . $result['group_id'];
        }
    }

    /**
     * callback for changing member settings
     *
     * @param object $args
     * @param object $action
     * @param object $mem_redirect
     * @param object $mem_resend
     * @access public
     * @return string
     */
    public function changeMemberSettings($args, $action, $mem_redirect, $mem_resend)
    {
        $count = $action->count;

        $return = $args->req;
        
        if (!APP_User::loggedIn())
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

        $post = $args->post;
        if (empty($post['membershipinfo_comment']) || empty($post['membershipinfo_acceptgroupmail']) || empty($post['group_id']) || empty($post['member_id']))
        {
            $mem_redirect->problems = true;
            return $return;
        }

        if ($this->_model->getLoggedInMember()->id == $post['member_id'])
        {
            $result = $this->_model->updateMembershipSettings($post['member_id'], $post['group_id'], $post['membershipinfo_acceptgroupmail'], $post['membershipinfo_comment']);
        }
        else
        {
            // TODO: check for rights before updating ... but as these are not in place yet, let anyone do it
            $result = $this->_model->updateMembershipSettings($membership, $post['membershipinfo_acceptgroupmail'], $post['membershipinfo_comment']);
        }

        $mem_redirect->result = $result;
        return $return;
    }

    /**
     * callback for changing group settings
     *
     * @param object $args
     * @param object $action
     * @param object $mem_redirect
     * @param object $mem_resend
     * @access public
     * @return string
     */
    public function changeGroupSettings($args, $action, $mem_redirect, $mem_resend)
    {
        $count = $action->count;

        $return = $args->req;
        
        if (!APP_User::loggedIn())
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

        $post = $args->post;
        if (empty($post['GroupDesc_']) || empty($post['Type']) || empty($post['group_id']) || empty($post['VisiblePosts']) || !($group = $this->_model->findGroup($post['group_id'])) || !$this->_model->canAccessGroupAdmin($group))
        {
            $mem_redirect->problems = array('General' => true);
            return $return;
        }

        $result = $this->_model->updateGroupSettings($group, $post['GroupDesc_'], $post['Type'], $post['VisiblePosts']);

        $mem_redirect->result = $result;
        $mem_redirect->post = $post;
        return $return;
    }


}

