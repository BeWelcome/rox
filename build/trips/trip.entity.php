<?php
/*
Copyright (c) 2009 - 2015 BeVolunteer

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
     * represents a single trip including subtrips
     *
     * @author thisismeonmounteverest
     * @package    Apps
     * @subpackage Entities
     */
class Trip extends RoxEntityBase
{
    private $subtrips = array();
    protected $_table_name = 'trips';

    public function __construct($id = null)
    {
        parent::__construct();
        if ($id)
        {
            $this->findById(intval($id));
        }
    }

    public function FindTripsForMember(Member $member, $offset = 0, $limit = 0) {
        return $this->FindByWhereMany('idMember = ' . $member->id, $offset, $limit);
    }

    public function loadEntity(array $data)
    {
        if ($status = parent::loadEntity($data))
        {
            // get subtrips for this trip
            $temp = new Subtrip();
            $this->subtrips = $temp->FindByWhereMany('tripId = ' . $this->getPKValue());
        }
        return $status;
    }

    public function update() {
        // write data into the database
        // update subtrips as necessary
        parent::update();
        // Now take care of the subtrips
        foreach($this->subtrips as $subtrip) {
            if ($subtrip->getPKValue() == 0) {
                $subtrip->insert();
            } else {
                $subtrip->update();
            }
        }

    }

    public function insert() {
        parent::insert();
        // Now write all subtrips
        foreach($this->subtrips as $subtrip) {
            $subtrip->tripId = $this->getPKValue();
            $subtrip->insert();
        }
    }

    public function addSubTrip($subtripInfo) {
        if ($subtripInfo->id == 0) {
            $subtrip = new Subtrip();
        } else {
            $subtrip = $this->subtrips[$subtripInfo->id];
        }
        $subtrip->geonameId = $subtripInfo->geonameId;
        $subtrip->arrival = $subtripInfo->arrival;
        $subtrip->departure = $subtripInfo->departure;
        $subtrip->options = $subtripInfo->options;
        if ($subtripInfo->id == 0) {
            $this->subtrips[] = $subtrip;
        } else {
            $this->subtrips[$subtripInfo->id] = $subtrip;
        }
    }

    public function getPostVariables() {
        $vars = array();
        $vars['trip-id'] = $this->getPKValue();
        $vars['trip-name'] = $this->name;
        $vars['trip-description'] = $this->description;
        $vars['trip-count'] = $this->countOfTravellers;
        $vars['trip-additional-info'] = $this->additionalInfo;
        $geonameIds = $arrivals = $departures = $options = array();
        $locations = array();
        foreach($this->subtrips as $subtrip) {
            $subtripDetails = $subtrip->getSubtripDetails();
            $locations[] = $subtripDetails;
        }
        $vars['locations'] = $locations;
        return $vars;
    }
}
