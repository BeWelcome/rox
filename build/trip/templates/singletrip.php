<?php
$words = new MOD_words();
?>
<!-- Subtemplate: 2 columns 50/50 size -->
<div class="subcolumns">
  <div class="c50l">
    <div class="subcl">
<?php
if (isset($trip_data[$trip->trip_id])) {
    //echo '<h3>Stations of this trip</h3>';
	if ($isOwnTrip) {
		echo '<p class="small">'.$words->get('Trip_draganddrop').'</p>';
	}
	
	echo '<ul id="triplist">';
	foreach ($trip_data[$trip->trip_id] as $blogid => $blog) {
		
		echo '<li id="tripitem_'.$blogid.'"'.($isOwnTrip ? ' style="cursor:move;"' : '').'>';
		echo '<div class="floatbox">';
        if ($blog->blog_start) {
            ?>
            <div class="calendar calendar-icon-<?php echo date("m", strtotime($blog->blog_start)) ?>">
              <div class="calendar-day"><?php echo date("j", strtotime($blog->blog_start)) ?></div>
            </div>
            <?php
		}
		if ($blog->name) {
			echo '<h3>'.$blog->name;
			if ($blog->blog_start) {
				echo ', '.$blog->blog_start;
			}
            echo '</h3>';
		}
        echo '<h3 class="borderless"><a href="blog/'.$trip->handle.'/'.$blogid.'">'.$blog->blog_title.'</a></h3>';
        echo '</div>';
		if ($blog->blog_text) {
			if (strlen($blog->blog_text) > 200) {
				$blogtext = substr($blog->blog_text, 0, 200);
				$blogtext .= '<br /><a href="blog/'.$trip->handle.'/'.$blogid.'">'.$words->get('ReadMore').'...</a>';
			} else {
				$blogtext = $blog->blog_text;
			}
			echo '<br />'.$blogtext.'';
		}
		echo '</li>';
			
	}
	echo '</ul>';


?>

<?php
	if ($isOwnTrip) {
?>
<script type="text/javascript">

Sortable.create('triplist', {
	onUpdate:function(){
		new Ajax.Updater('list-info', 'trip/reorder/', {
			onComplete:function(request){
				new Effect.Highlight('triplist',{});
				params = Sortable.serialize('triplist').toQueryParams();
				points = Object.values(params).toString().split(',');
				setPolyline();
				
			}, 
			parameters:Sortable.serialize('triplist'), 
			evalScripts:true, 
			asynchronous:true,
			method: 'get'
		})
	}
})</script>

<?php
} // end if is own trip

} // end if tripdata
else {
    echo 'No entries for this trip yet.';
}
?>
<!-- End of contents for left subtemplate -->
    </div>
  </div>

  <div class="c50r">
    <div class="subcr">
      <!-- Contents for right subtemplate -->
      
<!-- This trip's map -->  
		<?php require 'singletrip_map.php';?>

<!-- This trip's gallery -->  
<?php
if (isset($trip->gallery_id_foreign) && $trip->gallery_id_foreign) {
    $gallery = new Gallery;
    $statement = $gallery->getLatestItems('',$trip->gallery_id_foreign);
    if ($statement) {
        echo '<h3>Pictures of this trip</h3>';
        // if the gallery is NOT empty, go show it
        require SCRIPT_BASE.'build/gallery/templates/overview_simple.php';
    	echo '<p><a href="gallery/show/galleries/'.$trip->gallery_id_foreign.'" title="'.$words->getSilent('Trip_GallerySee').'"><img src="images/icons/picture.png"> '.$words->get('Trip_GallerySee').'</a></p>';
    } elseif ($isOwnTrip) {
        echo '<p><a href="gallery/show/galleries/'.$trip->gallery_id_foreign.'" title="'.$words->getSilent('Trip_GalleryAddPhotos').'"><img src="images/icons/picture_add.png"> '.$words->get('Trip_GalleryAddPhotos').'</a></p>';
    }
    echo $words->flushBuffer();
}
?>
<!-- End of contents for right subtemplate -->
    </div>
  </div>
</div> 
<?php ?>