<div class="row>">
<?php 
if (count($this->activities) == 0) {
    echo '<p>' . $words->get('ActivitiesNoMyActivities') . '</p>';
} else {
require_once('activitieslist.php');
}
?>
</div>