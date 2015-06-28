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
    // @array SubTrip
    private $_subTrips = array();
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

    protected function loadEntity($data)
    {
        if ($status = parent::loadEntity($data))
        {
            $member = new Member($this->memberId);
            $this->username = $member->Username;
            // get subtrips for this trip
            $temp = new SubTrip();
            $subTrips = $temp->FindByWhereMany('tripId = ' . $this->getPKValue());

            $this->_subTrips = array();
            // \todo: The database contains some strange entries
            $arrival = null;
            $departure = null;
            foreach($subTrips as $subTrip) {
                $details = $subTrip->getSubTripDetails();
                if ($details) {
                    $this->_subTrips[$subTrip->id] = $subTrip;
                    if (($subTrip->arrivalTS <> 0) && (($arrival == null) || ($arrival > $subTrip->arrivalTS))) {
                        $arrival = $subTrip->arrivalTS;
                    }
                    if (($subTrip->departureTS <> 0) && (($departure == null) || ($departure < $subTrip->departureTS))) {
                        $departure = $subTrip->departureTS;
                    }
                }
            }
            $duration = date('Y-m-d', $arrival);
            if ($departure <> $arrival) {
                $duration .= " - " . date('Y-m-d', $departure);
            }
            $this->duration = $duration;
        }
        return $status;
    }

    public function update() {
        // write data into the database
        // update subtrips as necessary
        parent::update();
        // Now take care of the subtrips
        foreach($this->_subTrips as $subtrip) {
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
        foreach($this->_subTrips as $subtrip) {
            $subtrip->tripId = $this->getPKValue();
            $subtrip->insert();
        }
    }

    public function addSubTrip($subtripInfo) {
        if ($subtripInfo->subTripId == 0) {
            $subtrip = new SubTrip();
        } else {
            $subtrip = $this->subtrips[$subtripInfo->subTripId];
        }
        $subtrip->geonameId = $subtripInfo->geonameId;
        $subtrip->arrival = $subtripInfo->arrival;
        $subtrip->departure = $subtripInfo->departure;
        $subtrip->options = $subtripInfo->options;
        if ($subtripInfo->subTripId == 0) {
            $this->_subTrips[] = $subtrip;
        } else {
            $this->_subTrips[$subtripInfo->id] = $subtrip;
        }
    }

    public function getTripDetails() {
        $vars = array();
        $vars['trip-id'] = $this->getPKValue();
        $vars['trip-title'] = $this->title;
        $vars['trip-description'] = $this->description;
        $vars['trip-count'] = $this->countOfTravellers;
        $vars['trip-additional-info'] = $this->additionalInfo;
        $geonameIds = $arrivals = $departures = $options = array();
        $locations = array();
        foreach($this->_subTrips as $subtrip) {
            $subtripDetails = $subtrip->getSubTripDetails();
            if ($subtripDetails) {
                $locations[] = $subtripDetails;
            }
        }
        $vars['locations'] = $locations;
        return $vars;
    }

    public function getSubTrips() {
        return $this->_subTrips;
    }
}
