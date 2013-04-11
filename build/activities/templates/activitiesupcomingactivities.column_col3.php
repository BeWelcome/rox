<div class="floatbox">
    <div class="float_right">
        <a class="bigbutton" href="activities/create"><span><?= $words->get('ActivityCreateNew'); ?></span></a>
    </div>
</div>
<div class="row>">
<?php
if (count($this->activities) == 0) {
?>
<p><?php 
if ($this->publicOnly) {
    echo $words->get('ActivitiesNoPublicUpcoming');
} else {
    echo $words->get('ActivitiesNoUpcoming');
}
?></p>
<?php 
} else {
    require_once('activitieslist.php');
}
?>
</div>