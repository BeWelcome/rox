<?php 
$vars = $this->getRedirectedMem('vars');
if (empty($vars)) {
    $vars['activity-radius'] = $this->radius;
}
$radiusSelect = '<select name="activity-radius" id="activity-radius">';
$distance = array(
    0 => '0 km/0 mi', 
    5  => '5 km/3 mi', 
    10 => '10 km/6 mi', 
    25 => '25 km/15 mi', 
    50 => '50 km/30 mi', 
    100 => '100 km/60 mi',
);
foreach($distance as $value => $display) :
    $radiusSelect .= '<option value="' . $value . '"';
    if ($value == $vars['activity-radius']) {
        $radiusSelect .= ' selected="selected"';
    }
    $radiusSelect .= '>' . $display . '</option>';
endforeach;
$radiusSelect .= '</select>';
?>
<form method="POST" name="activity-radius-form">
<?php 
echo $this->layoutkit->formkit->setPostCallback('ActivitiesController', 'setRadiusCallback');
echo $words->get('ActivitiesRadiusSelect', $radiusSelect); ?>. &nbsp;
<input type="submit" class="button" id="activity-nearme" name="activity-nearme" value="update" /> 
</form> 
<div class="bw-row"><?php 
if (count($this->activities) == 0) {
    echo '<p>' . $words->get('ActivitiesNoActivitiesNearYou', $vars['activity-radius']) . '</p>';
} else {
    require_once('activitieslist.php');
}
?></div>
