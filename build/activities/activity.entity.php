<?php

/**
 * represents a single activities
 *
 */
class Activity extends RoxEntityBase
{
    protected $_table_name = 'activities';

    public function __construct($activityId = false)
    {
        parent::__construct();
        if ($activityId)
        {
            $this->findById($activityId);
        }
    }

    /**
     * overloads RoxEntityBase::loadEntity to load related data
     *
     * @param array $data
     *
     * @access protected
     * @return bool
     */
    protected function loadEntity(array $data)
    {
        if ($status = parent::loadEntity($data))
        {
            // split date and time for start and end
            $startdatetime = strtotime($this->dateTimeStart);
            $this->dateStart = date('d.m.Y', $startdatetime);
            $this->timeStart = date('H:i', $startdatetime);
            $enddatetime = strtotime($this->dateTimeEnd);
            $this->dateEnd = date('d.m.Y', $enddatetime);
            $this->timeEnd = date('H:i', $enddatetime);
            // get organizers
            $query = "SELECT a.*, m.Username FROM activitiesattendees AS a, members AS m WHERE a.activityId = {$this->getPKValue()} AND a.organizer = 1 AND a.attendeeId = m.Id ORDER BY a.status, m.Username";
            if ($result = $this->dao->query($query)) {
                $organizers = array();
                while ($organizer = $result->fetch(PDB::FETCH_OBJ)) {
                    $organizers[$organizer->attendeeId] = $organizer;
                }
                $this->organizers = $organizers;
            }
            // get attendees
            $query = "SELECT a.*, m.Username FROM activitiesattendees AS a, members AS m WHERE a.activityId = {$this->getPKValue()} AND a.attendeeId = m.Id ORDER BY a.status, m.Username";
            if ($result = $this->dao->query($query)) {
                $attendees = array();
                while ($attendee = $result->fetch(PDB::FETCH_OBJ)) {
                    $attendees[$attendee->attendeeId] = $attendee;
                }
                $this->attendees = $attendees;
            }
            // location details
            $entityFactory = new RoxEntityFactory();
            $this->location = $entityFactory->create('Geo', $this->locationId);
        }
        return $status;
    }
    
    /**
     * overloads RoxEntityBase::insert
     *
     * @access public
     * @return int
     */
    public function insert()
    {
        $status = parent::insert();
        error_log("Status: [" . $status . "]");
        if ($status) {
            error_log("organizers: " . print_r($this->organizers, true) . "|");
            if (count($this->organizers) > 0) {
                // add organizers to activitiesattendees table
                foreach($this->organizers as $organizer) {
                    $query = "INSERT INTO activitiesattendees SET activityId = " . $this->id;
                    $query .= ", attendeeId=" . $organizer['attendeeId'];
                    $query .= ", organizer=1, status=1";
                    error_log($query);
                    $this->dao->query($query);
                }
            }
        }
        return $status;
    }
    
    /** 
     * get all activities for a member
     * 
     * @access public
     * @return list of ActivitiesBasePage
     */
    public function getActivitiesForMember(Member $member) {
        $activities = array();
        $query = "SELECT a.id AS id FROM activities AS a, activitiesattendees AS aa WHERE a.id = aa.activityId AND aa.attendeeId = " . $member->id;
        $result = $this->dao->query($query);
        if ($result) {
            $activityIds = array();
            while ($row = $result->fetch(PDB::FETCH_OBJ)) {
                $activityIds[] = $row->id;
            }
            if (count($activityIds)) {
                $activities = $this->findByWhereMany("id IN ('" . implode("','", $activityIds) . "')");
            }
        }
        return $activities;
    }
}