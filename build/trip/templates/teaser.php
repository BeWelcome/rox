<?php
$words = new MOD_words();
$search = '';
if (isset($_GET['s']) && $_GET['s'])
    $search = $_GET['s'];

$map_conf = PVars::getObj('map');

?>
<input type="hidden" id="osm-tiles-provider-base-url" value="<?php echo ($map_conf->osm_tiles_provider_base_url); ?>"/>
<input type="hidden" id="osm-tiles-provider-api-key" value="<?php echo ($map_conf->osm_tiles_provider_api_key); ?>"/>

<div id="teaser" class="page-teaser clearfix">
	<div class="subcolumns">
    	<div class="c50l">
			<div class="subr">
    			<h1><a href="trip"><?php echo $words->getFormatted('tripsTitle'); ?></a></h1>
        	</div>
    	</div> 
    	<div class="c50r" > 
        	<div class="subc">
            	<div id="searchteaser" >
                <form method="get" action="trip/search" class="float_right">
                    <input type="text" name="s" value="<?= htmlspecialchars($search, ENT_QUOTES)?>" />
                    <input class="button" type="submit" name="submit" value="<?php echo $words->getSilent('TripsSearch'); ?>" />
                    <?php echo $words->flushBuffer(); ?>
                </div>
                </form>
            </div>
        </div>
    </div>
</div>

