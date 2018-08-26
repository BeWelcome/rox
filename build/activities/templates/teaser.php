<div class="row mb-2">
    <div class="col px-sm-0 px-md-3">
        <h1><a href="activities"><?php echo $words->get('Activities'); ?></a></h1>
    </div>

        <?php if (!($this->hideSearch)) { ?>
            <div class="col-auto">
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