<?php

/**
 * This controller is called when the request is 'groups/...'
 */
class GroupsController extends PAppController
{
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
                $page = new GroupsCreationPage();
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
}


?>