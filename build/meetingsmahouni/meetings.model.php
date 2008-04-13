<?php/*
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
 * meetings model
 *
 * @package meetings
 * @author BeVolunteer - Toni (mahouni) (based on lemon-head`s groups application)
 */


class MeetingsModel extends PAppModel
{
    private $_meeting_list = 0;
    
    public function __construct()
    {
        parent::__construct();
    }
    
    public function findMeeting($meeting_id)
    {
        $meeting = new Meeting($meeting_id);
        if ($meeting->getData()) return $meeting;
        else return 0;
    }
    
    public function getMeetings()
    {
        if ($this->_meeting_list != 0) {
            // do nothing
        } else if (!$result = $this->dao->query(
            "
SELECT id, DATE_FORMAT( date, '%e.%c.%Y - %W' ) AS date_form, DATE_FORMAT( time, '%H:%i' ) AS time_form, name, (TO_DAYS(date)-TO_DAYS(NOW())) AS calkat, (max-confirmed) as maxstatus, (min-confirmed) as minstatus
FROM meetings
WHERE TO_DAYS(date)>=TO_DAYS(NOW()) ORDER BY date ASC , time ASC
            "
        )) {
            // db query went wrong
        } else {
            $this->_meeting_list = array();
            while ($meeting_data = $result->fetch(PDB::FETCH_OBJ)) {
                $this->_meeting_list[] = $meeting_data;
            }
        }
        return $this->_meeting_list;
    }

    public function getMyMeetings()
    {
        if (!isset($_SESSION['IdMember'])) {
            return array();
        } else {
            return $this->getMeetingsForMember($_SESSION['IdMember']);
        }
    }
        
    public function getMeetingsForMember($member_id)
    {
        if (!$result = $this->dao->query(
            "
SELECT meetings.*
FROM meetings, membersmeetings
WHERE membersmeetings.IdMeeting = meetings.id
AND membersmeetings.IdMember = $member_id
            "
        )) {
            // db query went wrong
            return 0;
        } else {
            $meeting_list = array();
            while ($meeting_data = $result->fetch(PDB::FETCH_OBJ)) {
                $meeting_list[] = $meeting_data;
            }
            return $meeting_list;
        }
    }

    /**
     * remember the last visited meetings, so 
     *
     * @param int $now_meeting_id id of the meeting you are visiting now
     */
    public function setMeetingVisit($meeting_id)
    {
        if (
            (!isset($_SESSION['my_meeting_visits'])) ||
            (!$meeting_visits = unserialize($_SESSION['my_meeting_visits'])) ||
            (!is_array($meeting_visits))
        ) {
            $meeting_visits = array();
        }
        $meeting_visits[$meeting_id] = microtime(true);
        
        // sort by value, while preserving the keys
        asort($meeting_visits);
        $_SESSION['my_meeting_visits'] = serialize(array_slice($meeting_visits, 0, 5));
        // unset($_SESSION['my_meeting_visits']);
    }
    
    public function getLastVisited()
    {
        if (
            (!isset($_SESSION['my_meeting_visits'])) ||
            (!$meeting_visits = unserialize($_SESSION['my_meeting_visits'])) ||
            (!is_array($meeting_visits))
        ) {
            return array();
        } else {
            $meetings = array();
            foreach($meeting_visits as $id => $time) {
                $meetings[] = $this->findMeeting($id);
            }
            return $meetings;
        } 
    }
}

/**
 * represents a single meeting
 *
 */
class Meeting extends PAppModel
{
    private $_meeting_id;
    private $_meeting_data = false;
    private $_meeting_memberships = 0;
    
    public function __construct($meeting_id)
    {
        parent::__construct();
        $this->_meeting_id = $meeting_id;
    }
    
    
    public function getData()
    {
        if ($this->_meeting_data) {
            // do nothing
        } else if (!$result = $this->dao->query(
            "
SELECT id, type, DATE_FORMAT( date, '%e.%c.%Y - %W' ) AS date_form, DATE_FORMAT( time, '%H:%i' ) AS time_form, geonameid, name, meetingpoint, contact, begin, location, description, picture, moreinfolink, min, max, confirmed, mostlikely, maybe, wantbutcant, (max-confirmed) AS maxstatus, (min-confirmed) AS minstatus
FROM meetings
WHERE id = $this->_meeting_id
            "
        )) {
            // db query went wrong
        } else if (!$meeting_data = $result->fetch(PDB::FETCH_OBJ)) {
            // meeting not found
        } else {
            $this->_meeting_data = $meeting_data;
        }
        return $this->_meeting_data;
    }


    public function getMembers()
    {
        return $this->getMemberships(30);
    }
        
    
    public function getMemberships($max_count)
    {
        $members = array();
        
        // TODO: check the $max_count argument
        if ($this->_meeting_memberships != 0) {
            // nothing to be done
        } else if (!$result = $this->dao->query(
            "
SELECT members.Username, membersmeetings.*
FROM membersmeetings, members
WHERE membersmeetings.IdMeeting = $this->_meeting_id
AND members.id = membersmeetings.IdMember
            "
        )) {
            // something went wrong with db query
        } else {
            $memberships = array();
            while ($member = $result->fetch(PDB::FETCH_OBJ)) {
                // TODO: check for $max_count
                $memberships[] = $member;
            }
            $this->_meeting_memberships = $memberships;
        }
        return $this->_meeting_memberships;
    }

    public function isMember($member_id) {
        $meeting_id = $this->getData()->id;
        if (!$result = $this->dao->query(
            "
SELECT *
FROM membersmeetings
WHERE IdMeeting = $meeting_id
AND IdMember = $member_id
            "
        )) {
            return false;
        } else if (!$member = $result->fetch(PDB::FETCH_OBJ)) {
            return false;
        } else {
            return true;
        }
        
    }
}


?>
