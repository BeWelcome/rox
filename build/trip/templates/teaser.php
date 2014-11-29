<?php
$words = new MOD_words();
$search = '';
if (isset($_GET['s']) && $_GET['s'])
    $search = $_GET['s'];
?>
<div id="teaser" class="page-teaser clearfix">
	<div class="row">
    	<div class="col-md-7">
    			<h1><a href="trip"><?php echo $words->getFormatted('tripsTitle'); ?></a></h1>
    	</div>
    	<div class="col-md-5" >
            	<div id="searchteaser" class="pull-right">
                <form method="get" action="trip/search" class="float_right">
                    <input type="text" name="s" value="<?= htmlspecialchars($search, ENT_QUOTES)?>" />
                    <input class="button" type="submit" name="submit" value="<?php echo $words->getSilent('TripsSearch'); ?>" />
                    <?php echo $words->flushBuffer(); ?>
                </form>
                </div>
        </div>
    </div>
</div>

