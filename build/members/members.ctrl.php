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
        
    }
    
    
    public function editMyProfileCallback($args, $action, $mem_redirect)
    {
        $post_args = $args->post;
    }
}


?>
