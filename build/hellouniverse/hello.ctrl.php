<?php

/**
 * Hello universe controller
 *
 * @package hellouniverse
 * @author Andreas (lemon-head)
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License (GPL)
 * @version $Id$
 */
class HellouniverseController extends PAppController
{
    /**
     * decide which view page to show.
     * This method is called automatically
     */
    public function index()
    {
        $request = PRequest::get()->request;
        
        // look at the request.
        if (!isset($request[1])) {
            // simple, ugly page
            $view = new HellouniverseSimplePage();
        } else switch ($request[1]) {
            case 'advanced':
                // fully decorated page
                $view = new HellouniversePage();
                break;
            case 'tab1':
            case 'tab2':
            case 'tab3':
                // page with submenu
                $view = new HellouniverseTabbedPage($request[1]);
                break;
            default:
                // simple, ugly page
                $view = new HellouniverseSimplePage();
                break;
        }
        
        // finally display the page.
        // the render() method will call other methods to render the page.
        $view->render();
    }
}


?>