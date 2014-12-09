<?php
/**
 * trip controller
 *
 * @package trip
 * @author The myTravelbook Team <http://www.sourceforge.net/projects/mytravelbook>
 * @copyright Copyright (c) 2005-2006, myTravelbook Team
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License (GPL)
 * @version $Id: trip.ctrl.php 233 2007-02-28 13:37:19Z marco $
 */
class TripController extends RoxControllerBase {
    private $_model;
    private $_view;
    
    public function __construct() {
        parent::__construct();
        $this->_model = new Trip();
    }
    
    public function __destruct() {
        unset($this->_model);
    }
    /*
    public function index() 
    {
        // index is called when http request = ./trip
        $request = PRequest::get()->request;
        if (!isset($request[1]))
            $request[1] = '';
            
        // Enable ViewWrap for cleaner code
        $this->_page = null;

        $member = $this->_model->getLoggedInMember();

        switch($request[1]) {
        	case 'create':
                if (!$member)
                    return false;
                $P->content .= $vw->createForm();
                break;
            
            case 'show':
            	if (isset($request[2]) && $request[2]) {
            		if ($request[2] == 'my') {
            			$this->showMyTrips();
            		} else {
            			$this->showTrips($request[2]);
            		}
            	} else {
            		$this->showAllTrips();
            	}
				break;
            case 'search':
                if (isset($_GET['s'])) {
                    $search = $_GET['s'];
                    if ((strlen($_GET['s']) >= 3)) {
                        //$tagsposts = $this->_model->getTaggedPostsIt($search);
                		$trip_data = $this->_model->getTripsDataForLocation($search);
                        $trips = $this->_model->getTripsForLocation();
                    } else {
                        $error = 'To few arguments';
                        $trip_data = array();
                        $trips = false;
                        //$tagsposts = false;
                    }
                    $P->content .= $vw->searchPage($trips,$trip_data);
                }
                break;
			case 'reorder':
				$this->reorder($_GET['triplist']);
				break;
			case 'edit':
				if (isset($request[2]) && $request[2]) {
					$this->editTrip((int)$request[2]);
					break;
				}
			case 'del':
				if (isset($request[2]) && $request[2]) {
					$this->delTrip((int)$request[2]);
					break;
				}
            default:
				if (intval($request[1])) {
					return $this->showTrip($request[1]);
				} else {
                    if ($member) {
                        $p = new BlogPage($this->_model);
                        $p->page = $page;
                        $p->member = $memberBlog;
                        $p->initPager($this->_model->countRecentPosts($memberBlog->id, false), $page);
                        $p->trips = $this->_model->getRecentPostsArray($memberBlog->id, false, $page);
                    } else {
                        $this->notLoggedIn();
                    }
	                break;
	            }
        }
        return $p;
        // Show the user functions in
        // the sidebar
        $P->newBar .= $vw->userbar();
    }
    */
    private function delTrip($tripId) {
		$callbackId = $this->delProcess();
		PPostHandler::clearVars($callbackId);
        
		$this->_model->prepareEditData($tripId, $callbackId);
        
        $P = PVars::getObj('page');
        $vw = new ViewWrap($this->_view);
        $P->content .= $vw->delTrip($callbackId);
        
		PPostHandler::clearVars($callbackId);
    }

    public function delProcess() {
		$callbackId = PFunctions::hex2base64(sha1(__METHOD__));
		
		if (PPostHandler::isHandling()) {
			return $this->_model->deleteTrip($callbackId);
		} else {
			PPostHandler::setCallback($callbackId, __CLASS__, __METHOD__);
			return $callbackId;
		}
    }


    /**
     * creates a trip, or sets a callback
     *
     * @access public
     * @return mixed
     */
    public function createProcess()
    {
        $callbackId = PFunctions::hex2base64(sha1(__METHOD__));
    	if (PPostHandler::isHandling())
        {
            if (!$member = $this->_model->getLoggedInMember())
            {
                return false;
            }
            $vars =& PPostHandler::getVars();
            $vars['errors'] = array();
            if (!isset($vars['n']) || !$vars['n'])
            {
                $vars['errors'][] = 'name';
                return false;
            }
            if ($trip_id = $this->_model->createTrip($vars, $member))
            {
                return $trip_id;
            }
            return false;
    	}
        else
        {
    		PPostHandler::setCallback($callbackId, __CLASS__, __FUNCTION__);
            return $callbackId;
    	}
    }

    private function editTrip_old($tripId)
    {
		$callbackId = $this->editProcess();
		PPostHandler::clearVars($callbackId);
		
		$this->_model->prepareEditData($tripId, $callbackId);
        
        $P = PVars::getObj('page');
        $vw = new ViewWrap($this->_view);
        $P->content .= $vw->editTrip($callbackId);
        
		PPostHandler::clearVars($callbackId);
    }
    
    public function editProcess()
    {
		$callbackId = PFunctions::hex2base64(sha1(__METHOD__));
		
		if (PPostHandler::isHandling()) {
			return $this->_model->editProcess($callbackId);
		}
        else
        {
			PPostHandler::setCallback($callbackId, __CLASS__, __METHOD__);
			return $callbackId;
		}
    }
    
    private function reorder($items)
    {
    	// Validate the array
    	foreach ($items as &$item)
        {
    		$item = (int) $item;
    	}
    	$this->_model->reorderTripItems($items);
    }
    
    private function getPage()
    {
        // track pages
        $request = PRequest::get()->request;
        $requestStr = implode('/', $request);
        $matches = array();
        if (preg_match('%/page(\d+)%', $requestStr, $matches))
        {
            $page = $matches[1];
        }
        else
        {
            $page = 1;
        }
        return $page;
    }

    private function notLoggedIn()
    {
        $P = PVars::getObj('page');
        $vw = new ViewWrap($this->_view);
        $P->content .= $vw->notLoggedIn();
    }

    private function showMyTrips()
    {
        if (($member = $this->_model->getLoggedInMember()) && ($handle = $member->Username))
        {
    		$this->showTrips($handle);
    	}
    }
    
    private function showTrips($handle)
    {
    }

    private function showMap($trip)
    {
        $P = PVars::getObj('page');
        $vw = new ViewWrap($this->_view);
//		$trips = $this->_model->getTrips($handle);
		$trip_data = $this->_model->getTripsMarkers();
        $P->newBar .= $vw->displayMap($trips = false, $trip_data = false);
    }

    private function showTeaser($trip = false)
    {
//		$trips = $this->_model->getTrips($handle);
//		$trip_data = $this->_model->getTripData();
        $P = PVars::getObj('page');
        $vw = new ViewWrap($this->_view);
        $P->teaserBar .= $vw->teaser($trip);
    }

    public function showAllTrips()
    {
        $member = $this->_model->getLoggedInMember();
        if (!$member) {
            return false;
        }

        $page_no = 1;
        if (isset($this->route_vars['pageno'])) {
            $page_no = $this->route_vars['pageno'];
        }

        $page = new TripPage();
        $count = $this->_model->getTripsCount();
        $page->trips = $this->_model->getTrips(false, $page_no);
        $page->trip_data = $this->_model->getTripData();
        $page->initPager($count);
        $page->member = $member;
        return $page;
    }

    public function showTripsForUsername() {
        $member = $this->_model->getLoggedInMember();
        if (!$member) {
            return false;
        }

        $page_no = 1;
        if (isset($this->route_vars['pageno'])) {
            $page_no = $this->route_vars['pageno'];
        }
        $userName = $this->route_vars['username'];
        $page = new TripPage();
        $count = $this->_model->getTripsCount($userName);
        $page->trips = $this->_model->getTrips($userName, $page_no);
        $page->trip_data = $this->_model->getTripData();
        $page->initPager($count);
        $page->member = $member;
        return $page;
    }

    /**
     * @param $tripid
     * @return SingleTripPage
     * @throws PException
     */
    public function showSingleTrip()
    {
        $member = $this->_model->getLoggedInMember();
        if (!$member) {
            return false;
        }
        $tripId = $this->route_vars['tripid'];
        $trip = $this->_model->getTrip($tripId);
        $trip_data = $this->_model->getTripData();
        if (!$trip)
        {
            return false;
        }
        $page = new TripSingleTripPage();
        $page->member = $member;
        $page->trip = $trip;
        $page->trip_data = $trip_data;
        $page->isOwnTrip = ($trip->IdMember == $member->id);

        return $page;
    }

    public function editCreateCallback(StdClass $args, ReadOnlyObject $action, ReadWriteObject $mem_redirect, ReadWriteObject $mem_resend) {
        // Check variables
        $vars = $args->post;
        $mem_redirect->vars = $vars;
        $errors = $this->_model->checkCreateEditVars( $vars );
        if (!empty($errors)) {
            $mem_redirect->errors = $errors;
            return false;
        }
        $member = $this->_model->getLoggedInMember();
        if ($vars['trip-id'] == 0) {
            $errors = $this->_model->createTrip($vars, $member);
        } else {
            $errors = $this->_model->editTrip($vars, $member);
        }

        if (!empty($errors)) {
            $mem_redirect->errors = $errors;
            return false;
        }
        return $this->router->url('trip_show', array('username' => $member->Username), false);
    }


    /**
     *
     */
    public function createTrip()
    {
        $member = $this->_model->getLoggedInMember();
        if (!$member) {
            return false;
        }
        $page = new TripEditCreatePage(false);
        $page->member = $member;
        $page->vars = array(
            "trip-id" => 0,
            "trip-name" => "",
            "trip-desc" => ""
        );

        return $page;
    }

    public function editTrip()
    {
        $member = $this->_model->getLoggedInMember();
        if (!$member) {
            return false;
        }
        $tripId = $this->route_vars['tripid'];
        $trip = $this->_model->getTrip($tripId);

        if (!$trip) {
            return false;
        }

        if ($trip->handle != $member->Username) {
            return false;
        }

        $page = new TripEditCreatePage(true);
        $page->member = $member;
        $page->vars = array(
            "trip-id" => $trip->trip_id,
            "trip-name" => $trip->trip_name,
            "trip-desc" => $trip->trip_descr
        );

        return $page;
    }

    public function deleteCallback(StdClass $args, ReadOnlyObject $action, ReadWriteObject $mem_redirect, ReadWriteObject $mem_resend) {
        $errors = array();
        $vars = $args->post;
        $member = $this->_model->getLoggedInMember();
        if (isset($vars['trip-yes'])) {
            $errors = $this->_model->deleteTrip($vars, $member);
        }
        if (!empty($errors)) {
            $mem_redirect->errors = $errors;
            return false;
        }
        return $this->router->url('trip_show', array('username' => $member->Username), false);
    }

    public function deleteTrip()
    {
        $member = $this->_model->getLoggedInMember();
        if (!$member) {
            return false;
        }
        $tripId = $this->route_vars['tripid'];
        $trip = $this->_model->getTrip($tripId);

        if (!$trip) {
            return false;
        }

        if ($trip->handle != $member->Username) {
            return false;
        }

        $page = new TripDeletePage(true);
        $page->member = $member;
        $page->vars = array(
            "trip-id" => $trip->trip_id,
            "trip-name" => $trip->trip_name,
            "trip-desc" => $trip->trip_descr
        );

        return $page;
    }
}
