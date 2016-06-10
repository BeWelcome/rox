<?php 
if ($this->_session->has( 'ActivityStatus' )) {
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
