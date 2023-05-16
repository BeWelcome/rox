<?php
$vars = $this->getRedirectedMem('vars');
if (empty($vars)) {
    $vars['activity-radius'] = $this->radius;
}
$radiusSelect = '<select name="activity-radius" id="activity-radius" class="o-input">';
$distance = array(
    0 => '0 km/0 mi',
    5  => '5 km/3 mi',
    10 => '10 km/6 mi',
    25 => '25 km/15 mi',
    50 => '50 km/30 mi',
    100 => '100 km/60 mi',
    200 => '200 km/120 mi',
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
<div class="row no-gutters">
<div class="col-12">
<form method="POST" name="activity-radius-form" class="form-inline mb-2">
    <?php echo $this->layoutkit->formkit->setPostCallback('ActivitiesController', 'setRadiusCallback');?>
    <label for="activity-nearme" class="o-input-label mr-2"><?= $words->get('ActivitiesRadiusSelectLabel'); ?></label><?= $radiusSelect ?>&nbsp;<input type="submit" class="ml-1 btn btn-primary btn-sm" id="activity-nearme" name="activity-nearme" value="<?= $words->getSilent('activity.update'); ?>" />
</form>
</div>
<?php
if (count($this->activities) == 0) {
    echo '<div class="col-12 mt-3">' . $words->get('ActivitiesNoActivitiesNearYou', $vars['activity-radius']) . '</div>';
    echo '<div class="col-12 mt-3"><a href="/activities/upcoming" class="btn btn-primary">' . $words->get('activities.upcoming') . '</a></div>';
} else {
    require_once('activitieslist.php');
}
?>
</div>
