<?php
/**
 * Tour controller
 * 
 * @package meeting
 * @author Anu (narnua)
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License (GPL)
 * @version $Id$
 */
class TourController extends PAppController
{
    /**
     * decide which view page to show.
     * This method is called automatically
     */
    public function index()
    {
        $request = PRequest::get()->request;
        $model = new TourModel();

        $page = new TourPage();        
        // look at the request.
        if (!isset($request[1])) {
            $page->page_number = '';
        } else switch ($request[1]) {
            case 'share':
                $page->page_number = 3;
            break;
            case 'meet':
                $page->page_number = 4;
            break;
            case 'trips':
                $page->page_number = 5;
            break;
            case 'maps':
                $page->page_number = 6;
            break;
            case 'openness':
                $page->page_number = 2;
            break;                
            default:
                $page->page_number = '';
            break;
        }
        
        // finally display the page.
        // the render() method will call other methods to render the page.
        $page->model = $model;
        PVars::getObj('page')->output_done = true;
        return $page;
    }
}


?>