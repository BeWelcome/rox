<?php
/**
 * Meetings controller
 * 
 * @package meeting
 * @author Anu (narnua)
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License (GPL)
 * @version $Id$
 */
class MeetingsController extends PAppController
{
    /**
     * decide which view page to show.
     * This method is called automatically
     */
    public function index()
    {
        $request = PRequest::get()->request;
        $model = new MeetingsModel();
        
        // look at the request.
        if (!isset($request[1])) {
            // simple, ugly page
            $view = new MeetingsSimplePage();
        } else switch ($request[1]) {
            case 'advanced':
                // fully decorated page
                $view = new MeetingsPage();
                break;
            case 'tab1':
            case 'tab2':
            case 'tab3':
                // page with submenu
                $view = new MeetingsTabbedPage($request[1]);
                break;
            default:
                // simple, ugly page
                $view = new MeetingsSimplePage();
                break;
        }
        
        // finally display the page.
        // the render() method will call other methods to render the page.
        $view->setModel($model);
        $view->render();
    }
}


?>