<div class="w-100"><?php
if (count($this->activities) == 0) {
    if ($this->publicOnly) {
        echo '<p>' . $words->get('ActivitiesNoPublicPastActivities') . '</p>';
    } else {
        echo '<p>' . $words->get('ActivitiesNoPastActivities') . '</p>';
    }
} else {
    require_once('activitieslist.php');
}
?></div>