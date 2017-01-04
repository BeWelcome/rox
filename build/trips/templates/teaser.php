<div class="m-t-2 clearfix">
    <h1 class="pull-left"><a href="trips"><?php echo $words->get('Trips'); ?></a></h1>

    <?php if (!($this->hideSearch)) { ?>
        <div class="pull-right fullheight mt-1">
            <form id="trips-search-box" method="post">
                <div class="input-group verticalalign">
                    <?php echo $callbackTags; ?>
                    <input class="form-control" type="text" name="trips-keyword" id="trips-keyword" />
                    <span class="input-group-btn">
                        <button type="submit" class="btn btn-primary" id="trip-search-button" name="trip-search-button"><i class="fa fa-search"></i> <?php echo $words->getSilent('TripsSearchButton'); ?></button>
                    </span>
                </div>
            </form>
            <?php echo $words->flushBuffer(); ?>
        </div>
    <?php } ?>
</div>