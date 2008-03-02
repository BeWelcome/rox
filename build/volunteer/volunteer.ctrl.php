<?php

class VolunteerController extends PAppController
{
    public function index()
    {
        if (!APP_User::isBWLoggedIn()) {
            header('Location: '.PVars::getObj('env')->baseuri.'index');
            exit();
        } else {
            $request = PRequest::get()->request;
            if (!isset($request[1])) {
                $view = new VolunteerDashboardView();
            } else switch ($request[1]) {
                case 'search':
                    $view = new VolunteerSearchView();
                    break;
                case 'tasks':
                    $view = new VolunteerTaskView();
                    break;
                case 'features':
                    $view = new VolunteerFeaturesView();
                    break;
                case 'trac':
                case 'forum':
                case 'otrs':
                case 'blogs':
                case 'mailman':
                case 'newtask':
                case 'newbug':
                    $view = new VolunteerToolsView($request[1]);
                    break;
                case 'dashboard':
                    $view = new VolunteerDashboardView();
                    break;
                default:
                    $loc = PVars::getObj('env')->baseuri;
                    $loc .= 'volunteer';
                    header('Location: '.$loc);
                    PPHP::PExit();
            }
            $view->render();
        }
    }
}


?>