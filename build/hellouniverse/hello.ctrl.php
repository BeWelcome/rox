<?php

/**
 * Hello universe controller
 *
 * @package hellouniverse
 * @author Andreas (lemon-head)
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License (GPL)
 * @version $Id$
 */
class HellouniverseController extends RoxControllerBase   //HelloUniverseController wouldn't work!
{
    /**
     * decide which page to show.
     * This method is called automatically
     */
    public function index()
    {
        $request = PRequest::get()->request;
        
        // look at the request.
        if (!isset($request[0])) {
            $page = new HellouniverseSimplePage();
        } else switch ($request[0]) {
            case 'calculator':
                $page = new HellouniverseCalculatorPage();
                break;
            case 'hellouniverse':
            default:
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
                    case 'wp':
                    case 'blog':
                    case 'wordpress':
                        $page = new HellouniverseWordpressPage();
                        break;
                    case 'post':
                    case 'calculator':
                        $page = new HellouniverseCalculatorPage();
                        break;
                    default:
                        // simple, ugly page
                        $page = new HellouniverseSimplePage();
                        break;
                }
        }
        
        // return the $page object, so the "$page->render()" function can be called somewhere else.
        return $page;
    }
    
    
    public function calculatorCallback($args, $action, $mem_redirect, $mem_resend)
    {
        $post_args = $args->post;
        
        // give some information to the page that will show up after the redirect
        $mem_redirect->x = $x = $post_args['x'];
        $mem_redirect->y = $y = $post_args['y'];
        $mem_redirect->z = $x + $y;
    }
}


?>