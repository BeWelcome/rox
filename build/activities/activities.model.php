<?php
/**
 * Events model class.
 *
 * @author shevek
 */
class ActivitiesModel extends RoxModelBase
{
    /**
     * Default constructor.
     */
    public function __construct() {
        parent::__construct();
    }

    public function getActivities($onlyPublic) {
        $temp = $this->CreateEntity('Activity');
        if ($onlyPublic) {
            $all = $temp->FindByWhereMany('public = 1 AND (dateTimeStart >= NOW() OR dateTimeEnd >= NOW())'
                . ' AND status = 0 AND ORDER BY dateTimeStart');
        } else {
            $all = $temp->FindByWhereMany('(dateTimeStart >= NOW() OR dateTimeEnd >= NOW())'
                . ' AND status = 0 ORDER BY dateTimeStart');
        }
        return $all;
    }

    public function getMyActivities() {
        $all = $this->CreateEntity('Activity')->getActivitiesForMember($this->getLoggedInMember());
        error_log("Activities: " . count($all));
        return $all;
    }

    public function getPastActivities($onlyPublic) {
        $temp = $this->CreateEntity('Activity');
        if ($onlyPublic) {
            $all = $temp->FindByWhereMany('public = 1 AND (dateTimeStart < NOW() AND dateTimeEnd < NOW())'
                . ' AND ORDER BY dateTimeStart');
        } else {
            $all = $temp->FindByWhereMany('(dateTimeStart < NOW() AND dateTimeEnd < NOW())'
                . ' ORDER BY dateTimeStart');
        }
        return $all;
    }

    public function checkEditCreateActivityVarsOk($args) {
        $errors = array();
        $post = $args->post;
        $startdate = $enddate = '';
        if (empty($post['activity-title'])) {
            $errors[] = 'ActivityTitleEmpty';
        }
        if (empty($post['activity-location'])) {
            $errors[] = 'ActivityLocationEmpty';
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
        }
        return $errors;
    }

    public function joinLeaveCancelActivity($post) {
        $status = 0;
        $activity = new Activity($post['activity-id']);
        if (isset($post['activity-yes'])) {
            $status = 1;
        }
        if (isset($post['activity-maybe'])) {
            $status = 2;
        }
        if (isset($post['activity-no'])) {
            $status = 3;
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
        if (isset($post['activity-leave'])) {
            $query = 'DELETE FROM activitiesattendees WHERE activityId = ' . $activity->id
                . ' AND attendeeId = ' . $this->getLoggedInMember()->id;
            $this->dao->query($query);
            return true;
        }
        if (isset($post['activity-cancel'])) {
            $query = 'UPDATE activitiesattendees SET status = 4 WHERE activityId = ' . $activity->id;
            $this->dao->query($query);
            $activity->status = 1;
            $activity->update();
        }
    }
    
    public function createActivity($args) {
        $activity = new Activity();
        $activity->creator = $this->getLoggedInMember()->id;
        $activity->title = $args->post['activity-title'];
        $activity->address = $args->post['activity-address'];
        $activity->locationId = $args->post['activity-location-id'];
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
    }

    public function updateActivity($args) {
        $activity = new Activity($args->post['activity-id']);
        $activity->title = $args->post['activity-title'];
        $activity->address = $args->post['activity-address'];
        $activity->locationId = $args->post['activity-location-id'];
        $startdate = strtotime($args->post['activity-start-date']);
        $activity->dateTimeStart = date('Y-m-d H:i:s', $startdate);
        $enddate = strtotime($args->post['activity-end-date']);
        $activity->dateTimeEnd = date('Y-m-d H:i:s', $enddate);;
        $activity->description = $args->post['activity-description'];
        $activity->public = isset($args->post['activity-public']);
        $activity->update();
    }

    public function checkSearchActivitiesVarsOk($args) {
        $errors = array();
        $post = $args->post;
        error_log(print_r($post, true));
        if (empty($post['activity-keyword'])) {
            $errors[] = 'ActivitiesKeywordEmpty';
        }
        return $errors;
    }
}
