<?php

	$i18n = new MOD_i18n('date.php');
	$format = $i18n->getText('format');

	$i18n = new MOD_i18n('apps/forums/board.php');
	$boardText = $i18n->getText('boardText');

	$can_del = false;
	$can_edit_own = false;
	$can_edit_foreign = false;
	
?>

<h2><?php echo 'Search Results'; ?></h2>

<?php


	foreach ($posts as $post) {
		require TEMPLATE_DIR.'apps/forums/singlepost.php';
	}
		
?>