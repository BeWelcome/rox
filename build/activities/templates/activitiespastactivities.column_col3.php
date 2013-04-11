<div class="floatbox">
    <div class="float_right">
        <a class="bigbutton" href="activities/create"><span><?= $words->get('ActivityCreateNew'); ?></span></a>
    </div>
</div>
<div class="row>">
<?php 
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