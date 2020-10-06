<?php
/*

Copyright (c) 2007 BeVolunteer

This file is part of BW Rox.

BW Rox is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

BW Rox is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, see <http://www.gnu.org/licenses/> or 
write to the Free Software Foundation, Inc., 59 Temple Place - Suite 330, 
Boston, MA  02111-1307, USA.

*/
/**
 * geo view
 *
 * @package geo
 * @author Felix van Hove <fvanhove@gmx.de>
 */
class GeoView extends PAppView {
    
    private $_model;
            
    public function __construct(GeoModel $model) {
        $this->_model = $model;
    }
    
    /**
     * Called by index method of geo.ctrl.php, does the
     * include for the HTML.
     */
    public function displayCountries()
    {
        $countriesHTML = $this->getAllCountriesSelectOption();
        require 'templates/geo.php';
    }
    
    /**
     * Called by index method of geo.ctrl.php, does the
     * include for the HTML.
     */
    public function displayGeoSelector()
    {
        $countriesHTML = $this->getAllCountriesSelectOption();
        require 'templates/geo.php';
    }

    /**
     * @return select-option box with all the countries
     */
    private function getAllCountriesSelectOption() {
        $countries = MOD_geo::get()->getAllCountries();
        $out = "<select name=\"country\">\n";
        foreach ($countries as $countryId => $country) {
            $out .= '<option value="' . $countryId . '">'.
                    $country .
                    "</option>\n";
        }
        $out .= "</select>\n";
        $geonames = MOD_geonames::get();    // get the singleton instance
        $id = $geonames->getUpdate();
        return $out;
    }
    
    /**
    * Display the description records of specific location(s)
    */
    public function GeoDisplayLocation($name)
    {
        $words = new MOD_words();
		$data=$this->_model->loadLocation($name) ;
        require 'templates/displaylocation.php';
        return '';
	}
    
    /**
    * Generate a list of the found locations
    * @param locations The places to display
    * @return HTML-List of the locations
    */
    public function generateLocationOverview($locations, $activities = false)
    {
        $words = new MOD_words();
        $out = '';
        $add_out = '';
        if ($locations) {
            $out = '<p class="desc">'.$words->get('Geo_hint_click_location').'</p>';
            $out .= '<ol id="locations" class="clearfix plain-location">';
            $dohide = '';
            $add_out = '';
            $ii = 0;
            $hideAfter = 10;
            if ($activities) {
                $hideAfter = 15;
            }
            foreach ($locations as $location) {
                if (isset($location['name'])) {
                    if(!isset($location['countryCode'])) $location['countryCode'] = '';
                    if(!isset($location['countryName'])) $location['countryName'] = '';
                    // hide all results above 10
                    if ($ii++ == $hideAfter) {
                        $dohide = 'style="display:none" class="hidden-location"';
                        $out .= '<p style="padding: 1em 0; clear:both" id="moreHint">'.$words->get('Geo_results_foundmore','<a id="showAllResults" href="#">','</a>').'</p>';
                        $add_out = '
                            <script>
                                jQuery.noConflict();
                                jQuery(\'a#showAllResults\').on(\'click\',
                                    function () {
                                        jQuery(\'li.hidden-location\').show();
                                        jQuery(\'p#moreHint\').hide();
                                        return false;
                                    }
                                );
                            </script>
                        ';
                    }
                    if (isset($location['adminName1'])) {
                        $adminName1 = rawurlencode($location['adminName1']);
                    } else {
                        $adminName1 = '';
                    }
                    if ($activities) {
                        $onclick = "javascript: setActivityLocation('";
                    } else {
                        $onclick = "javascript: setMap('";
                    }
                    $onclick .= $location['geonameId']
                        . "', '"
                        . $location['lat']
                        . "', '"
                        . $location['lng']
                        . "', '"
                        . $location['zoom']
                        . "', '"
                        . rawurlencode($location['name'])
                        . "', '"
                        . rawurlencode($location['countryName'])
                        . "', '"
                        . $location['countryCode']
                        . "', '"
                        . $adminName1
                        . "'); return false;";
                    $out .= '<li id="li_' . $location['geonameId'] . '" '
                        . $dohide . ' onclick="' . $onclick . '">'
                        . '<a id="href_' . $location['geonameId'] . '">'
                        . $location['name']
                        . '<br /><img src="images/icons/flags/'
                        . strtolower($location['countryCode']) . '.png" alt="'
                        . $location['countryName'] . '"> <span class="small">'
                        . $location['countryName'];
                    if (isset($location['adminName1'])) {
                        $out .= ' / '.$location['adminName1'];
                    }
                    $out .= '</span></a></li>';
                }
            }
            $out .= '</ol>';
            $out .= $add_out;
            if ($ii == 0) {
                return '<p class="desc">' . $words->get('Geo_no_matches_found') . '</p>';
            } else {
                return $out;
            }
        } else {
            return '<p class="desc">' . $words->get('Geo_no_matches_found') . '</p>';
        }
    }



    /**
    * Generate a list of the found locations that works without javascript
    * @param locations The places to display
    * @return HTML-List of the locations
    */
    public function generateLocationOverviewNoJs($locations, $callbacktag)
    {
        $words = new MOD_words();
        $page_url = PVars::getObj('env')->baseuri . implode('/', PRequest::get()->request);
        $out = '';
        $add_out = '';
        if ($locations) {
            $out = '<p class="desc">'.$words->get('Geo_hint_click_location').'</p>';
            $out .= '<ol id="locations" class="plain">';
            $ii = 0;
            foreach ($locations as $location) {
                if (isset($location['name'])) {
                    $ii++;
                    if(!isset($location['countryCode'])) $location['countryCode'] = '';
                    if(!isset($location['countryName'])) $location['countryName'] = '';
                    // hide all results above 10
                    $out .= '<li id="li_'.$location['geonameId'].'">'.$location['name'].'<br />';
                            // <input type="radio" name="geolocation" value="'.$location['geonameId'].'//'.$location['name'].'" />';
                    $out .= '<img src="images/icons/flags/'.strtolower($location['countryCode']).'.png" alt="'.$location['countryName'].'"> <span class="small">'.$location['countryName'];
                    if (isset($location['fcodeName'])) {
                        // $out .= ' ('.$location['fcodeName'].') -'.$location['fclName'];
                    }
                    if (isset($location['adminName1'])) {
                        $out .= ' / '.$location['adminName1'];
                    }
                    $out .= '</span>';
                    $out .= '<form method="POST" action="'.$page_url.'">';
                    $out .= $callbacktag.'
                            <input type="hidden" name="geonameId" id="geonameId" value="';
                    $out .= isset($location['geonameId']) ? htmlentities($location['geonameId'], ENT_COMPAT, 'utf-8') : '';
                    $out .= '" />';
                    $out .= '<input type="hidden" name="latitude" id="latitude" value="';
                    $out .= isset($location['lat']) ? htmlentities($location['lat'], ENT_COMPAT, 'utf-8') : '';
                    $out .= '" />';
                    $out .= '<input type="hidden" name="longitude" id="longitude" value="';
                    $out .= isset($location['lng']) ? htmlentities($location['lng'], ENT_COMPAT, 'utf-8') : '';
                    $out .= '" />';
                    $out .= '<input type="hidden" name="geonamename" id="geonamename" value="';
                    $out .= isset($location['name']) ? htmlentities($location['name'], ENT_COMPAT, 'utf-8') : '';
                    $out .= '" />';
                    $out .= '<input type="hidden" name="countryname" id="countryname" value="';
                    $out .= isset($location['countryName']) ? htmlentities($location['countryName'], ENT_COMPAT, 'utf-8') : '';
                    $out .= '" />';
                    $out .= '<input type="hidden" name="geonamecountrycode" id="geonamecountrycode" value="';
                    $out .= isset($location['countryCode']) ? strtolower($location['countryCode']) : '';
                    $out .= '" />';
                    $out .= '<input type="hidden" name="admincode" id="admincode" value="';
                    $out .= isset($location['adminName1']) ? htmlentities($location['adminName1'], ENT_COMPAT, 'utf-8') : '';
                    $out .= '" />';
                    $out .= '<input type="submit" class="button" value="'.$words->get('Select').'" class="button" />';
                    $out .= '</form></li>';
                }
            }
            $out .= '</ol>';
            if ($ii == 0) return 'We couldnt find your location!';
            return $out;
        }
    return false;
    }
    
    public function admin() {
        $words = new MOD_words();
        require 'templates/geoadmin.php';
        $out = '';
        return $out;
    }
    
    
}
?>
