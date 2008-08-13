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
            
    public function __construct(Geo $model) {
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
    public function generateLocationOverview($locations,$type)
    {
    	$words = new MOD_words();
        $out = '';
        $add_out = '';
        if ($locations) {
        	$out = '<p class="desc">'.$words->get('Geo_hint_click_location').'</p>';
            $out .= '<ol id="locations">';
            $different = 0;
            foreach ($locations as $location) {
                $add_out .= '<li id="li_'.$location['geonameId'].'"><a id="href_'.$location['geonameId'].'" onclick="javascript: setMap(\''.$location['geonameId'].'\', \''.$location['lat'].'\',  \''.$location['lng'].'\', \''.$location['zoom'].'\', \''.$location['name'].'\', \''.$location['countryName'].'\', \''.$location['countryCode'].'\', \''.$location['fcodeName'].'\'); return false;">'.$location['name'].', '.$location['countryName'];
                if (isset($location['fcodeName'])) {
//                    $add_out .= ' ('.$location['fcodeName'].') -'.$location['fclName'];
                }
				if (isset($location['adminName1'])) {
					$add_out .= ' / '.$location['adminName1'];
				}
				
                $add_out .= '</a></li>';
                 if ($location['fclName'] == $type) {
                    $different = 0;
                    $out .= $add_out;
                }
                $add_out = '';
            }
            $out .= '</ol>';
            if ($different != 0) return 'We couldnt find your location!';
            return $out;
        } else
        return 'We couldnt find your location!';
    }
    
}
?>