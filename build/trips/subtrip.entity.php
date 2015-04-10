<?php
/*
Copyright (c) 2009 BeVolunteer

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
     * @author thisismeonmounteverest
     */

    /**
     * represents a single trip including subtrips
     *
     * @author thisismeonmounteverest
     * @package    Apps
     * @subpackage Entities
     */
class Subtrip extends RoxEntityBase
{

    protected $_table_name = 'subtrips';

    public function __construct($id = null)
    {
        parent::__construct();
        if ($id)
        {
            $this->findById(intval($id));
        }
    }

    private function _getLocationName(Geo $location) {
        $lang = $_SESSION['lang'];
        $admin1 = $location->getParent();
        $country = $location->getCountry();
        $locationName = $location->getName($lang);
        $admin1Name = $admin1->getName($lang);
        $countryName = $country->getName($lang);
        $name = $locationName;
        if (!empty($admin1Name)) {
            $name .= ', ' . $admin1Name;
        }
        $name .= ', ' . $countryName;
        return $name;
    }

    public function getSubtripDetails() {
        $vars = new StdClass;
        $vars->geonameId = $this->geonameId;
        $location = new Geo($this->geonameId);
        $vars->name = $this->_getLocationName($location);
        $vars->latitude = $location->latitude;
        $vars->longitude = $location->longitude;
        $vars->arrivalTS = strtotime($this->arrival);
        $vars->arrival = $this->arrival;
        $vars->departureTS = strtotime($this->departure);
        $vars->departure = $this->departure;
        $vars->options = $this->options;
        return $vars;
    }
}
