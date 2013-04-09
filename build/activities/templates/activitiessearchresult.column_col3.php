<?php
$formkit = $this->layoutkit->formkit;
$callbackTags = $formkit->setPostCallback('ActivitiesController', 'searchActivitiesCallback');

$errors = array();
if (isset($_SESSION['errors'])) {
    $errors = unserialize($_SESSION['errors']);
    unset($_SESSION['errors']);
}
$vars= array();
if (isset($_SESSION['vars'])) {
    $vars = unserialize($_SESSION['vars']);
    unset($_SESSION['vars']);
}
$activities= array();
if (isset($_SESSION['activities'])) {
    $activities = unserialize($_SESSION['activities']);
    unset($_SESSION['activities']);
}
$this->activities = $activities;
if (empty($vars)) {
    $vars['activity-keyword'] = '';
}
?><div class="row">
<div class="subcolumns">
    <div class="c50l">
        <div class="subcl">
            <form id="activities-search-box" method="post" >
            <?php echo $callbackTags; ?>
            <input type="text" name="activities-keyword" id="activities-keyword" value="<?php echo $vars['activity-keyword']; ?>" /><input type="submit" name="activities-search" value="<?php echo $words->getSilent('ActivitiesSearchButton'); ?>" /><?php echo $words->flushBuffer(); ?>
            </form>
        </div>
    </div>
    <div class="c50r">
        <div class="subcr float_right">
            <a class="bigbutton" href="activities/create"><span><?= $words->get('ActivityCreateNew'); ?></span></a>
        </div>
    </div>
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
?>
<table class='activitieslist'>
<tr>
<th><?php echo $words->get('ActivitiesTitle'); ?></th>
<th><?php echo $words->get('ActivitiesDuration'); ?></th>
<th><?php echo $words->get('ActivitiesPlace'); ?></th>
<th><?php echo $words->get('ActivitiesNumberAttendees'); ?></th>
<th><?php echo $words->get('ActivitiesOrganizers'); ?></th>
<th></th>
</tr>
<?php 
$count= 0;
foreach($this->activities as $activity) {
    echo '<tr class="' . $background = (($count % 2) ? 'highlight' : 'blank') . '" title="' . $activity->title . '">';
    echo '<td><a href="activities/' . $activity->id . '">' . $activity->title . '</a></td>';
    echo '<td>' . $activity->dateStart . '-<br />' . $activity->dateEnd . '</td>';
    echo '<td>' . $activity->location->name . ', ' . $activity->location->getCountry()->name . '</td>';
    echo '<td>' . count($activity->attendees) . '</td>';
    echo '<td>';
    $organizers = '';
    foreach($activity->organizers as $organizer) {
        $organizers .= $organizer->Username . ", ";
    }
    echo substr($organizers, 0, -2) . '</td>';
    if (in_array($this->member->id, array_keys($activity->organizers))) {
        echo '<td><a href="activities/' . $activity->id . '/edit">'
        . '<img src="images/icons/comment_edit.png" alt="edit" /></a></td>';
    } else {echo '<td></td>';}
    echo '</tr>';
    $count++;
}
?>
</table>
<?php
}
?>
</div>