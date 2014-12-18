<div id="teaser" class="page-teaser clearfix">
    <div class="float_left">
        <h1><a href="trips"><?php echo $words->get('Trips'); ?></a></h1>
    </div>
    <?php if (!($this->hideSearch)) { ?>
        <div class="float_right abitlower">
            <form id="trips-search-box" method="post">
                <?php echo $callbackTags; ?>
                <input type="text" name="trips-keyword" id="trips-keyword" /><input type="submit" class="button" size="60" id="trip-search-button" name="trip-search-button" value="<?php echo $words->getSilent('TripsSearchButton'); ?>" /><?php echo $words->flushBuffer(); ?>
            </form>
        </div>
    <?php } ?>
</div>