<?php

/**
 * Link controller
 *
 * @package link
 * @author Philipp (philipp)
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License (GPL)
 * @version $Id$
 */
class LinkController extends RoxControllerBase   
{

    public function __construct()
    {
        parent::__construct();
        $this->_model = new LinkModel();
     //   $this->_view  = new LinkView($this->_model);
    }
    
    public function __destruct()
    {
        unset($this->_model);
     //   unset($this->_view);
    }

    /**
     * decide which page to show.
     * This method is called automatically
     */
    public function index($args = false)
    {
        $request = PRequest::get()->request;
		$model = new LinkModel();
        
        // look at the request.
        if (!isset($request[0])) {
            $page = new LinkShowPage(showlink);
        } else switch ($request[0]) {
            case 'link':
            default:
                if (!isset($request[1])) {
                    $page = new LinkPage();

                } else switch ($request[1]) {
				        case 'display':
                        // fully decorated page
                        $page = new LinkDisplayPage($request[1]);
                        break;
                    case 'update':
                        // fully decorated page
                        $page = new LinkUpdatePage($request[1]);
                        break;
                    case 'showlink':
                        // page with submenu
                        $page = new LinkShowPage($request[1]);
                        break;
                    case 'showfriends':
                        // page with submenu
                        $page = new LinkShowFriendsPage($request[1]);
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
    
    
    public function LinkShowCallback($args, $action, $mem_redirect, $mem_resend)
    {
        $post_args = $args->post;
		$mem_redirect->from = $from = $post_args['from'];
		$mem_redirect->to = $to = $post_args['to'];	
        $mem_redirect->limit = $limit = $post_args['limit'];
		//$link = $this->_model->getSingleLink($fromID,$toID);
		$mem_redirect->linksFull = $linksFull =$this->_model->getLinksFull($from,$to,$limit);		
		$mem_redirect->links = $links =$this->_model->getLinks($from,$to,$limit);
		//$this->_model->getFriendsFull($fromUsername,$toUsername,$limit);


    }
	
	    public function LinkShowFriendsCallback($args, $action, $mem_redirect, $mem_resend)
    {
        $post_args = $args->post;
		$mem_redirect->from = $from = $post_args['from'];
		$mem_redirect->degree = $degree = $post_args['degree'];	
        $mem_redirect->limit = $limit = $post_args['limit'];
		//$link = $this->_model->getSingleLink($fromID,$toID);
		//$mem_redirect->link = $link =$this->_model->getLinks($fromID,$toID,$limit);
		$mem_redirect->friendsIDs = $this->_model->getFriends($from,$degree,$limit);
		$mem_redirect->friendsFull = $this->_model->getFriendsFull($from,$degree,$limit);


    }
}


?>
