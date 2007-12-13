<?php
/**
* country controller
*
* @package country
* @author The myTravelbook Team <http://www.sourceforge.net/projects/mytravelbook>
* @copyright Copyright (c) 2005-2006, myTravelbook Team
* @license http://www.gnu.org/licenses/gpl.html GNU General Public License (GPL)
* @version $Id$
*/

class CountryController extends PAppController {
	private $_model;
	private $_view;
	
	public function __construct() {
		parent::__construct();
		$this->_model = new Country();
		$this->_view =  new CountryView($this->_model);
	}
	
	public function __destruct() {
		unset($this->_model);
		unset($this->_view);
	}
	
	/**
	* index is called when http request = ./country
	*/
	public function index() {
		$request = PRequest::get()->request;
		$User = APP_User::login();
        $subTab = 'country';
                
        // submenu
        ob_start();
        $this->_view->submenu($subTab);
        $str = ob_get_contents();
        $P = PVars::getObj('page');
        $P->subMenu .= $str;
        ob_end_clean();
        
        // teaser content
        ob_start();
        $country = '';
        $region = '';
        $city = '';
        $countrycode = '';
		if (isset($request[1]) && $request[1]) {$countrycode = $request[1]; $country = $this->_model->getCountryName($request[1]);}
        if (isset($request[2]) && $request[2]) {$region = $request[2];}
        if (isset($request[3]) && $request[3]) {$city = $request[3];}
        $this->_view->teasercountry($countrycode,$country,$region,$city);
        $str = ob_get_contents();
        $P = PVars::getObj('page');
        $P->teaserBar .= $str;
        ob_end_clean(); 
        
		if (isset($request[1]) && $request[1]) {
            if (isset($request[2]) && $request[2]) {
                if (isset($request[3]) && $request[3]) {
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
                        	$cities = $this->_model->getAllCities($request[2],$request[1]); // not yet
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
    			$countryinfo = $this->_model->getCountryInfo($request[1]);
    			if (!$countryinfo) {
    				$this->_view->countryNotFound();
    			} else {
                	$regions = $this->_model->getAllRegions($request[1]);
                    $this->_view->displayRegions($regions,$request[1]);
    				$members = $this->_model->getMembersOfCountry($request[1]);
    				$this->_view->displayCountryInfo($countryinfo, $members);
    			}
                $Page = PVars::getObj('page');
                $Page->content .= ob_get_contents();
                ob_end_clean();
            }
		} else {       
            // main content
            ob_start();
			$countries = $this->_model->getAllCountries();
			$this->_view->displayCountryOverview($countries);
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
