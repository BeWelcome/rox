<?php
$words = new MOD_words();
$search = '';
if (isset($_GET['s']) && $_GET['s'])
    $search = $_GET['s'];

$cloudmade_conf = PVars::getObj('cloudmade');

?>
 <input type="hidden" id="cloudmadeApiKeyInput" value="<?php echo ($cloudmade_conf->cloudmade_api_key); ?>"/>


<div id="teaser" class="clearfix">
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
                    <input class="button" type="submit" name="submit" value="<?php echo $words->getFormatted('TripsSearch'); ?>" />
                </div>
                </form>
            </div>
        </div>
    </div>
</div>

