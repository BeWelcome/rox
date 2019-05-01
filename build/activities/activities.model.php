<?php
/**
 * Activities model class.
 *
 * @author shevek
 */
class ActivitiesModel extends RoxModelBase
{
    // Limits for textareas
    const ACTIVITY_ADDRESS_LIMIT = 320;
    const ACTIVITY_DESCRIPTION_LIMIT = 65535;

    /**
     * Default constructor.
     */
    public function __construct() {
        parent::__construct();
    }

    protected function getUpcomingQuery($onlyPublic) {
        $sql = '';
        if ($onlyPublic) {
            $sql .= 'public = 1 AND ';
        }
        $sql .= '(dateTimeStart >= NOW() OR dateTimeEnd >= NOW())'
                . ' AND status = 0';
        return $sql;
    }

    public function getUpcomingActivitiesCount($onlyPublic) {
        $temp = $this->CreateEntity('Activity');
        $count = $temp->countWhere($this->getUpcomingQuery($onlyPublic));
        return $count;
    }

    public function getUpcomingActivities($onlyPublic, $pageno, $items) {
        $temp = $this->CreateEntity('Activity');
        $temp->sql_order = "dateTimeStart";
        $query = $this->getUpcomingQuery($onlyPublic);
        $all = $temp->FindByWhereMany($query, $pageno * $items, $items);
        return $all;
    }

    public function getMyActivitiesCount() {
        $all = $this->CreateEntity('Activity')->getActivitiesForMemberCount($this->getLoggedInMember());
        return $all;
    }

    public function getMyActivities($pageno, $items) {
        $all = $this->CreateEntity('Activity')->getActivitiesForMember($this->getLoggedInMember(), $pageno, $items);
        return $all;
    }

    protected function getPastQuery($onlyPublic) {
        $sql = '';
        if ($onlyPublic) {
            $sql .= 'public = 1 AND ';
        }
        $sql .= '(dateTimeStart < NOW() AND dateTimeEnd < NOW())';
        return $sql;
    }

    public function getPastActivitiesCount($onlyPublic) {
        $temp = $this->CreateEntity('Activity');
        $count = $temp->countWhere($this->getPastQuery($onlyPublic));
        return $count;
    }

    public function getPastActivities($onlyPublic, $pageno, $items) {
        $temp = $this->CreateEntity('Activity');
        $temp->sql_order = 'dateTimeEnd DESC';
        $all = $temp->FindByWhereMany($this->getPastQuery($onlyPublic), $pageno * $items, $items);
        return $all;
    }

    public function setRadius($args) {
        $radius = $args->post['activity-radius'];
        $query = "
            SELECT
                id
            FROM
                preferences
            WHERE
                CodeName = 'ActivitiesNearMeRadius'
            LIMIT 1
            ";
        $row = $this->dao->query($query);
        $radiusPref = $row->fetch(PDB::FETCH_OBJ);
        if ($radiusPref === false) {
            return false;
        }

        $membersModel = new MembersModel();
        $membersModel->set_preference($this->getLoggedInMember()->id, $radiusPref->id, $radius);
    }
    
    public function getRadius() {
        $layoutbits = new MOD_layoutbits();
        $loggedInMember = $this->getLoggedInMember();
        return intval($layoutbits->getPreference("ActivitiesNearMeRadius", $loggedInMember->id));
    }
    
    protected function getNearMeQuery($distance, $count = false) {
        // get latitude and longitude for location of logged in member
        $loggedInMember = $this->getLoggedInMember();
        $query = "SELECT latitude, longitude FROM geonames WHERE geonameid = " . $loggedInMember->IdCity;
        $sql = $this->dao->query($query);
        if (!$sql) {
            return false;
        }
        $row = $sql->fetch(PDB::FETCH_OBJ);

        // calculate rectangle around place with given distance
        $lat = deg2rad(doubleval($row->latitude));
        $long = deg2rad(doubleval($row->longitude));

        $longne = rad2deg(($distance + 6378 * $long) / 6378);
        $longsw = rad2deg((6378 * $long - $distance) / 6378);

        $radiusAtLatitude = 6378 * cos($lat);
        $latne = rad2deg(($distance + $radiusAtLatitude * $lat) / $radiusAtLatitude);
        $latsw = rad2deg(($radiusAtLatitude * $lat - $distance) / $radiusAtLatitude);
        if ($latne < $latsw) {
            $tmp = $latne;
            $latne = $latsw;
            $latsw = $tmp;
        }
        if ($longne < $longsw) {
            $tmp = $longne;
            $longne = $longsw;
            $longsw = $tmp;
        }
        if ($count) {
            $query = "SELECT COUNT(*) AS count ";
        } else {
            $query = "SELECT a.* ";
        }
        $query .= "FROM activities AS a, geonames AS g WHERE a.locationId = g.geonameid ";
        $query .= 'AND g.latitude < ' . $latne . '
            AND g.latitude > ' . $latsw . '
            AND g.longitude < ' . $longne . '
            AND g.longitude > ' . $longsw . '
            AND (a.dateTimeStart >= NOW() OR a.dateTimeEnd >= NOW())
            AND a.status = 0';
        return $query;
    }

    public function getActivitiesNearMeCount($distance) {
        $query = $this->getNearMeQuery($distance, true);
        $sql = $this->dao->query($query);
        if (!$sql) {
            return 0;
        }
        $row = $sql->fetch(PDB::FETCH_OBJ);
        return $row->count;
    }

    public function getActivitiesNearMe($distance, $pageno, $items) {
        $temp = $this->CreateEntity('Activity');
        $temp->sql_order = "dateTimeStart";
        $query = $this->getNearMeQuery($distance);
        $query .= " ORDER BY dateTimeStart LIMIT " . $items . " OFFSET " . ($pageno * $items);
        $all = $temp->FindBySQLMany($query);
        return $all;
    }

    public function searchActivitiesCount($onlyPublic, $keyword) {
        $temp = $this->CreateEntity('Activity');
        return $temp->searchActivitiesCount($onlyPublic, $keyword);
    }

    public function searchActivities($onlyPublic, $keyword, $pageno, $items) {
        $temp = $this->CreateEntity('Activity');
        return $temp->searchActivities($onlyPublic, $keyword, $pageno, $items);
    }

    /**
     * checks if the entered data on the activity-create-form is correct
     *
     * There is one situation however where we can't determine if the form data is correct
     * This occurs when the user selects a location that isn't yet in the geonames_cache table
     * As the location will only be added after the form is validated there is no way to check if
     * the content of activity-location and activity-location-id match.
     *
     * To ensure that the user at least used the search button to set the activity-location-id one
     * check is added. If activity-location-id is still 0, the content of activity-location must match
     * the city of the member.
     *
     * @return array with the found problems
     */
    public function checkEditCreateActivityVarsOk($args) {
        $errors = array();
        $post = $args->post;
        $startdate = $enddate = '';
        if (empty($post['activity-title'])) {
            $errors[] = 'ActivityTitleEmpty';
        }
        if (empty($post['activity-location'])) {
            $errors[] = 'ActivityLocationEmpty';
        } else {
            if ($post['activity-location_geoname_id'] == 0) {
                $geo = $this->CreateEntity('Geo', $this->getLoggedInMember()->IdCity);
                $defaultLocation = $geo->name . ", " . $geo->getCountry()->name;
                if ($defaultLocation != $post['activity-location']) {
                    $errors[] = 'ActivityLocationAmbiguous';
                }
            }
        }
        if (!empty($post['activity-address'])) {
            if (strlen($post['activity-address']) > self::ACTIVITY_ADDRESS_LIMIT) {
                $errors[] = 'ActivityAddressTooLong###' . self::ACTIVITY_ADDRESS_LIMIT . '###';
            }
        }
        if (empty($post['activity-start-date'])) {
            $errors[] = 'ActivityDateStartEmpty';
        } else {
            $startdate = strtotime($post['activity-start-date']);
            if ($startdate === false) {
                $errors[] = 'ActivityWrongStartDateFormat';
            }
        }
        if (empty($post['activity-end-date'])) {
            $errors[] = 'ActivityDateEndEmpty';
        } else {
            $enddate = strtotime($post['activity-end-date']);
            if ($enddate === false) {
                $errors[] = 'ActivityWrongEndDateFormat';
            }
        }
        if ($enddate < $startdate) {
            $errors[] = 'ActivityEndBeforeStart';
        }
        if (empty($post['activity-description'])) {
            $errors[] = 'ActivityDescriptionEmpty';
        } else {
            if (strlen($post['activity-description']) > self::ACTIVITY_DESCRIPTION_LIMIT) {
                $errors[] = 'ActivityDescriptionTooLong###' . self::ACTIVITY_DESCRIPTION_LIMIT . '###';
            }
        }
        return $errors;
    }

    public function checkJoinLeaveActivityVarsOk($args) {
        $errors = array();
        $post = $args->post;
        $status = 0;
        if (isset($post['activity-status'])) {
            switch ($post['activity-status']) {
                case 'activity-yes':
                    $status = 1;
                    break;
                case 'activity-maybe':
                    $status = 2;
                    break;
                case 'activity-no':
                    $status = 3;
                    break;
            }
        }
        if ($status == 0) {
            if (empty($post['activity-comment'])) {
                $errors[] = 'ActivitiesNoStatusSelectedComment';
            } else {
                $errors[] = 'ActivitiesNoStatusSelected';
            }
        }
        return $errors;
    }

    public function joinLeaveActivity($post) {
        $activity = new Activity($post['activity-id']);
        // First check if the member wants to leave the activity
        if (isset($post['activity-leave'])) {
            $query = 'DELETE FROM activitiesattendees WHERE activityId = ' . $activity->id
                . ' AND attendeeId = ' . $this->getLoggedInMember()->id;
            $this->dao->query($query);
            return true;
        }
        $status = 0;
        if (isset($post['activity-status'])) {
            switch ($post['activity-status']) {
                case 'activity-yes':
                    $status = 1;
                    break;
                case 'activity-maybe':
                    $status = 2;
                    break;
                case 'activity-no':
                    $status = 3;
                    break;
            }
        }
        if ($status != 0) {
            if (in_array($this->getLoggedInMember()->id, array_keys($activity->attendees))) {
                $query = 'UPDATE activitiesattendees SET status=' . $status . ', comment=\'' . $this->dao->escape($post['activity-comment'])
                    . '\' WHERE activityId = ' . $activity->id . ' AND attendeeId = ' . $this->getLoggedInMember()->id;
            } else {
                $query = 'INSERT INTO activitiesattendees SET status=' . $status . ', comment=\'' . $this->dao->escape($post['activity-comment'])
                    . '\', activityId = ' . $activity->id . ', attendeeId = ' . $this->getLoggedInMember()->id;
            }
            $this->dao->query($query);
            return true;
        }
    }

    public function cancelUncancelActivity($post) {
        $activity = new Activity($post['activity-id']);
        if (isset($post['activity-cancel'])) {
            // Check if currently logged in member is an organizer of the meeting
            if (in_array($this->getLoggedInMember()->id, array_keys($activity->organizers))) {
                $query = 'UPDATE activitiesattendees SET status = 4 WHERE activityId = ' . $activity->id;
                $this->dao->query($query);
                $activity->status = 1;
                $activity->update();
                return true;
            } else {
                return false;
            }
        }
        if (isset($post['activity-uncancel'])) {
            // Check if currently logged in member is an organizer of the meeting
            if (in_array($this->getLoggedInMember()->id, array_keys($activity->organizers))) {
                $query = 'UPDATE activitiesattendees SET status = 1 WHERE activityId = ' . $activity->id;
                $this->dao->query($query);
                $activity->status = 0;
                $activity->update();
                return true;
            } else {
                return false;
            }
        }

    }

    public function createActivity($args) {
        // First add geo location to geonames_cache if it doesn't exist yet
        $locationId = $args->post['activity-location_geoname_id'];
        if ($locationId != 0) {
        } else {
            $locationId = $this->getLoggedInMember()->IdCity;
        }
        $activity = new Activity();
        $activity->creator = $this->getLoggedInMember()->id;
        $activity->title = $args->post['activity-title'];
        $activity->address = $args->post['activity-address'];
        $activity->locationId = $locationId;
        $startdate = strtotime($args->post['activity-start-date']);
        $activity->dateTimeStart = date('Y-m-d H:i:s', $startdate);
        $enddate = strtotime($args->post['activity-end-date']);
        $activity->dateTimeEnd = date('Y-m-d H:i:s', $enddate);;
        $activity->description = $args->post['activity-description'];
        $activity->public = isset($args->post['activity-public']);
        $organizer = array();
        $organizer[$activity->creator] = array ( "attendeeId" => $activity->creator, "organizer" => "1", "status" => "1");
        $activity->organizers = $organizer;
        $activity->insert();
        return $activity;
    }

    public function updateActivity($args) {
        // First add geo location to geonames_cache if it doesn't exist yet
        $locationId = $args->post['activity-location_geoname_id'];
        if ($locationId != 0) {
        } else {
            $locationId = $this->getLoggedInMember()->IdCity;
        }
        $activity = new Activity($args->post['activity-id']);
        $activity->title = $args->post['activity-title'];
        $activity->address = $args->post['activity-address'];
        $activity->locationId = $locationId;
        $startdate = strtotime($args->post['activity-start-date']);
        $activity->dateTimeStart = date('Y-m-d H:i:s', $startdate);
        $enddate = strtotime($args->post['activity-end-date']);
        $activity->dateTimeEnd = date('Y-m-d H:i:s', $enddate);;
        $activity->description = $args->post['activity-description'];
        $activity->public = isset($args->post['activity-public']);
        $activity->update();
        return $activity;
    }

    public function checkSearchActivitiesVarsOk($args) {
        $errors = array();
        $post = $args->post;
        if (empty($post['activity-keyword'])) {
            $errors[] = 'ActivitiesKeywordEmpty';
        }
        return $errors;
    }
}
