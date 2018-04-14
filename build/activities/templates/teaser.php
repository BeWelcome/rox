<div class="d-flex flex-row mb-2">
    <h1><a href="activities"><?php echo $words->get('Activities'); ?></a></h1>

    <?php if (!($this->hideSearch)) { ?>
        <div class="ml-auto">
            <form id="activities-search-box" method="post">
                <div class="input-group">
                    <?php echo $callbackTags; ?>
                    <input class="form-control" type="text" name="activity-keyword" id="activity-keyword" />
                    <span class="input-group-append">
                        <button type="submit" class="btn btn-primary" id="activy-search-button" name="activy-search-button"><i class="fa fa-search"></i> <?php echo $words->getSilent('ActivitiesSearchButton'); ?></button>
                    </span>
                </div>
            </form>
            <?php echo $words->flushBuffer(); ?>
        </div>
    <?php } ?>
</div>