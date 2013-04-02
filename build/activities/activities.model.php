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

    public function checkEditCreateActivityVarsOk($args) {
        $post = $args->post;
        $errors = array();
        if (empty($post['activity-title'])) {
            $errors[] = 'ActivityTitleEmpty';
        }
        if (empty($post['activity-location'])) {
            $errors[] = 'ActivityLocationEmpty';
        }
        if (empty($post['activity-start-date'])) {
            $errors[] = 'ActivityDateStartEmpty';
        }
        if (empty($post['activity-start-time'])) {
            $errors[] = 'ActivityTimeStartEmpty';
        }
        if (empty($post['activity-end-date'])) {
            $errors[] = 'ActivityDateEndEmpty';
        }
        if (empty($post['activity-end-time'])) {
            $errors[] = 'ActivityTimeEndEmpty';
        }
        if (empty($post['activity-description'])) {
            $errors[] = 'ActivityDescriptionEmpty';
        }
        error_log(print_r($errors, true));
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
                $query = 'UPDATE activitiesattendees SET status=' . $status . ', comment=\'' . $post['activity-comment']
                    . '\' WHERE activityId = ' . $activity->id . ' AND attendeeId = ' . $this->getLoggedInMember()->id;
            } else {
                $query = 'INSERT INTO activitiesattendees SET status=' . $status . ', comment=\'' . $post['activity-comment']
                    . '\', activityId = ' . $activity->id . ', attendeeId = ' . $this->getLoggedInMember()->id;
            }
            error_log($query);
            $this->dao->query($query);
            return true;
        }
        if (isset($post['activity-leave'])) {
            $query = 'DELETE FROM activitiesattendees WHERE activityId = ' . $activity->id
                . ' AND attendeeId = ' . $this->getLoggedInMember()->id;
            error_log($query);
            $this->dao->query($query);
            return true;
        }
        if (isset($post['activity-cancel'])) {
            $query = 'UPDATE activitiesattendees SET status = 4 WHERE activityId = ' . $activity->id;
            error_log($query);
            $this->dao->query($query);
            $activity->status = 1;
            $activity->update();
        }
    }
    
    public function createActivity($args) {
    error_log(__FUNCTION__);
        $activity = new Activity();
        $activity->creator = $this->getLoggedInMember()->id;
        $activity->title = $args->post['activity-title'];
        $activity->address = $args->post['activity-address'];
        $activity->locationId = 2988507;
        $activity->dateTimeStart = $args->post['activity-start-date'] . " " . $args->post['activity-start-time'] . ":00";
        $activity->dateTimeEnd = $args->post['activity-end-date'] . " " . $args->post['activity-end-time'] . ":00";
        $activity->description = $args->post['activity-description'];
        $activity->public = isset($args->post['activity-public']);
        $organizer = array();
        $organizer[$activity->creator] = array ( "attendeeId" => $activity->creator, "organizer" => "1", "status" => "1");
        $activity->organizers = $organizer;
        $activity->insert();
    }

    public function updateActivity($args) {
        $activity = new Activity($args->post['id']);
        $activity->title = $args->post['activity-title'];
        $activity->address = $args->post['activity-address'];
        $activity->locationId = 2988507;
        $activity->dateTimeStart = $args->post['activity-start-date'] . " " . $args->post['activity-start-time'] . ":00";
        $activity->dateTimeEnd = $args->post['activity-end-date'] . " " . $args->post['activity-end-time'] . ":00";
        $activity->description = $args->post['activity-description'];
        $activity->public = isset($args->post['activity-public']);
        $activity->update();
    }
}
