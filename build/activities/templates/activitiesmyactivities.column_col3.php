<div class="row">
    <div class="col-12">
<?php
if ($this->session->has( 'ActivityStatus' )) {
    echo '<div class="success">';
    $status = $this->session->get('ActivityStatus');
    switch($status[0]) {
        case 'ActivityCreateSuccess':
            echo $words->get('ActivitiesSuccessCreate', $status[1]);
            break;
        case 'ActivityUpdateSuccess':
            echo $words->get('ActivitiesSuccessUpdate', $status[1]);
            break;
    }
    echo '</div>';
    $this->session->remove('ActivityStatus');
}

if (count($this->activities) == 0) {
    echo '<p>' . $words->get('ActivitiesNoMyActivities') . '</p>';
} else {
require_once('activitieslist.php');
}
?>
</div>
</div>
