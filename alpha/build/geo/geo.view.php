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
        require TEMPLATE_DIR.'apps/geo/geo.php';
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
    
}
?>