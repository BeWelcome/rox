<?php

/**
 * Hello universe controller
 *
 * @package hellouniverse
 * @author Andreas (lemon-head)
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License (GPL)
 * @version $Id$
 */
class HellouniverseController extends RoxControllerBase
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
            $page = new HellouniverseSimplePage();
        } else switch ($request[1]) {
            case 'advanced':
                // fully decorated page
                $page = new HellouniversePage();
                break;
            case 'tab1':
            case 'tab2':
            case 'tab3':
                // page with submenu
                $page = new HellouniverseTabbedPage($request[1]);
                break;
            case 'post':
                $page = new HellouniversePostPage();
                $page->inject('RoxPostHandler', $this->get('RoxPostHandler'));
                break;
            default:
                // simple, ugly page
                $page = new HellouniverseSimplePage();
                break;
        }
        
        // finally display the page.
        // the render() method will call other methods to render the page.
        $page->render();
    }
    
    public function postCallback($post_args = false) {
        echo '<b>'.__METHOD__.'</b><br>';
        echo '<pre>';
        print_r($post_args);
        echo '</pre>';
        PVars::getObj('page')->output_done = true;
    }
    
    public function postExpired($post_args = false) {
        $page = new HellouniversePostPage();
        $page->inject('post_args', $post_args);
        $page->inject('post_expired', true);
        $page->render();
    }
}


?>