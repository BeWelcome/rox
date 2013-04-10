<div class="row>">
<?php 
print_r($this->activities);
if (count($this->activities) == 0) {
    if ($this->public) {
        echo '<p>' . $words->get('ActivitiesNoPublicPastActivities') . '</p>';
    } else {
        echo '<p>' . $words->get('ActivitiesNoPastActivities') . '</p>';
    }
} else {
    require_once('activitieslist.php');
}
?>
</div>