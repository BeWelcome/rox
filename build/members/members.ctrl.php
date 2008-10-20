<?php


class MembersController extends RoxControllerBase
{
    function index($args = false)
    {
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
                } else if (!$member = $this->getMember($request[1])) {
                    // did not find such a member
                    $page = new MembersMembernotfoundPage;
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
		
		/**
		* get infnomation about the connection between members
		*
		**/
	function linkpath_render($fromID,$toID,$cssID) {
        $linkwidget = new LinkSinglePictureLinkpathWidget();
        $linkwidget->render($fromID,$toID,$cssID);
	}
        
        switch (isset($request[0]) ? $request[0] : false) {
            case 'mypreferences':
                $page = new MyPreferencesPage();
                break;
            case 'editmyprofile':
                $page = new EditMyProfilePage();
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
    
    
    public function myPreferencesCallback($args, $action, $mem_redirect)
    {
        $post_args = $args->post;
        $callbackId = PFunctions::hex2base64(sha1(__METHOD__));
        if( PPostHandler::isHandling()) {
            if( !($User = APP_User::login()))
                return false;
            $vars =& $post_args;
            $errors = array();
            $messages = array();

            $query = "select id from members where id=" . $_SESSION["IdMember"] . " and PassWord=PASSWORD('" . trim($vars['OldPassword']) . "')";
            $qry = $this->dao->query($query);
            $rr = $qry->fetch(PDB::FETCH_OBJ);
            if (!$rr || !array_key_exists('id', $rr))
                $errors[] = 'ChangePasswordInvalidPasswordError';
            if( isset($vars['NewPassword']) && strlen($vars['NewPassword']) > 0) {
                if( strlen($vars['NewPassword']) < 8) {
                    $errors[] = 'ChangePasswordPasswordLengthError';
                }
                if(isset($vars['ConfirmPassword'])) {
                    if(strlen(trim($vars['ConfirmPassword'])) == 0) {
                        $errors[] = 'ChangePasswordConfirmPasswordError';
                    } elseif(trim($vars['NewPassword']) != trim($vars['ConfirmPassword'])) {
                        $errors[] = 'ChangePasswordMatchError';
                    }
                }
            }
            if( count($errors) > 0) {
                $vars['errors'] = $errors;
                return false;
            }
            if( isset($vars['NewPassword']) && strlen($vars['NewPassword']) > 0) {
//            	$pwenc = MOD_user::passwordEncrypt($vars['NewPassword']);
//              $query = 'UPDATE `user` SET `pw` = \''.$pwenc.'\' WHERE `id` = '.(int)$User->getId();
                $query = 'UPDATE `members` SET `PassWord` = PASSWORD(\''.trim($vars['NewPassword']).'\') WHERE `id` = '.$_SESSION['IdMember'];
                if( $this->dao->exec($query)) {
                    $messages[] = 'ChangePasswordUpdated';
                    $L = MOD_log::get();
                    $L->write("Password changed", "change password");
                } else {
                    $errors[] = 'ChangePasswordNotUpdated';
                }
            }

            $vars['errors'] = $errors;
            $vars['messages'] = $messages;
            return false;
        } else {
            PPostHandler::setCallback($callbackId, __CLASS__, __FUNCTION__);
            return $callbackId;
        }
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
        
        // add the comment!
        if (!$model->addComment($vars)) return false;
        
        return 'members/'.$request[1].'/comments';
    }
    
    
    public function editMyProfileCallback($args, $action, $mem_redirect)
    {
        $post_args = $args->post;
    }
}


?>
