<div class="row">
    <div class="col-12">
<?php
if (count($this->activities) == 0) {
?>
<p><?php 
    echo $words->get('ActivitiesNoUpcomingOnline');
?></p>
    <?php
} else {
?>
<?php
        require_once('activitieslist.php');
}
?>
    </div>
</div>