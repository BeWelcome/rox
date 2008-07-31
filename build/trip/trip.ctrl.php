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
class TripController extends PAppController {
    private $_model;
    private $_view;
    
    public function __construct() {
        parent::__construct();
        $this->_model = new Trip();
        $this->_view  = new TripView($this->_model);
    }
    
    public function __destruct() {
        unset($this->_model);
        unset($this->_view);
    }
    
    public function index() 
    {
        // index is called when http request = ./trip
        $request = PRequest::get()->request;
        if (!isset($request[1]))
            $request[1] = '';

        // Enable ViewWrap for cleaner code
        $P = PVars::getObj('page');
        $vw = new ViewWrap($this->_view);
        
        $User = APP_User::login();
        
        // Show the teaser
        $this->showTeaser();
        
        // then include the col2-stylesheet
        $P->addStyles .= $vw->customStyles();
        
        switch($request[1]) {
        	case 'create':
                if (!$User)
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
				if (eregi('^[0-9]+$', $request[1])) {
					$this->showTrip($request[1]);
				} else {
	            	$this->showAllTrips();
	                break;
	            }
        }
        // Show the user functions in the sidebar
        $P->newBar .= $vw->userbar();
    }
    
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
			return $this->_model->delProcess($callbackId);
		} else {
			PPostHandler::setCallback($callbackId, __CLASS__, __METHOD__);
			return $callbackId;
		}
    }


    private function editTrip($tripId) {
		$callbackId = $this->editProcess();
		PPostHandler::clearVars($callbackId);
		
		$this->_model->prepareEditData($tripId, $callbackId);
        
        $P = PVars::getObj('page');
        $vw = new ViewWrap($this->_view);
        $P->content .= $vw->editTrip($callbackId);
        
		PPostHandler::clearVars($callbackId);
    }
    
    public function editProcess() {
		$callbackId = PFunctions::hex2base64(sha1(__METHOD__));
		
		if (PPostHandler::isHandling()) {
			return $this->_model->editProcess($callbackId);
		} else {
			PPostHandler::setCallback($callbackId, __CLASS__, __METHOD__);
			return $callbackId;
		}
    }
    
    private function reorder($items) {
    	// Validate the array
    	foreach ($items as &$item) {
    		$item = (int) $item;
    	}
    	$this->_model->reorderTripItems($items);
    }
    
    private function showMyTrips() {
        $User = APP_User::login();
        if ($User && $handle = $User->getHandle()) {
    		$this->showTrips($handle);
    	}
    }
    
    private function showTrips($handle) {
		$trips = $this->_model->getTrips($handle);
		$trip_data = $this->_model->getTripData();
        $P = PVars::getObj('page');
        $vw = new ViewWrap($this->_view);
        $P->teaserBar .= $vw->displayMap($trips, $trip_data);
        $P->content .= $vw->displayTrips($trips, $trip_data);
    }

    private function showMap($trip) {
        $P = PVars::getObj('page');
        $vw = new ViewWrap($this->_view);
//		$trips = $this->_model->getTrips($handle);
		$trip_data = $this->_model->getTripsMarkers();
        $P->teaserBar .= $vw->displayMap($trips = false, $trip_data = false);
    }

    private function showTeaser($trip = false) {
//		$trips = $this->_model->getTrips($handle);
//		$trip_data = $this->_model->getTripData();
        $P = PVars::getObj('page');
        $vw = new ViewWrap($this->_view);
        $P->teaserBar .= $vw->teaser($trip);
    }
    
    private function showAllTrips() {
    	$this->showTrips(false);
        //$this->showMap(false);
    }
    
    /*
    * Show a single trip (details, map, possibiltiy to reorder)
    */
    private function showTrip($tripid) {
    	$trip = $this->_model->getTrip($tripid);
    	$trip_data = $this->_model->getTripData();
        
        $P = PVars::getObj('page');
        $vw = new ViewWrap($this->_view);
        $P->newBar .= $vw->displaySingleTrip_Sidebar($trip, $trip_data);
        $P->content .= $vw->displaySingleTrip($trip, $trip_data);
    }
}
?>