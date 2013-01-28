<?php
/*
 * template content: 
 * shows the destinations (subtrips) of a trip 
 * 2 columns 10% /90%
 * left column: calendar icons (date)
 * right column: destination title, location and text
 * 
 */
?>
<div class="floatbox">

<?php
if (isset($trip_data[$trip->trip_id])) {
    echo '<h3>'. $CntSubtrips.' ' .$words->get('Trip_SubtripsTitle').'</h3>';
	echo '<ul id="triplist">';
	foreach ($trip_data[$trip->trip_id] as $blogid => $blog) {
		
		echo '<li id="tripitem_'.$blogid.'">';
?>

<!-- Subtemplate: 2 columns 50/50 size -->
<div class="subcolumns" style="padding-bottom: 30px;">
  <div class="c25l" style="width: 10%">
    <div class="subcl">
<?php
        if ($blog->blog_start) {
            ?>
            <div class="calendar calendar-icon-<?php echo date("m", strtotime($blog->blog_start)) ?>">
              <div class="calendar-day"><?php echo date("j", strtotime($blog->blog_start)) ?></div>
              <div class="calendar-year"><?php echo date("Y", strtotime($blog->blog_start)) ?></div>
            </div>
            <?php
		}
?>
<!-- End of contents for left subtemplate -->
    </div>
  </div>
  
  <div class="c75r" style="width: 90%">
    <div class="subcr">
      <!-- Contents for right subtemplate -->
        <h3 class="borderless">
        <a href="blog/<?=$trip->handle?>/<?=$blogid?>"><?=$blog->blog_title?></a> </h3>
<?php	if ($blog->name)
        {
		    if ($bloggeo = $this->model->getBlogGeo($blog->blog_geonameid))
            {
                $country = $bloggeo->getCountry();
                $countryname = $country->name;
	        }
            else
            {
                $countryname = '';
            }
			echo "<span class='trip_author'>{$blog->name}, {$countryname}</span><br />";
		}
?>
<?php 
		if ($blog->blog_text) {
            $blogtext = $blog->blog_text;
            $moreLink = '<br /><a href="blog/' . $trip->handle. '/' . $blogid . '">' . $words->get('ReadMore') . ' ...</a>';
            echo '<div>' . MOD_layoutbits::truncate_words($blogtext, 60, $moreLink) . '</div>';
		} 
?>
<div>
<?php
if ($member && $isOwnTrip) {?>
<a href="blog/edit/<?=$blogid; ?>"><img src="styles/css/minimal/images/iconsfam/pencil.png" style="vertical-align:bottom;" alt="<?=$words->get('Trip_EditMyOwnSubTrip')?>" /></a> <a href="blog/edit/<?=$blogid; ?>" title="<?=$words->get('Trip_EditMyOwnSubTrip')?>"><?=$words->get('Trip_EditMyOwnSubTrip')?></a>
<?php   }?>
</div>
<!-- End of contents for right subtemplate -->
    </div><!-- End of subcr -->
  </div><!-- End of c75r -->
</div>
<?php
		echo '</li>';
			
	}
	echo '</ul>';

} // end if tripdata
?>
</div>
<?php require 'subtrip_createform.php' ?>
