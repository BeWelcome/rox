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

class PlacesController extends RoxControllerBase {
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
    public function index_1() {
        exit(0);
        $request = PRequest::get()->request;
        $User = APP_User::login();
        $subTab = 'places';

        // submenu
        ob_start();
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
        if (isset($request[3]) && $request[3] && (substr($request[3], 0, 5) != '=page')) {
            $region = $request[2];
        }
        if (isset($request[5]) && $request[5] && (substr($request[5], 0, 5) != '=page')) {
            $city = $request[4];
        }
        $this->_view->teaserplaces($countrycode,$countryinfo,$region,$city);
        $str = ob_get_contents();
        $P = PVars::getObj('page');
        $P->teaserBar .= $str;
        ob_end_clean();

        if (isset($request[1]) && $request[1] && (substr($request[1], 0, 5) != '=page')) {
            if (isset($request[3]) && $request[3] && (substr($request[3], 0, 5) != '=page')) {
                if (isset($request[5]) && $request[5] && (substr($request[5], 0, 5) != '=page')) {
                    // make citypage
                    ob_start();
                    $regioninfo = $this->_model->getRegionInfo($request[3], $request[1]);
                    $cityinfo = $this->_model->getCityInfo($request[5],$request[3],$request[1]);
                    if (!$cityinfo) {
                        $this->_view->placesNotFound($request[4]);
                    } else {
                        // calculate total number of members in this city
                        $cityinfo->memberCount = 0;
                        if (isset($regioninfo->idregion)){
                            $cities = $this->_model->getAllCities($request[3], $request[1]);
                            foreach ($cities as $city){
                                if ($city->city==$cityinfo->city){
                                    $cityinfo->memberCount = $city->NbMember;
                                    break;
                                }
                            }
                        }
                        // define collection of members that are visible
                        $members = $this->_model->getMembersOfCity($request[5],$request[3],$request[1]);
                        // make the page itself
                        $this->_view->displayPlaceInfo($cityinfo,$members,'city');
                    }
                    $Page = PVars::getObj('page');
                    $Page->content .= ob_get_contents();
                    ob_end_clean();
                } else {
                    ob_start();
                    $regioninfo = $this->_model->getRegionInfo($request[3], $request[1]);
                    if (!$regioninfo) {
                        $this->_view->placesNotFound($request[2]);
                    } else {
                        // make regionpage
                        $this->_view->cities = $this->_model->getAllCities($request[3], $request[1]);
                        // make list of cities
                        // incidentally this also calculates the total number of
                        // members in the region.
                        $members = $this->_model->getMembersOfRegion($request[3],$request[1]);
                        $this->_view->displayCities($request[1],$regioninfo,$members);
                    }
                    $Page = PVars::getObj('page');
                    $Page->content .= ob_get_contents();
                    ob_end_clean();
                }
            } else {
                ob_start();
                if (!$countryinfo) {
                    $this->_view->placesNotFound();
                } else {
                    // make country page
                    $this->_view->regions = $this->_model->getAllRegions($request[1]);
                    // members in the country
                    $members = $this->_model->getMembersOfCountry($request[1]);
                    // make the actual page as well as list of regions
                    // incidentally this also calculates the total number of
                    $this->_view->displayRegions($request[1],$countryinfo, $members);
                }
                $Page = PVars::getObj('page');
                $Page->content .= ob_get_contents();
                ob_end_clean();
            }
        } else {
            // places front page: countries per continent
            ob_start();
            $continents = $this->_model->getContinents();
            $countries = $this->_model->getAllCountries();
            $this->_view->displayCountries($countries, $continents);
            $Page = PVars::getObj('page');
            $Page->content .= ob_get_contents();
            ob_end_clean();
        }
    }

    /**
     * Shows a list of all countries together with number of members if any
     */
    public function countries() {
        $page = new CountriesPage();
        $page->continents = $this->_model->getContinents();
        $page->countries = $this->_model->getAllCountries();
        return $page;
    }

    public function country() {
        $page = new CountryPage();
        $page->pageNumber  = 1;
        if (isset($this->route_vars['page'])) {
            $page->pageNumber  = $this->route_vars['page'];
        }
        $countryCode = $this->route_vars['countrycode'];
        $page->regions = $this->_model->getAllRegions($countryCode);
        list($count, $members) = $this->_model->getMembersOfCountry($countryCode, $page->pageNumber);
        $page->count = $count;
        $page->members = $members;
        $page->countryName = $this->route_vars['countryname'];
        $page->countryCode = $countryCode;
        return $page;
    }

    public function region() {
        $page = new RegionPage();
        $page->pageNumber  = 1;
        if (isset($this->route_vars['page'])) {
            $page->pageNumber  = $this->route_vars['page'];
        }
        $countryCode = $this->route_vars['countrycode'];
        $regionCode = $this->route_vars['regioncode'];
        $page->cities = $this->_model->getAllCities($regionCode, $countryCode);
        list($count, $members) = $this->_model->getMembersOfRegion($regionCode,$countryCode, $page->pageNumber);
        $page->count = $count;
        $page->members = $members;
        $page->countryName = $this->route_vars['countryname'];
        $page->countryCode = $countryCode;
        $page->regionName = $this->route_vars['regionname'];
        $page->regionCode = $regionCode;
        return $page;
    }

    public function city() {
        $page = new CityPage();
        $page->pageNumber = 1;
        if (isset($this->route_vars['page'])) {
            $page->pageNumber = $this->route_vars['page'];
        }
        $cityCode = $this->route_vars['citycode'];
        $cityName = $this->route_vars['cityname'];
        list($count, $members)  = $this->_model->getMembersOfCity($cityCode, $cityName, $page->pageNumber);
        $page->count = $count;
        $page->members = $members;
        $page->countryName = $this->route_vars['countryname'];
        $page->countryCode = $this->route_vars['countrycode'];
        $page->regionName = $this->route_vars['regionname'];
        $page->regionCode = $this->route_vars['regioncode'];
        $page->cityName = $cityName;
        $page->cityCode = $cityCode;
        return $page;
    }

    public function topMenu($currentTab) {
        $this->_view->topMenu($currentTab);
    }
}
?>
