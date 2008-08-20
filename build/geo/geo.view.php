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
	    return $out;
    }
    
    /**
    * Generate a list of the found locations
    * @param locations The places to display
    * @return HTML-List of the locations
    */
    public function generateLocationOverview($locations)
    {
    	$words = new MOD_words();
        $out = '';
        $add_out = '';
        if ($locations) {
        	$out = '<p class="desc">'.$words->get('Geo_hint_click_location').'</p>';
            $out .= '<ol id="locations">';
            $dohide = '';
            $add_out = '';
            $ii = 0;
            foreach ($locations as $location) {
                if (isset($location['fclName'])) {
                    // hide all results above 10
                    if ($ii++ == 10) {
                        $dohide = 'style="display:none" class="hidden"';
                        $out .= '<p style="padding: 1em 0; clear:both">We found even more results. You want to <a id="showAllResults" href="#">display them?</a></p>';
                        $add_out = '
                            <script>
                                $(\'showAllResults\').onclick = showAllResults;
                                function showAllResults () {
                                    $$(\'li.hidden\').invoke(\'toggle\');
                                    return false;
                                }
                            </script>
                        ';
                    }
                    $out .= '<li id="li_'.$location['geonameId'].'" '.$dohide.' onclick="javascript: setMap(\''.$location['geonameId'].'\', \''.$location['lat'].'\',  \''.$location['lng'].'\', \''.$location['zoom'].'\', \''.$location['name'].'\', \''.$location['countryName'].'\', \''.$location['countryCode'].'\', \''.$location['fcodeName'].'\'); return false;"><a id="href_'.$location['geonameId'].'" onclick="javascript: setMap(\''.$location['geonameId'].'\', \''.$location['lat'].'\',  \''.$location['lng'].'\', \''.$location['zoom'].'\', \''.$location['name'].'\', \''.$location['countryName'].'\', \''.$location['countryCode'].'\', \''.$location['fcodeName'].'\'); return false;">
                            '.$location['name'].'<br />
                            <img src="images/icons/flags/'.strtolower($location['countryCode']).'.png" alt="'.$location['countryName'].'"> <span class="small">'.$location['countryName'];
                    if (isset($location['fcodeName'])) {
                        // $out .= ' ('.$location['fcodeName'].') -'.$location['fclName'];
                    }
    				if (isset($location['adminName1'])) {
    					$out .= ' / '.$location['adminName1'];
    				}
                    $out .= '</span></a></li>';
                }
            }
            $out .= '</ol>';
            $out .= $add_out;
            if ($ii == 0) return 'We couldnt find your location!';
            return $out;
        } else
        return 'We couldnt find your location!';
    }
    
}
?>