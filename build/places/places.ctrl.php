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

    public function __construct() {
        parent::__construct();
        $this->_model = new Places();
    }

    public function __destruct() {
        unset($this->_model);
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
        $loggedInMember = $this->_model->getLoggedInMember();
        $page = new CountryPage();
        $page->pageNumber  = 1;
        if (isset($this->route_vars['page'])) {
            $page->pageNumber  = $this->route_vars['page'];
        }
        $countryCode = $this->route_vars['countrycode'];
        $page->regions = $this->_model->getAllRegions($countryCode);
        list($memberCount, $totalMemberCount, $members) = $this->_model->getMembersOfCountry($countryCode, $page->pageNumber);
        $page->totalMemberCount = $totalMemberCount;
        $page->memberCount = $memberCount;
        $page->members = $members;
        $page->countryName = $this->route_vars['countryname'];
        $page->countryCode = $countryCode;
        return $page;
    }

    public function region() {
        $loggedInMember = $this->_model->getLoggedInMember();
        $page = new RegionPage();
        $page->pageNumber  = 1;
        if (isset($this->route_vars['page'])) {
            $page->pageNumber  = $this->route_vars['page'];
        }
        $countryCode = $this->route_vars['countrycode'];
        $regionCode = $this->route_vars['regioncode'];
        $page->cities = $this->_model->getAllCities($regionCode, $countryCode);
        list($memberCount, $totalMemberCount, $members) = $this->_model->getMembersOfRegion($regionCode,$countryCode, $page->pageNumber);
        $page->totalMemberCount = $totalMemberCount;
        $page->memberCount = $memberCount;
        $page->members = $members;
        $page->countryName = $this->route_vars['countryname'];
        $page->countryCode = $countryCode;
        $page->regionName = $this->route_vars['regionname'];
        $page->regionCode = $regionCode;
        return $page;
    }

    public function city() {
        $loggedInMember = $this->_model->getLoggedInMember();
        $page = new CityPage();
        $page->pageNumber = 1;
        if (isset($this->route_vars['page'])) {
            $page->pageNumber = $this->route_vars['page'];
        }
        $cityCode = $this->route_vars['citycode'];
        $cityName = $this->route_vars['cityname'];
        list($memberCount, $totalMemberCount, $members)  = $this->_model->getMembersOfCity($cityCode, $cityName, $page->pageNumber);
        $page->totalMemberCount = $totalMemberCount;
        $page->memberCount = $memberCount;
        $page->members = $members;
        $page->countryName = $this->route_vars['countryname'];
        $page->countryCode = $this->route_vars['countrycode'];
        $page->regionName = $this->route_vars['regionname'];
        $page->regionCode = $this->route_vars['regioncode'];
        $page->cityName = $cityName;
        $page->cityCode = $cityCode;
        return $page;
    }

}
?>
