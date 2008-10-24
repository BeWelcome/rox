<?php

/**
 * API controller
 *
 * @package api
 * @author Philipp 
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License (GPL)
 * @version $Id$
 */
class ApiController extends RoxControllerBase   //HelloUniverseController wouldn't work!
{
    /**
     * decide which page to show.
     * This method is called automatically
     */
    public function index($args = false)
    {
        $request = PRequest::get()->request;
        
        // look at the request.
        if (!isset($request[0])) {
            $page = new SimplePage();
        } else switch ($request[0]) {
            case 'calculator':
                $page = new HellouniverseCalculatorPage();
                break;
            case 'hellouniverse':
            default:
                if (!isset($request[1])) {
                    $page = new FootstepsPage();
                } else switch ($request[1]) {
                    case 'footsteps':
                        $page = new FootstepsPage();
                        break;
                    case 'bwtest1':
                        $page = new Bwtest1Page($request[1]);
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
