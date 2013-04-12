<?php $this->pager->render(); ?>
<table class='activitieslist'>
<?php 
$count= 0;
foreach($this->activities as $activity) {
    echo '<tr class="' . $background = (($count % 2) ? 'highlight' : 'blank') . '" title="' . $activity->title . '">';
    echo '<td style="padding-bottom: 30px; width: 10%;">
            <div class="calendar calendar-icon-' . date("m", strtotime($activity->dateStart)) . '">
              <div class="calendar-day">' . date("j", strtotime($activity->dateStart)) . '</div>
              <div class="calendar-year">' . date("Y", strtotime($activity->dateStart)) . '</div></td>';
    echo '<td colspan="2"><div class="small grey">' . $activity->dateStart . '-' . $activity->dateEnd . '</div><h3><a href="activities/' . $activity->id . '">' . $activity->title . '</a><h3></td>';
    echo '<td><i class="icon-map-marker icon-3x grey"></i></td>';
    echo '<td>' . $activity->location->name . '<br /> ' . $activity->location->getCountry()->name . '</td>';
    echo '<td>' . count($activity->attendees) . '&nbsp;' . $words->get('ActivitiesNumbAttendees') . '</td>';
    echo '<td width="112px"><div class="small grey">' . $words->get('ActivitiesOrganizedBy') . '</div>';
    $organizers = '';
    foreach($activity->organizers as $organizer) {
        $organizers .= MOD_layoutbits::PIC_40_40($organizer->Username,'',$style='framed float_left') . " ";
    }
    echo substr($organizers, 0, -2) . '</td>';
    if (isset($this->member) && in_array($this->member->id, array_keys($activity->organizers))) {
        echo '<td><a href="activities/' . $activity->id . '/edit">'
        . '<img src="images/icons/comment_edit.png" alt="edit" /></a></td>';
    } else {echo '<td></td>';}
    echo '</tr>';
    $count++;
}
?>
</table>
<?php $this->pager->render(); ?>
