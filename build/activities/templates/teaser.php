<div id="teaser" class="page-teaser clearfix">
<div class="float_left">
<h1><a href="activities"><?php echo $words->get('Activities'); ?></a></h1>
</div>
<?php if (!($this->hideSearch)) { ?>
<div class="float_right abitlower">
    <form id="activities-search-box" method="post">
    <?php echo $callbackTags; ?>
    <input type="text" name="activity-keyword" id="activity-keyword" /><input type="submit" class="button" size="60" id="activy-search-button" name="activy-search-button" value="<?php echo $words->getSilent('ActivitiesSearchButton'); ?>" /><?php echo $words->flushBuffer(); ?>
    </form>
</div>
<?php } ?>
</div>