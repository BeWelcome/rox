<?php


class MembersController extends RoxControllerBase
{

    public function __construct()
    {
        parent::__construct();
        $this->model = new MembersModel;
    }

    /**
     * still main function called by router
     *
     * @todo split controller up to use routing, when proper routing is working
     * @param object $args
     * @access public
     * @return object
     */
    public function index($args = false)
    {
        if ($member = $this->model->getLoggedInMember())
        {
            return $this->index_loggedIn($args, $member);
        }
        else
        {
            return $this->index_loggedOut($args);
        }
    }
    
    protected function index_loggedOut($args)
    {
        $request = $args->request;
        
        switch (isset($request[0]) ? $request[0] : false) {
            case 'updatemandatory':
            case 'mypreferences':
            case 'editmyprofile':
            case 'myvisitors':      
            case 'self':
            case 'myself':
            case 'my':
                // you are not supposed to open these pages when not logged in!
                $page = new MembersMustloginPage;
                break;
            case 'members':
            case 'people':
            default:
                if (!isset($request[1]) || empty($request[1])) {
                    // no member specified
                    $this->redirect("places");
                } else if ($request[1] == 'avatar') {
                    if (!isset($request[2]) || !$member = $this->getMember($request[2]))
                        PPHP::PExit();
                    PRequest::ignoreCurrentRequest();
                    $this->model->showAvatar($member->id);
                    break;
                } else if (!$member = $this->getMember($request[1])) {
                    // did not find such a member
                    $page = new MembersMembernotfoundPage;
                } else if (!$member->publicProfile) {
                    // this profile is not public
                    $page = new MembersMustloginPage;
                } else {
                    // found a member with given id or username. juhu
                    switch (isset($request[2]) ? $request[2] : false) {
                        case 'comments':
                            $page = new CommentsPage();
                            break;
                        case 'profile':
                        case '':
                        case false:
                            $page = new ProfilePage();
                            break;
                        default:
                            $page = new ProfilePage();
                            $this->model->set_profile_language($request[2]);
                            break;
                    }
                    $page->member = $member;
                }
        }
        $page->model = $this->model;
        return $page;
    }
    
    protected function index_loggedIn($args, $member_self)
    {
        $request = $args->request;
        
        $myself = true;
        
        switch (isset($request[0]) ? $request[0] : false) {
            case 'setlocation':
                $page = new SetLocationPage();
                break;
            case 'mypreferences':
                $page = new MyPreferencesPage();
                break;
            case 'editmyprofile':
                $page = new EditMyProfilePage();
                // $member->edit_mode = true;
                if (isset($request[1]))
                    $this->model->set_profile_language($request[1]);
                if (isset($request[2]) && $request[2] == 'delete')
                    $page = new DeleteTranslationPage();
                if (in_array('finish',$request))
                    $page->status = "finish";
                break;
            case 'myvisitors':
                $page = new MyVisitorsPage();
                break;
            case 'self':
            case 'myself':
                $page = new ProfilePage;
                break;
            case 'my':
                switch (isset($request[1]) ? $request[1] : false) {
                    case 'preferences':
                        $page = new MyPreferencesPage();
                        break;
                    case 'visitors':
                        $page = new MyVisitorsPage();
                        return;                        
                    case 'messages':
                        $this->redirect("messages/received");
                        return;
                    case 'profile':
                    default:
                        $page = new ProfilePage;
                }
                break;
            case 'people':
            case 'members':
            default:
                if (!isset($request[1]))
                {
                    // no member specified
                    $this->redirect("places");
                }
                elseif ($request[1] == 'reportcomment')
                {
                    if (!isset($request[2]) || !isset($request[3]) || !$this->model->getLoggedInMember() || !$this->model->reportBadComment($request[2], $request[3]))
                    {
                        $this->redirect('');
                    }
                    $member = $this->model->getLoggedInMember();
                    $this->logWrite("{$member->Username} has reported comment ID: {$request[3]} on user {$request[2]} as problematic", 'comments');
                    $this->redirect('feedback?IdCategory=4');
                }
                else if ($request[1] == 'avatar')
                {
                    if (!isset($request[2]) || !$member = $this->getMember($request[2]))
                        PPHP::PExit();
                    PRequest::ignoreCurrentRequest();
                    $this->model->showAvatar($member->id);
                    break;
                }
                else if (!$member = $this->getMember($request[1]))
                {
                    // did not find such a member
                    $page = new MembersMembernotfoundPage;
                }
                else
                {
                    // found a member with given id or username
                    $myself = false;
                    if ($member->id == $member_self->id)
                    {
                        // user is watching her own profile
                        $myself = true;
                    }
                    else
                    {
                        if (($logged_member = $this->model->getLoggedInMember()) and $logged_member->isNotActiveHidden())
                        {
                            $member->recordVisit($logged_member);
                        }
                    }
                    switch (isset($request[2]) ? $request[2] : false) {
                        case 'relations':
                            if (!$myself && isset($request[3]) && $request[3] == 'add') {
                                $page = new AddRelationPage();
                                if (isset($request[4]) && $request[4] == 'finish') {
                                    $page->relation_wait = true;
                                }
                            } else {
                                $page = new RelationsPage();
                            }
                            break;
                        case 'comments':
                            if (!$myself && isset($request[3]) && $request[3] == 'adminedit') {
                                $page = new AddCommentPage();
                                $page->adminedit;
                            } elseif (!$myself && isset($request[3]) && ($request[3] == 'add' || $request[3] == 'edit')) {
                                $page = new AddCommentPage();
                            } else {
                                $page = new CommentsPage();
                            }
                            break;
                        case 'groups':
                            $my_groups = $member->getGroups();
                            $params->strategy = new HalfPagePager('left');
                            $params->items = $my_groups;
                            $params->items_per_page = 10;
                            $pager = new PagerWidget($params);
                            $page = new MemberGroupsPage();
                            $page->my_groups = $my_groups;
                            $page->pager = $pager;
                            break;
                        case 'redesign':
                            $page = new ProfileRedesignPage();
                            break;
                        case 'adminedit':
                            $rights = new MOD_right();
                            if ($rights->hasRight('Admin'))
                            {
                                $page = new EditMyProfilePage();
                                $page->adminedit = true;
                                // $member->edit_mode = true;
                                if (isset($request[3]) && $request[3] == 'delete')
                                    $page = new DeleteTranslationPage();
                                if (in_array('finish',$request))
                                    $page->status = "finish";
                            }
                            else
                            {
                                $page = new MembersMembernotfoundPage;
                            }
                            break;
                        case 'profile':
                        case '':
                        case false:
                            $page = new ProfilePage();
                            break;
                        default:
                            $page = new ProfilePage();
                            $this->model->set_profile_language($request[2]);
                            break;
                    }
                }
        }
        if (!isset($member)) {
            $page->member = $member_self;
        } else if (is_object($member)) {
            $page->member = $member;
        }
        if (!empty($myself)) {
            $page->myself = true;
        }
        $page->loggedInMember = $this->model->getLoggedInMember();
        $page->model = $this->model;
        return $page;
    }
    
    protected function getMember($cid)
    {
        $model = new MembersModel;
        if (is_numeric($cid)) {
            return $model->getMemberWithId($cid);
        } else if (!empty($cid)) {
            return $model->getMemberWithUsername($cid);
        } else {
            return false;
        }
    }
    
    protected function redirect_myprofile()
    {
        if (isset($_SESSION['Username'])) { 
            $username = $_SESSION['Username'];
        } else {
            $username = 'henri';
        }
        $this->redirect("members/$username");
    }
    
    public function setLocationCallback($args, $action, $mem_redirect, $mem_resend)
    {
        $request = $args->request;
        if (isset($args->post)) {
            $mem_redirect->post = $args->post;
            foreach ($args->post as $key => $value) {
                $vars[$key] = $value;
            }
            
            $errors = array();
            // member id
            if (empty($vars['id'])) {
                $errors[] = 'GeoErrorProvideMemberId';
                unset($vars['id']);
            }
            // geonameid
            if (empty($vars['geonameid'])) {
                $errors[] = 'SignupErrorProvideLocation';
                unset($vars['geonameid']);
            }
            
            if (count($errors) > 0) {
                // show form again
                $vars['errors'] = $errors;
                $mem_redirect->post = $vars;
                return false;
            }
            
            // set the location
            $result = $this->model->setLocation($vars['id'],$vars['geonameid']);
            $errors['Geonameid'] = 'Geoname not set';
            if (count($result['errors']) > 0) {
                $mem_redirect->errors = $result['errors'];
            }
            return false;
        }
    }

    public function updateMandatoryCallback($args, $action, $mem_redirect, $mem_resend)
    {
        throw new Exception('This should not be used - mandatory details are taken care of in edit my profile');
        $request = $args->request;
        if (isset($args->post)) {
            foreach ($args->post as $key => $value) {
                $vars[$key] = $value;
            }
            
            if (count($errors) > 0) {
                // show form again
                $vars['errors'] = $errors;
                $mem_redirect->post = $vars;
                return false;
            }
            $model->polishFormValues($vars);
            $model->sendMandatoryForm($vars);
            return 'updatemandatory/finish';
        }
        return false;        
    }
    
    public function myPreferencesCallback($args, $action, $mem_redirect)
    {
        $vars = $args->post;
        $request = $args->request;
        $errors = $this->model->checkMyPreferences($vars);
        
        if (count($errors) > 0) {
            // show form again
            $mem_redirect->problems = $errors;
            $mem_redirect->post = $vars;
            return false;
        }
    
        if( !($User = APP_User::login()))
            return false;
        
        $this->model->editPreferences($vars);

        if (isset($vars['PreferenceLanguage']) && $_SESSION['IdLanguage'] != $vars['PreferenceLanguage'])
        {
            $this->model->setSessionLanguage($vars['PreferenceLanguage']);
        }

        // set profile as public
        if( isset($vars['PreferencePublicProfile']) && $vars['PreferencePublicProfile'] != '') {   
            $this->model->set_public_profile($vars['memberid'],($vars['PreferencePublicProfile'] == 'Yes') ? true : false);
        }
        // set new password
        if( isset($vars['passwordnew']) && strlen($vars['passwordnew']) > 0) {
            $query = 'UPDATE `members` SET `PassWord` = PASSWORD(\''.trim($vars['passwordnew']).'\') WHERE `id` = '.$_SESSION['IdMember'];
            if( $this->model->dao->exec($query)) {
                $messages[] = 'ChangePasswordUpdated';
                $L = MOD_log::get();
                $L->write("Password changed", "change password");
            } else {
                $mem_redirect->problems = array(0 => 'ChangePasswordNotUpdated');
            }
        }
        return false;
    }
    
    /**
     * commentCallback
     *
     * @param Object $args
     * @param Object $action 
     * @param Object $mem_redirect memory for the page after redirect
     * @param Object $mem_resend memory for resending the form
     * @return string relative request for redirect
     */
    public function commentCallback($args, $action, $mem_redirect, $mem_resend)
    {
        $vars = $args->post;
        $request = $args->request;
        $errors = $this->model->checkCommentForm($vars); // TODO: checkCommentForm still needs more finetuning
        
        if (count($errors) > 0) {
            // show form again
            $vars['errors'] = $errors;
            $mem_redirect->post = $vars;
            return false;
        }
        
        $member = $this->getMember($request[1]);
        $TCom = $member->get_comments_commenter($this->model->getLoggedInMember()->id);
        // add the comment!
        if (!$this->model->addComment(isset($TCom[0]) ? $TCom[0] : false,$vars)) return false;
        
        return 'members/'.$request[1].'/comments';
    }
    
    
    /**
     * handles edit profile form post - profile updating
     *
     * @param object $args
     * @param object $action
     * @param object $mem_redirect
     * @param object $mem_resend
     * @access public
     * @return string
     */
    public function editMyProfileCallback($args, $action, $mem_redirect, $mem_resend)
    {
        if (isset($args->post)) {
            $vars = $this->cleanVars($args->post);
            $request = $args->request;
            $errors = $this->model->checkProfileForm($vars);
            $vars['errors'] = array();
            if (count($errors) > 0) {
                // show form again
                $vars['errors'] = $errors;
                $mem_redirect->post = $vars;
                return false;
            }
            $rights = new MOD_right;
            if (!$rights->hasRight('Admin'))
            {
                $vars['memberid'] = $this->model->getLoggedInMember()->getPKValue();
            }
            $vars['member'] = $this->getMember($vars['memberid']);
            $vars = $this->model->polishProfileFormValues($vars);
            $success = $this->model->updateProfile($vars);
            if (!$success) $mem_redirect->problems = array('Could not update profile');
            
            // Redirect to a nice location like editmyprofile/finish
            $str = implode('/',$request);
            if (in_array('finish',$request)) return $str;
            return $str.'/finish';
        }
    }

    public function deleteTranslationCallback($args, $action, $mem_redirect, $mem_resend)
    {
        if (isset($args->post)) {
            $vars = $args->post;
            $request = $args->request;
            if (isset($vars['choice']) && $vars['choice'] == 'yes' && isset($vars['memberid'])) {
                if (!isset($vars['profile_language'])) return false;
                $member = $this->getMember($vars['memberid']);
                $fields = $member->get_trads_fields();
                $trad_ids = array();
                foreach ($fields as $field)
                    $trad_ids[] = $member->$field;
                $this->model->delete_translation_multiple($trad_ids,$vars['memberid'],$vars['profile_language']);
                // Redirect to a nice location like editmyprofile/finish
                return 'editmyprofile/finish';
            } else {
                return 'editmyprofile';
            }
        }
    }

    public function RelationCallback($args, $action, $mem_redirect, $mem_resend)
    {
        if (isset($args->post)) {
            $vars = $args->post;
            $request = $args->request;

            if (isset($vars['IdOwner']) && $vars['IdOwner'] == $_SESSION['IdMember'] && isset($vars['IdRelation'])) {
                if (isset($vars['action'])) {
                    $member = $this->getMember($vars['IdRelation']);
                    if (isset($vars['Type'])) $vars['stype'] = $vars['Type'];
                    else {
                        $TabRelationsType = $member->get_TabRelationsType();
                        $stype=""; 
                        $tt=$TabRelationsType;
                        $max=count($tt);
                        for ($ii = 0; $ii < $max; $ii++) {
                            if (isset($vars["Type_" . $tt[$ii]]) && $vars["Type_" . $tt[$ii]] == "on") {
                              if ($stype!="") $stype.=",";
                              $stype.=$tt[$ii];
                            }
                        }
                        $relations = $member->get_relations();
                        $vars['stype'] = $stype;
                    }
                    switch ($vars['action']) {
                    case 'add':
                        $blub = $this->model->addRelation($vars);
                        break;
                    case 'update':
                        $this->model->updateRelation($vars);
                        break;
                    case 'confirm':
                        $vars['confirm'] = 'Yes';
                        $blub = $this->model->addRelation($vars);
                        $this->model->confirmRelation($vars);
                        break;
                    default:
                    }
                }
                // Redirect to a nice location like editmyprofile/finish
                $str = implode('/',$request);
                if (in_array('finish',$request)) return $str;
                return $str.'/finish';
            }
            return false;
        }
    }

}
