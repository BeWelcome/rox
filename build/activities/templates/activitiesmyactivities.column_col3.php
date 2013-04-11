<div class="floatbox">
    <div class="float_right">
        <a class="bigbutton" href="activities/create"><span><?= $words->get('ActivityCreateNew'); ?></span></a>
    </div>
</div>
<div class="row>">
<?php 
if (isset($_SESSION['ActivityStatus'])) {
    echo '<div class="success">';
    $status = $_SESSION['ActivityStatus'];
    switch($status[0]) {
        case 'ActivityCreateSuccess':
            echo $words->get('ActivitiesSuccessCreate', $status[1]);
            break;  
        case 'ActivityUpdateSuccess':
            echo $words->get('ActivitiesSuccessUpdate', $status[1]);
            break;  
    }
    echo '</div>';
    unset($_SESSION['ActivityStatus']);
}
 
if (count($this->activities) == 0) {
    echo '<p>' . $words->get('ActivitiesNoMyActivities') . '</p>';
} else {
require_once('activitieslist.php');
}
?>
</div>