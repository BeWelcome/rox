<?php

	$i18n = new MOD_i18n('date.php');
	$format = $i18n->getText('format');

?>

<table class="forumsboardthreads">

<tr>
	<th>Thread</th>
	<th>Replies</th>
	<th>Author</th>
	<th>Views</th>
	<th>Last Post</th>
</tr>

<?php

	foreach ($threads as $thread) {
	//[threadid] => 10 [title] => aswf [replies] => 0 [views] => 0 [first_postid] => 1 [first_authorid] => 1 [first_create_time] => 1165322369 [last_postid] => 1 [last_authorid] => 1 [last_create_time] => 1165322369 [first_author] => dave [last_author] => dave )
		$url = $uri.'s'.$thread->threadid.'-'.$thread->title;
		?>
			<tr>
				<td class="forumsboardthreadtitle">
					<a href="<?php echo $url; ?>"><?php echo $thread->title; ?></a><br />
					<span class="forumsboardthreadtags"><?php
						
						if (isset($thread->continent) && $thread->continent) {
							echo $thread->continent.' ';
						}
					
						if (isset($thread->countryname) && $thread->countryname) {
							echo $thread->countryname.' ';
						}
						
						if (isset($thread->adminname) && $thread->adminname) {
							echo $thread->adminname.' ';
						}
					
						if (isset($thread->geonames_name) && $thread->geonames_name) {
							echo $thread->geonames_name.' ';
						}
					
					?></span>
				</td>
				<td class="forumsboardthreadreplies"><?php echo $thread->replies; ?></td>
				<td class="forumsboardthreadauthor"><a href="user/<?php echo $thread->first_author; ?>"><?php echo $thread->first_author; ?></a></td>
				<td class="forumsboardthreadviews"><?php echo number_format($thread->views); ?></td>
				<td class="forumsboardthreadlastpost">
					<?php echo date($format['short'], $thread->last_create_time); ?><br />
					<?php echo $thread->last_author; ?>
				</td>
			</tr>
		<?php
	}


?>

</table>

<?php
if ($User) {
?>
<div id="boardnewtopicbottom"><a href="<?php echo $uri; ?>new">Start a new topic</a></div>
<?php
}
?>