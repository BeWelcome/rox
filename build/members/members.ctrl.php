<?php


class MembersController extends RoxControllerBase
{
    function index($args = false)
    {
        // REMOVE NEXT 3 LINES TO ACTIVATE most of the members-pages again
        // $request = $args->request;
        // if (!isset($request[0]) || $request[0] != 'setlocation')
            // $this->redirect("");

        $model = new MembersModel;
        if (isset($_SESSION['Username'])) {
            // logged in
            $username_self = $_SESSION['Username'];
            $member_self = $model->getMemberWithUsername($username_self);
            return $this->index_loggedIn($args, $member_self);
        } else {
            return $this->index_loggedOut($args);
        }
    }
    
    protected function index_loggedOut($args)
    {
        $request = $args->request;
        $model = new MembersModel();
        
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
                    $page = new MembersMembernotspecifiedPage;
                } else if ($request[1] == 'avatar') {
                    if (!isset($request[2]) || !$member = $this->getMember($request[2]))
                        PPHP::PExit();
                    PRequest::ignoreCurrentRequest();
                    $model->showAvatar($member->id);
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
                            $model->set_profile_language($request[2]);
                            break;
                    }
                    $page->member = $member;
                }
        }
        $page->model = $model;
        return $page;
    }
    
    protected function index_loggedIn($args, $member_self)
    {
        $request = $args->request;
        $model = new MembersModel();
        
        $myself = true;
        
        switch (isset($request[0]) ? $request[0] : false) {
            case 'updatemandatory':
                $page = new UpdateMandatoryPage();
                break;
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
                    $model->set_profile_language($request[1]);
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
                if (!isset($request[1])) {
                    // no member specified
                    $page = new MembersMembernotspecifiedPage;
                    $member = false;
                } else if ($request[1] == 'avatar') {
                    if (!isset($request[2]) || !$member = $this->getMember($request[2]))
                        PPHP::PExit();
                    PRequest::ignoreCurrentRequest();
                    $model->showAvatar($member->id);
                    break;
                } else if (!$member = $this->getMember($request[1])) {
                    // did not find such a member
                    $page = new MembersMembernotfoundPage;
                } else {
                    // found a member with given id or username
                    $myself = false;
                    if ($member->id == $member_self->id) {
                        // user is watching her own profile
                        $myself = true;
                    }
                    switch (isset($request[2]) ? $request[2] : false) {
                        case 'comments':
                            if (!$myself && isset($request[3]) && $request[3] == 'add') {
                                $page = new AddCommentPage();
                            } else {
                                $page = new CommentsPage();
                            }
                            break;
                        case 'redesign':
                            $page = new ProfileRedesignPage();
                            break;
                        case 'profile':
                        case '':
                        case false:
                            $page = new ProfilePage();
                            break;
                        default:
                            $page = new ProfilePage();
                            $model->set_profile_language($request[2]);
                            break;
                    }
                }
        }
        if (!isset($member)) {
            $page->member = $member_self;
        } else if (is_object($member)) {
            $page->member = $member;
        }
        if (isset($myself) && $myself) {
            $page->myself = true;
        }
        $page->model = $model;
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
            $Member = new MembersModel;
            
            // set the location
            $result = $Member->setLocation($vars['id'],$vars['geonameid']);
            $errors['Geonameid'] = 'Geoname not set';
            if (count($result['errors']) > 0) {
                $mem_redirect->errors = $result['errors'];
            }
            return false;
        }
    }

    public function updateMandatoryCallback($args, $action, $mem_redirect, $mem_resend)
    {
        $request = $args->request;
        if (isset($args->post)) {
            foreach ($args->post as $key => $value) {
                $vars[$key] = $value;
            }
            $model = new Rox();
            
            $errors = $model->checkUpdateMandatoryForm($vars);
            
            if (count($errors) > 0) {
                // show form again
                $vars['errors'] = $errors;
                $mem_redirect->post = $vars;
                return false;
            }
            $Signup = new SignupModel;
            $Signup->polishFormValues($vars);
            
            // signup on MyTB successful, yeah.
            $id = $model->registerBWMember($vars);
            $_SESSION['IdMember'] = $id;
            
            unset($_SESSION['IdMember']);
            return 'signup/finish';
        }
        return false;        
    }
    
    public function myPreferencesCallback($args, $action, $mem_redirect)
    {
        $vars = $args->post;
        $request = $args->request;
        $model = new MembersModel;
        $errors = $model->checkMyPreferences($vars);
        
        if (count($errors) > 0) {
            // show form again
            $mem_redirect->problems = $errors;
            $mem_redirect->post = $vars;
            return false;
        }
    
        if( !($User = APP_User::login()))
            return false;
        
        $model->editPreferences($vars);
        
        // set profile as public
        if( isset($vars['PreferencePublicProfile']) && $vars['PreferencePublicProfile'] != '') {   
            $model->set_public_profile($vars['memberid'],($vars['PreferencePublicProfile'] == 'Yes') ? true : false);
        }
        // set new password
        if( isset($vars['passwordnew']) && strlen($vars['passwordnew']) > 0) {
            $query = 'UPDATE `members` SET `PassWord` = PASSWORD(\''.trim($vars['passwordnew']).'\') WHERE `id` = '.$_SESSION['IdMember'];
            if( $this->dao->exec($query)) {
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
     * commentCallback - NOT FINISHED YET !
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
        $model = new MembersModel;
        $errors = $model->checkCommentForm($vars);
        
        if (count($errors) > 0) {
            // show form again
            $mem_redirect->post = $vars;
            return false;
        }
        
        $member = $this->getMember($request[1]);
        $TCom = $member->get_comments_commenter($_SESSION['IdMember']);
        // add the comment!
        if (!$model->addComment(isset($TCom[0]) ? $TCom[0] : false,$vars)) return false;
        
        return 'members/'.$request[1].'/comments';
    }
    
    
    public function editMyProfileCallback($args, $action, $mem_redirect, $mem_resend)
    {
        if (isset($args->post)) {
            $vars = $args->post;
            $request = $args->request;
            $model = new MembersModel;
            $errors = $model->checkProfileForm($vars);
            $vars['errors'] = array();
            if (count($errors) > 0) {
                // show form again
                $vars['errors'] = $errors;
                $mem_redirect->post = $vars;
                return false;
            }
            $vars['member'] = $this->getMember($vars['memberid']);
            $vars = $model->polishProfileFormValues($vars);
            $success = $model->updateProfile($vars);
            if (!$success) $mem_redirect->problems = array(0 => 'Could not update profile');
            
            // Redirect to a nice location like editmyprofile/finish
            $str = implode('/',$request);
            if (in_array('finish',$request)) return $str;
            return $str.'/finish';
            
        }
    }
}


?>
