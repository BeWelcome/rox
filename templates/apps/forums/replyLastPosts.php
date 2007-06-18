<?php

	$i18n = new MOD_i18n('date.php');
	$format = $i18n->getText('format');

	$i18n = new MOD_i18n('apps/forums/board.php');
	$boardText = $i18n->getText('boardText');

	$can_del = false;
	$can_edit_own = false;
	$can_edit_foreign = false;
?>

<h2 id="forums_reply_title_lastposts"><?php echo $boardText['last_posts']; ?></h2>
<p><?php echo $boardText['last_post_subline']; ?></p>

<?php
	foreach ($topic->posts as $post) {
		require TEMPLATE_DIR.'apps/forums/singlepost.php';
	}


?>