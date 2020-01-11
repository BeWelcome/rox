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

                }
								else switch ($request[1]) {
				        case 'myself':

								  $result->strerror="" ;
									$result->from= $this->session->get('Username');
									$result->to = $request[2] ;
									$IdGuy=$this->_model->getMemberID($result->to);
									if ($IdGuy<=0)  {
										if ($result->to=="") {
											$result->strerror.="<br />You must give a second Username " ;
										}
										else {
											$result->strerror.="<br />No such member ".$result->to ;
										}
									}
									if (isset($request[3]))
											$result->limit=$request[3];
									else
											$result->limit=10;
									$result->linksFull =$this->_model->getLinksFull($result->from,$result->to,$result->limit);
									$result->links =$this->_model->getLinks($result->from,$result->to,$result->limit);


                        $page = new LinkShowPage($request[1],$result);
												break ;

				        case 'display': // Nota : display must not be a user name !
                        // fully decorated page
                        $page = new LinkDisplayPage($request[1]);
                        break;
                    case 'update':
                        // fully decorated page

                        set_time_limit(0);
                        $page = new LinkUpdatePage($request[1]);
                        break;
                    case 'rebuild':
                    case 'rebuildmissing':
                        // fully decorated page

                        set_time_limit(0);
                        $page = new LinkRebuildPage($request[1]);
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
								  	$result->strerror="" ;
										$result->from= $request[1];
										$IdGuy=$this->_model->getMemberID($result->from);
										if ($IdGuy<=0)  {
											if ($result->from=="") {
												$result->strerror.="<br />You must give a first Username " ;
											}
											else {
												$result->strerror.="<br />No such member ".$result->from ;
											}
										}
										$result->to = $request[2] ;
										$IdGuy=$this->_model->getMemberID($result->to);
										if ($IdGuy<=0)  {
											if ($result->to=="") {
												$result->strerror.="<br />You must give a second Username " ;
											}
											else {
												$result->strerror.="<br />No such member ".$result->to ;
											}
										}
										if (isset($request[3]))
											$result->limit=$request[3];
										else
											$result->limit=10;
										$result->linksFull =$this->_model->getLinksFull($result->from,$result->to,$result->limit);
										$result->links =$this->_model->getLinks($result->from,$result->to,$result->limit);


                    $page = new LinkShowPage($request[1],$result);
										break ;
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
			$from=$this->session->get("Username") ;
		}
		if (empty($limit)) {
			$limit=10 ; // give a default value to limit
		}

		$mem_redirect->strerror="" ;
		$IdGuy=$this->_model->getMemberID($from);
		if ($IdGuy<=0)  {
			$mem_redirect->strerror.="<br />No such member ".$from ;
		}
		$IdGuy=$this->_model->getMemberID($to);
		if ($IdGuy<=0)  {
			if ($to=="") {
				$mem_redirect->strerror.="<br />You must give a second Username " ;
			}
			else {
				$mem_redirect->strerror.="<br />No such member ".$to ;
			}
		}
		if ((int)$limit<=0)  {
			$mem_redirect->strerror.="<br />limit must be 1 or more " ;
		}


		$mem_redirect->from= $from;
		$mem_redirect->to = $to ;
    $mem_redirect->limit = $limit ;
		$mem_redirect->linksFull = $linksFull =$this->_model->getLinksFull($from,$to,$limit);
		$mem_redirect->links = $links =$this->_model->getLinks($from,$to,$limit);
		//$this->_model->getFriendsFull($fromUsername,$toUsername,$limit);


    }

	    public function LinkShowFriendsCallback($args, $action, $mem_redirect, $mem_resend)
    {
        $post_args = $args->post;
				$from = $post_args['from'];
				$degree = $post_args['degree'];
    		$limit = $post_args['limit'];

				$mem_redirect->strerror="" ;
				$IdGuy=$this->_model->getMemberID($from);
				if ($IdGuy<=0)  {
					$mem_redirect->strerror.="<br />No such member ".$from ;
				}
				if ($degree<=0)  {
					$mem_redirect->strerror.="<br />degree must be 1 or more " ;
				}
				if ($limit<=0)  {
					$mem_redirect->strerror.="<br />limit must be 1 or more " ;
				}


				$mem_redirect->from = $from ;
				$mem_redirect->degree = $degree ;
        $mem_redirect->limit = $limit ;
		//$link = $this->_model->getSingleLink($fromID,$toID);
		//$mem_redirect->link = $link =$this->_model->getLinks($fromID,$toID,$limit);
				$mem_redirect->friendsIDs = $this->_model->getFriends($from,$degree,$limit);
				$mem_redirect->friendsFull = $this->_model->getFriendsFull($from,$degree,$limit);


    } // end of LinkShowFriendsCallback
}


?>
