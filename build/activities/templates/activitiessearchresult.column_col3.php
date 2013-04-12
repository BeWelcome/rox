<?php
$formkit = $this->layoutkit->formkit;
$callbackTags = $formkit->setPostCallback('ActivitiesController', 'searchActivitiesCallback');

require_once('../build/geo/geo.entity.php');

$errors = array();
if (isset($_SESSION['errors'])) {
    $errors = $_SESSION['errors'];
    unset($_SESSION['errors']);
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
    <div class="c66l">
        <div class="subcl">
            <form id="activities-search-box" method="post" >
            <?php echo $callbackTags; ?>
            <input type="text" name="activity-keyword" id="activity-keyword" value="<?php echo $this->keyword; ?>" /><input type="submit" name="activities-search" value="<?php echo $words->getSilent('ActivitiesSearchButton'); ?>" /><?php echo $words->flushBuffer(); ?>
            </form>
        </div>
    </div>
    <div class="c33r">
        <div class="subcr float_right">
            // todo: add spinner...
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