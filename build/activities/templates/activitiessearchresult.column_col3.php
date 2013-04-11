<?php
$formkit = $this->layoutkit->formkit;
$callbackTags = $formkit->setPostCallback('ActivitiesController', 'searchActivitiesCallback');

require_once('../build/geo/geo.entity.php');
require_once('../build/activities/activity.entity.php');

$errors = array();
if (isset($_SESSION['errors'])) {
    $errors = $_SESSION['errors'];
    unset($_SESSION['errors']);
}
$vars= array();
if (isset($_SESSION['vars'])) {
    $vars = $_SESSION['vars'];
    unset($_SESSION['vars']);
}
$activities= array();
if (isset($_SESSION['activities'])) {
    $activities = $_SESSION['activities'];
    unset($_SESSION['activities']);
}
$this->activities = $activities;
if (empty($vars)) {
    $vars['activity-keyword'] = '';
}
?><div class="row">
<?php 
if (!empty($errors)) {
    echo '<div class="subcolumns error">';
    foreach($errors as $error) {
        echo '<p>' . $words->get($error) . '<p>';
    }
    echo '</div>';
}
?>
</div>
<div class="subcolumns row">
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
if (count($this->activities) == 0) {
    if ($this->public) {
        echo '<p>' . $words->get('ActivitiesSearchNoPublicResults') . '</p>';
    } else {
        echo '<p>' . $words->get('ActivitiesSearchNoResults') . '</p>';
    }
} else {
    require_once('activitieslist.php');
}
?>
</div>