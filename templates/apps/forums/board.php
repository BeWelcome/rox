<?php

$User = APP_User::login();

$i18n = new MOD_i18n('apps/forums/board.php');
$boardText = $i18n->getText('boardText');

//BW to be cut:
if ($navichain_items = $boards->getNaviChain()) {
	$navichain = '<span class="forumsboardnavichain">';
	foreach ($navichain_items as $link => $title) {
		$navichain .= '<a href="'.$link.'">'.$title.'</a> :: ';
	}
	$navichain .= '<br /></span>';
} else {
	$navichain = '';
}

?>

<h2><?php 
	 
	echo $boards->getBoardName(); 
?></h2>
<!-- cut end -->
<?php

	if ($boards->hasSubBoards()) {
		require TEMPLATE_DIR.'apps/forums/boardboards.php';
	}

?>

<h3><?php

	$number = $boards->getTotalThreads(); 
	if ($number == 0) {
		echo $boardText['found_0_threads'];
	} else if ($number == 1) {
		echo $boardText['found_1_thread'];
	} else {
		printf($boardText['found_X_threads'], $number);
	}

?></h3>

<?php
if ($User) {
?>
	<div id="boardnewtopictop">
    <div class="l"><?php echo $navichain; ?></div>
    <a class="button" href="<?php echo $uri; ?>new"><?php echo $boardText['new_topic']; ?></a></div>
<?php
} // end if $User

	
	if ($threads = $boards->getThreads()) {
		require TEMPLATE_DIR.'apps/forums/boardthreads.php';
	}

?>