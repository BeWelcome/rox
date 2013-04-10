<?php
$formkit = $this->layoutkit->formkit;
$callbackTags = $formkit->setPostCallback('ActivitiesController', 'searchActivitiesCallback');
?><div class="row">
<div class="subcolumns">
    <div class="c50l">
        <div class="subcl">
            <form id="activities-search-box" method="post">
            <?php echo $callbackTags; ?>
            <input type="text" name="activity-keyword" id="activity-keyword" /><input type="submit" size="60" id="activy-search-button" name="activy-search-button" value="<?php echo $words->getSilent('ActivitiesSearchButton'); ?>" /><?php echo $words->flushBuffer(); ?>
            </form>
        </div>
    </div>
    <div class="c50r">
        <div class="subcr float_right">
            <a class="bigbutton" href="activities/create"><span><?= $words->getSilent('ActivityCreateNew'); ?></span></a><?php echo $words->flushBuffer(); ?>
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
    require_once('activitieslist.php');
}
?>
</div>