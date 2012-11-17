<div class="floatbox">

<?php
if (isset($trip_data[$trip->trip_id])) {
    echo '<h3>'. $CntSubtrips.' ' .$words->get('Trip_SubtripsTitle').'</h3>';
	echo '<ul id="triplist">';
	foreach ($trip_data[$trip->trip_id] as $blogid => $blog) {
		
		echo '<li id="tripitem_'.$blogid.'">';
?>

<!-- Subtemplate: 2 columns 50/50 size -->
<div class="subcolumns">
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
<?php
        echo <<<HTML
        <h3 class="borderless">
        <a href="blog/{$trip->handle}/{$blogid}">{$blog->blog_title}</a><br />
HTML;
		if ($blog->name)
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
			echo "<span style='font-size: 14px;'>{$blog->name}, {$countryname}</span>";
		}
        echo '</h3>';
		if ($blog->blog_text) {
			if (strlen($blog->blog_text) > 400) {
				$blogtext = substr($blog->blog_text, 0, 400);
				$blogtext .= '...<br /><a href="blog/'.$trip->handle.'/'.$blogid.'">'.$words->get('ReadMore').'...</a>';
			} else {
				$blogtext = $blog->blog_text;
			}
			echo '<p>'.$blogtext.'</p>';
		}
?>
<!-- End of contents for right subtemplate -->
    </div>
  </div>
</div> 
<?php
		echo '</li>';
			
	}
	echo '</ul>';

} // end if tripdata
?>
</div>

<?php
	if ($isOwnTrip) {
?>
    <div style="padding: 20px 0">
    <h3>
    <a href="blog/create" name="destination" onclick="$('blog-create-form').toggle(); return false"><img src="images/icons/note_add.png"></a> <a href="blog/create" onclick="$('blog-create-form').toggle(); return false"><?=$words->get('Trip_SubtripsCreate')?></a><br />
    </h3>
    <p class="small"><?=$words->get('Trip_SubtripsCreateDesc')?></p>
    </div>
    <?php require 'subtrip_createform.php' ?>
<?php
    }
?>
