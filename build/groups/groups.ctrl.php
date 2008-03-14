<?php


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
                echo 'bla';
                //header('Location: '.PVars::get('env')->baseuri.'groups');
                PPHP::PExit();
            } else if (!isset($request[2])) {
                $page = new GroupStartPage();
            } else switch ($request[2]) {
                // which group subpage is requested?
                default:
                    $page = new GroupStartPage();
            }
            
            $page->setGroup($group);
        } else switch ($request[1]) {
            case 'search':
                $page = new GroupsSearchPage();
                $page->setSearchQuery($search_query);
                break;
            case 'new':
                $page = new GroupsCreationPage();
                break;
            default:
                $page = new GroupsOverviewPage();
        }
        $page->setModel($model);
        $page->render();
    }
}


?>