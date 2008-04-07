<?php


class MembersController extends RoxControllerBase
{
    public function index($args = false)
    {
        $request = $args->request;
        // $controlkit = $this->controlkit;
        $controlkit = new ReadWriteObject();
        
        $model = new MembersModel();
        
        if (isset($_SESSION['Username'])) {
            $username_self = $_SESSION['Username'];
        } else {
            $username_self = 'henri';
        }
        $member_self = $model->getMemberWithUsername($username_self);
        
        if (!isset($request[0])) {
            // this should never happen!
            $this->redirect_myprofile();
        } else switch($request[0]) {
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
                $this->redirect_myprofile();
                return;
            case 'my':
                if (!isset($request[1])) {
                    $this->redirect_myprofile();
                    return;
                } else switch($request[1]) {
                    case 'profile':
                        $this->redirect_myprofile();
                        return;
                    case 'preferences':
                        $this->redirect_mypreferences();
                        return;
                    case 'messages':
                        $controlkit->redirect("messages/received");
                        return;
                }
                break;
            case 'people':
            case 'members':
            default:
                if (!isset($request[1])) {
                    // so, who?
                    $this->redirect_myprofile();
                    return;
                } else if (is_numeric($request[1])) {
                    if (!$member = $model->getMemberWithId($request[1])) {
                        $this->redirect_myprofile();
                    } else {
                        $controlkit->redirect("members/$member->Username");
                    }
                    return;
                } else if (!$member = $model->getMemberWithUsername($request[1])) {
                    $this->redirect_myprofile();
                    return;
                } else {
                    if (!isset($request[2])) {
                        $page = new ProfilePage();
                    } else switch($request[2]) {
                        case 'comments':
                            $page = new CommentsPage();
                            break;
                        case 'profile':
                        default:
                            $page = new ProfilePage();
                            break;
                    }
                    $page->member = $member;
                }
        }
        
        $page->model = $model;
        return $page;
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
    
    
    public function myPreferencesCallback($args, $action, $mem_for_redirect)
    {
        
    }
    
    
    public function editMyProfileCallback($args, $action, $mem_for_redirect)
    {
        $post_args = $args->post;
    }
}



?>