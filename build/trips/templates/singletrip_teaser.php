<?php
$words = new MOD_words();
$layoutbits = new MOD_layoutbits();
$search = '';
if (isset($_GET['s']) && $_GET['s'])
    $search = $_GET['s'];
?>

<div id="teaser" class="page-teaser clearfix">
	<div class="subcolumns">
    	<div class="c50l">
			<div class="subr">
                <div class="clearfix">
                <h1 id="trip_name">
                    <a href="trip/<?=$trip->trip_id ?>" style="padding-right: 10px;">
                    <?=$trip->trip_name ?>
                    </a>
                </h1>
                    <div class="float_left">
                    	<?=$layoutbits->PIC_30_30($trip->handle)?>
                    </div>
                    <div class="trip_author"><?=$words->get('by')?> <a href="members/<?php echo $trip->handle; ?>"><?php echo $trip->handle; ?></a>
                        <a href="blog/<?php echo $trip->handle; ?>" title="Read blog by <?php echo $trip->handle; ?>"><img src="images/icons/blog.gif" alt="" /></a>
                        <a href="trip/show/<?php echo $trip->handle; ?>" title="Show trips by <?php echo $trip->handle; ?>"><img src="images/icons/world.gif" alt="" /></a>
                    </div>
                </div>
            </div>
    	</div> 
    	<div class="c50r" > 
        	<div class="subc">
            	<div id="searchteaser" >
                <form method="get" action="trip/search">
                    <input type="text" name="s" value="<?=$search?>" />
                    <input class="button" type="submit" name="submit" value="<?php echo $words->getFormatted('TripsSearch'); ?>" />
                </div>
                </form>
                </div>
            </div>
        </div>
    </div>
</div>
