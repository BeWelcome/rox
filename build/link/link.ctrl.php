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
      if (!($User = APP_User::login())) { // First ensure that the user is logged in
            $page = new MessagesMustloginPage();
            $page->setRedirectURL(implode('/',$request));
        		return $page;
      }

			$model = new LinkModel();
        
        // look at the request.
        if (!isset($request[0])) {
            $page = new LinkShowPage('showlink');
        } else switch ($request[0]) {
            case 'link':
            default:
                if (!isset($request[1])) {
                    $page = new LinkShowPage('showlink');

                } else switch ($request[1]) {
				        case 'myself':

									$result->from= $_SESSION['Username'];
									$result->to = $result->SearchUsername= $request[2] ;	
									if (isset($request[3])) 
											$result->limit=$request[3];
									else
											$result->limit=10;
									$result->linksFull =$this->_model->getLinksFull($result->from,$result->to,$result->limit);
									$result->links =$this->_model->getLinks($result->from,$result->to,$result->limit);


                        $page = new LinkShowPage($request[1],$result);
												break ;

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
                        $page = new LinkShowPage(showlink);
                        break;
                }
        }
        
        // return the $page object, so the "$page->render()" function can be called somewhere else.
        return $page;
    }
    
    
    public function LinkShowCallback($args, $action, $mem_redirect, $mem_resend)
    {
    $post_args = $args->post;
		$from = $post_args['from'];
		$to = $post_args['to'];	
    $limit = $post_args['limit'];
		//$link = $this->_model->getSingleLink($fromID,$toID);
		if (empty($from)) {
			$from=$_SESSION["Username"] ;
		}
		if (empty($limit)) {
			$limit=10 ; // give a default value to limit
		}
		$mem_redirect->from= $from;
		$mem_redirect->to = $mem_redirect->SearchUsername= $to ;	
    $mem_redirect->limit = $limit ;
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
