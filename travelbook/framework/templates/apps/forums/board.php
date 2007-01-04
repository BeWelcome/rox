<?php

$User = APP_User::login();

if ($navichain = $boards->getNaviChain()) {
	$navichain = '<span class="forumsboardnavichain">'.implode(' &#187; ', $navichain).' &#187; <br /></span>';
} else {
	$navichain = '';
}

?>

<h2><?php 
	echo $navichain; 
	echo $boards->getBoardName(); 
?></h2>
<?php
if ($User) {
?>
	<div id="boardnewtopictop"><a href="<?php echo $uri; ?>new">Start a new topic</a></div>
<?php
} // end if $User

	if ($boards->hasSubBoards()) {
		require TEMPLATE_DIR.'apps/forums/boardboards.php';
	}
	
	if ($threads = $boards->getThreads()) {
		require TEMPLATE_DIR.'apps/forums/boardthreads.php';
	}

?>