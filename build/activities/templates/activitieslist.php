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