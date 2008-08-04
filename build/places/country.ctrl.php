<?php
/**
* places controller
*
* @package places
* @author lupochen
* @copyright Copyright (c) 2007-2008, BeWelcome
* @license http://www.gnu.org/licenses/gpl.html GNU General Public License (GPL)
* @version $Id$
*/

class PlacesController extends PAppController {
	private $_model;
	private $_view;
	
	public function __construct() {
		parent::__construct();
		$this->_model = new Places();
		$this->_view =  new PlacesView($this->_model);
	}
	
	public function __destruct() {
		unset($this->_model);
		unset($this->_view);
	}
	
	/**
	* index is called when http request = ./places
	*/
	public function index() {
		$request = PRequest::get()->request;
		$User = APP_User::login();
        $subTab = 'places';
                
        // submenu
        ob_start();
        $this->_view->submenu($subTab);
        $str = ob_get_contents();
        $P = PVars::getObj('page');
        $P->subMenu .= $str;
        ob_end_clean();
        
        ob_start();
        $this->_view->customStyles();
        $str = ob_get_contents();
        $P = PVars::getObj('page');
        $P->addStyles .= $str;
        ob_end_clean(); 
        
        // teaser content
        ob_start();
        $countryinfo = '';
        $region = '';
        $city = '';
        $countrycode = '';
		if (isset($request[1]) && $request[1] && (substr($request[1], 0, 5) != '=page')) {
            $countrycode = $request[1]; 
            $countryinfo = $this->_model->getCountryInfo($request[1]);
        }
        if (isset($request[2]) && $request[2] && (substr($request[2], 0, 5) != '=page')) {$region = $request[2];}
        if (isset($request[3]) && $request[3] && (substr($request[3], 0, 5) != '=page')) {$city = $request[3];}
        $this->_view->teaserplaces($countrycode,$countryinfo,$region,$city);
        $str = ob_get_contents();
        $P = PVars::getObj('page');
        $P->teaserBar .= $str;
        ob_end_clean(); 
        
		if (isset($request[1]) && $request[1] && (substr($request[1], 0, 5) != '=page')) {
            if (isset($request[2]) && $request[2] && (substr($request[2], 0, 5) != '=page')) {
                if (isset($request[3]) && $request[3] && (substr($request[3], 0, 5) != '=page')) {
                            ob_start();
                			$cityinfo = $this->_model->getCityInfo($request[3],$request[2],$request[1]);
                			if (!$cityinfo) {
                				$this->_view->cityNotFound();
                			} else {
                				$members = $this->_model->getMembersOfCity($request[3],$request[2],$request[1]);
                				$this->_view->displayCityInfo($cityinfo, $members);
                			}
                            $Page = PVars::getObj('page');
                            $Page->content .= ob_get_contents();
                            ob_end_clean();                      
                } else {
                    switch ($request[2]) {
                        case 'about':
                        // main content    
                    	ob_start();
                        $this->_view->testpage();
                        $str = ob_get_contents();
                        ob_end_clean();
                        $P = PVars::getObj('page');
                        $P->content .= $str;
                        break;
                        
                        default:
                        ob_start();
            			$regioninfo = $this->_model->getRegionInfo($request[2],$request[1]);
            			if (!$regioninfo) {
            				$this->_view->regionNotFound();
            			} else {
                            $IdRegion = $regioninfo->idregion;
                        	$cities = $this->_model->getAllCities($IdRegion);
                            $this->_view->displayCities($cities,$request[2],$request[1]); // not yet
            				$members = $this->_model->getMembersOfRegion($request[2],$request[1]);
            				$this->_view->displayRegionInfo($regioninfo, $members);
            			}
                        $Page = PVars::getObj('page');
                        $Page->content .= ob_get_contents();
                        ob_end_clean();
                        break;
                    }    
            }
            } else {
                ob_start();
    			if (!$countryinfo) {
    				$this->_view->placesNotFound();
    			} else {
                	$regions = $this->_model->getAllRegions($request[1]);
                    $this->_view->displayRegions($regions,$request[1]);
    				$members = $this->_model->getMembersOfCountry($request[1]);
    				$this->_view->displayPlacesInfo($countryinfo, $members);
    			}
                $Page = PVars::getObj('page');
                $Page->content .= ob_get_contents();
                ob_end_clean();
            }
		} else {       
            // main content
            ob_start();
			$countries = $this->_model->getAllCountries();
			$this->_view->displayPlacesOverview($countries);
            $Page = PVars::getObj('page');
    		$Page->content .= ob_get_contents();
    		ob_end_clean();
		}
		

	}

    public function topMenu($currentTab) {
        $this->_view->topMenu($currentTab);
    }    
	

}
?>
