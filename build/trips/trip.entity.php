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
class Trip extends RoxEntityBase
{
    public $subtrips = null;
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
            $subtrips = $temp->FindByWhereMany('tripId = ' . $this->getPK());
        }
    }
}
