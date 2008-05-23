<?php
/** 
 * Online Controller
 * 
 * @package online
 * @author lupochen
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License (GPL)
 * @version $Id$
 */


class OnlineController extends RoxControllerBase
{

    public function index()
    {
        $request = PRequest::get()->request;
        $model = new OnlineModel();
        
        if (!isset($request[1])) {
            // normal chat page
            $page = new OnlinePage();
        } else switch($request[1]) {
            case 'online':
            // nothing yet
            default:
                $page = new OnlinePage();
        }
        $page->model = $model;
        PVars::getObj('page')->output_done = true;
        return $page;
    }
    
    protected function createOnlineOtherPage($args)
    {
        return new OnlineOtherPage();
    }
    
}
?>