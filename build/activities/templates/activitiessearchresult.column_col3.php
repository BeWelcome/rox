<?php
$formkit = $this->layoutkit->formkit;
$callbackTags = $formkit->setPostCallback('ActivitiesController', 'searchActivitiesCallback');

$errors = array();
if ($this->session->has( 'errors' )) {
    $errors = $this->session->get('errors');
    $this->session->remove('errors');
}
?>
<div class="row">
<?php
if (!empty($errors)) {
    echo '<div class="col-12 alert alert-danger">';
    foreach($errors as $error) {
        echo '<p>' . $words->get($error) . '<p>';
    }
    echo '</div>';
}
?>
    <div class="col-12 mb-1">
        <form id="activities-search-box" method="post">
            <div class="input-group">
                <?php echo $callbackTags; ?>
                <input class="form-control" type="text" name="activity-keyword" id="activity-keyword" value="<?= $this->keyword ?>">
                <span class="input-group-append">
                            <button type="submit" class="btn btn-primary" id="activy-search-button" name="activy-search-button"><i class="fa fa-search"></i> <?php echo $words->getSilent('ActivitiesSearchButton'); ?></button>
                        </span>
            </div>
        </form>
        <?php echo $words->flushBuffer(); ?>
    </div>

    <div class="col-12">
<?php
if ($this->keyword != '') {
    if (count($this->activities) == 0) {
        if ($this->public) {
            echo '<p>' . $words->get('ActivitiesSearchNoPublicResults') . '</p>';
        } else {
            echo '<p>' . $words->get('ActivitiesSearchNoResults') . '</p>';
        }
    } else {
        require_once('activitieslist.php');
    }
}
?>

    </div>
</div>
