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
        $User = APP_User::login();
        if ($User && $User->loggedIn()) {
            ob_start();
        	$this->_view->userbar();
            $str = ob_get_contents();
            ob_end_clean();
            $Page = PVars::getObj('page');
            $Page->content .= $str;
        }
        switch($request[1]) {
        	case 'create':
                if (!$User)
                    return false;
                ob_start();
                $this->_view->createForm();
                $str = ob_get_contents();
                ob_end_clean();
                $Page = PVars::getObj('page');
                $Page->content .= $str;
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
    }
    
    private function delTrip($tripId) {
		$callbackId = $this->delProcess();
		PPostHandler::clearVars($callbackId);
		
		ob_start();

		$this->_model->prepareEditData($tripId, $callbackId);
		$this->_view->delTrip($callbackId);
	
		$str = ob_get_contents();
		ob_end_clean();
		$Page = PVars::getObj('page');
		$Page->content .= $str;

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
		
		ob_start();

		$this->_model->prepareEditData($tripId, $callbackId);
		$this->_view->editTrip($callbackId);
	
		$str = ob_get_contents();
		ob_end_clean();
		$Page = PVars::getObj('page');
		$Page->content .= $str;

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
		ob_start();
		$trips = $this->_model->getTrips($handle);
		$trip_data = $this->_model->getTripData();
		$this->_view->displayTrips($trips, $trip_data);
		$str = ob_get_contents();
		ob_end_clean();
		$Page = PVars::getObj('page');
		$Page->content .= $str;
    }
    
    private function showAllTrips() {
    	$this->showTrips(false);
    }
    
    /*
    * Show a single trip (details, map, possibiltiy to reorder)
    */
    private function showTrip($tripid) {
		ob_start();
    	$trip = $this->_model->getTrip($tripid);
    	$trip_data = $this->_model->getTripData();
		$this->_view->displaySingleTrip($trip, $trip_data);
		$str = ob_get_contents();
		ob_end_clean();
		$Page = PVars::getObj('page');
		$Page->content .= $str;
    }
}
?>