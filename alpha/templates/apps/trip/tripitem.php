<?php

$i18n = new MOD_i18n('apps/trip/trip.php');
$tripText = $i18n->getText('tripText');

?>

<tr>
<td class="info">

<h3 class="trip_title"><?php echo $trip->trip_name; ?></h3>
<div class="trip_author">by <a href="user/<?php echo $trip->handle; ?>"><?php echo $trip->handle; ?></a>

<?php
if ($trip->fk_countrycode) {
	echo ' <a href="country/'.$trip->fk_countrycode.'"><img src="images/icons/flags/'.strtolower($trip->fk_countrycode).'.png" alt="" /></a>';
}
?>

        <a href="blog/<?php echo $trip->handle; ?>" title="Read blog by <?php echo $trip->handle; ?>"><img src="images/icons/blog.gif" alt="" /></a>
        <a href="trip/show/<?php echo $trip->handle; ?>" title="Show trips by <?php echo $trip->handle; ?>"><img src="images/icons/world.gif" alt="" /></a>
         &mdash; 
		<a href="trip/<?php echo $trip->trip_id; ?>"><?php echo $tripText['details']; ?></a></div>
<?php
if (isset($trip->trip_descr) && $trip->trip_descr) {
	echo '<p>'.$trip->trip_descr.'</p>';
}

if (isset($trip->trip_text) && $trip->trip_text) {
	echo '<p>'.$trip->trip_text.'</p>';
}




if (isset($trip_data[$trip->trip_id])) {

	echo '<ul>';
	foreach ($trip_data[$trip->trip_id] as $blogid => $blog) {
		
		echo '<li><a href="blog/'.$trip->handle.'/'.$blogid.'">'.$blog->blog_title.'</a>';
		if ($blog->name) {
			echo ', ';
			echo $blog->name;
		}
		if ($blog->blog_start) {
			echo ', ';
			echo $blog->blog_start;
		}
		echo '</li>';
			
	}
	echo '</ul>';
}



?>
</td>
</tr>