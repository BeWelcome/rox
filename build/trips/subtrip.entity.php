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
class SubTrip extends RoxEntityBase
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

    protected function loadEntity(array $data)
    {
        if ($status = parent::loadEntity($data))
        {
            $geo = new Geo($this->geonameId);
            $this->latitude = $geo->latitude;
            $this->longitude = $geo->longitude;
            if (!$this->departure || $this->departure == '0000-00-00' ) {
                $this->departure = $this->arrival;
            }
            $this->arrivalTS = strtotime($this->arrival);
            $this->departureTS = strtotime($this->departure);
        }
        return $status;
    }


    private function _getLocationName(Geo $location) {
        $lang = $_SESSION['lang'];
        $admin1 = $location->getParent();
        $country = $location->getCountry();
        $locationName = $location->getName($lang);
        if (!$admin1) {
            return false;
        }
        $admin1Name = $admin1->getName($lang);
        if (!$country) {
            return false;
        }
        $countryName = $country->getName($lang);
        $name = $locationName;
        if (!empty($admin1Name)) {
            $name .= ', ' . $admin1Name;
        }
        $name .= ', ' . $countryName;
        return $name;
    }

    public function getSubTripDetails() {
        // check if location exists if not let the caller know
        $location = $this->_entity_factory->create('Geo')->FindById($this->geonameId);
        if (!$location) {
            return false;
        }
        $vars = new StdClass;
        $vars->subTripId = $this->getPKValue();
        $vars->geonameId = $this->geonameId;
        $location = $this->_entity_factory->create('Geo')->FindById($this->geonameId);
        $vars->shortName = $location->getName($_SESSION['lang']);
        $name = $this->_getLocationName($location);
        if (!$name) {
            return false;
        }
        $vars->name = $name;
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
