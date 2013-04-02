<div><h3><?php echo $words->get('ActivitiesUpcoming'); ?></h3>
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
foreach($this->activities as $activity) {
    echo '<tr>';
    echo '<td><a href="/activities/show/' . $activity->id . '">' . $activity->title . '</a></td>';
    echo '<td>' . $activity->dateStart . '-<br />' . $activity->dateEnd . '</td>';
    echo '<td>' . $activity->locationName . ', ' . $activity->locationCountry . '</td>';
    echo '<td>' . count($activity->attendees) . '</td>';
    echo '<td>';
    $organizers = '';
    foreach($activity->organizers as $organizer) {
        $organizers .= $organizer->Username . ", ";
    }
    echo substr($organizers, 0, -2) . '</td>';
    if (in_array($this->member->id, array_keys($activity->organizers))) {
        echo '<td><a href="activities/edit/' . $activity->id . '">'
        . '<img src="images/icons/comment_edit.png" alt="edit" /></a></td>';
    }
    echo '</tr>';
}
?>
</table>
</div>
<?php
}
?>