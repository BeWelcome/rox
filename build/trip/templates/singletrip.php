<?php
$words = new MOD_words();
$i = 1;
?>
<div class="floatbox">

<?php
if (isset($trip_data[$trip->trip_id])) {
    echo '<h3>'.$words->get('Trip_SubtripsTitle').'</h3>';
	if ($isOwnTrip) {
		echo '<p class="small">'.$words->get('Trip_draganddrop').'</p>';
	}
	
	echo '<ul id="triplist">';
	foreach ($trip_data[$trip->trip_id] as $blogid => $blog) {
		
		echo '<li id="tripitem_'.$blogid.'"'.($isOwnTrip ? ' class="edit" style="cursor:move;"' : '').'>';
?>
<!-- Subtemplate: 2 columns 50/50 size -->
<div class="subcolumns">
  <div class="c25l" style="width: 15%">
    <div class="subcl">
<!--	<div class="trip_flag"><?=$i++?></div>-->
<?php
        if ($blog->blog_start) {
            ?>
            <!--<h2 class="trip_date"><?php echo date("M d", strtotime($blog->blog_start)) ?><br />
            <span style="font-size: 14px;"><?php echo date("Y", strtotime($blog->blog_start)) ?></span></h2>-->
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
  
  <div class="c75r" style="width: 85%">
    <div class="subcr">
      <!-- Contents for right subtemplate -->
<?php
        echo '<h3 class="borderless">';
        echo '<a href="blog/'.$trip->handle.'/'.$blogid.'">'.$blog->blog_title.'</a><br />';
		if ($blog->name) {
		    $geo = new GeoModel();
		    $bloggeo = $geo->LoadLocation($blog->blog_geonameid);
		    if (isset($bloggeo[0])) {
		        $countryId = isset($bloggeo[0]->parentCountryId) ? $bloggeo[0]->parentCountryId : false;
		        if ($countryId) {
		            $countrygeo = $geo->LoadLocation($countryId);
		            $countryname = $countrygeo[0]->name;
	            }
	        }
			echo '<span style="font-size: 14px;">'.$blog->name.', '.(isset($countryname) ? $countryname : '').'</span>';
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
?>
</div>

<?php
	if ($isOwnTrip) {
?>
    <div style="padding: 20px 0">
    <h3>
    <a href="blog/create" onclick="$('blog-create-form').toggle(); return false"><img src="images/icons/note_add.png"></a> <a href="blog/create" onclick="$('blog-create-form').toggle(); return false"><?=$words->get('Trip_SubtripsCreate')?></a><br />
    </h3>
    <p class="small"><?=$words->get('Trip_SubtripsCreateDesc')?></p>
    </div>
    <?php require 'subtrip_createform.php' ?>
<?php
    }
?>
