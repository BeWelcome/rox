<?php

	$i18n = new MOD_i18n('date.php');
	$format = $i18n->getText('format');
	
	$User = APP_User::login();
	
?>

<h2><?php echo $topic->topicinfo->title; ?><br />
<span class="forumsthreadtags"><?php

	if (isset($topic->topicinfo->continent) && $topic->topicinfo->continent) {
		echo Forums::$continents[$topic->topicinfo->continent].' ';
	}

	if (isset($topic->topicinfo->countryname) && $topic->topicinfo->countryname) {
		echo $topic->topicinfo->countryname.' ';
	}
	
	if (isset($topic->topicinfo->adminname) && $topic->topicinfo->adminname) {
		echo $topic->topicinfo->adminname.' ';
	}

	if (isset($topic->topicinfo->geonames_name) && $topic->topicinfo->geonames_name) {
		echo $topic->topicinfo->geonames_name.' ';
	}


?></span></h2>

<?php

if ($User) {

?>
	
	<div id="forumsthreadreplytop"><a href="<?php echo $uri; ?>reply">Reply</a></div>

<?php

} // end if ($User)

	foreach ($topic->posts as $post) {
		require TEMPLATE_DIR.'apps/forums/singlepost.php';
	}
		
if ($User) {

?>
<div id="forumsthreadreplybottom"><a href="<?php echo $uri; ?>reply">Reply</a></div>
<?php

}
?>