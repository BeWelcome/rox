<?php

$User = APP_User::login();

$words = new MOD_words();

// Build board navigation path
$navigationPath = '';
$navichain_items = $boards->getNaviChain();
if (is_array($navichain_items)) {
    // trim off first item ("forums")
    array_shift($navichain_items);
    foreach ($navichain_items as $link => $title) {
        $navigationPath .= '<a href="' . htmlspecialchars($link, ENT_QUOTES) . '">' . htmlspecialchars($title, ENT_QUOTES) . '</a> » ';
    }
}
$boardName = htmlspecialchars($boards->getBoardName(), ENT_QUOTES);
$navigationPath .= '<a href="' . htmlspecialchars($boards->getBoardLink(), ENT_QUOTES) . '">'
    . $boardName . '</a>';

?>
<div class="row">
<div class="col-8">
<?php echo $words->flushBuffer();

	$number = $boards->getTotalThreads(); 
	if ($number == 0) {
		echo $words->getFormatted("Found0Threads");
		$this->page->SetMetaRobots("NOINDEX, NOFOLLOW") ;
	} else if ($number == 1) {
		echo $words->getFormatted("Found1Threads");
	} else {
		echo $words->getFormatted("FoundXThreads", $number);
	}
	?>
</div>

<?
if ($User && empty($noForumNewTopicButton)) {
?>
	<div class="col-4 mb-1">
    <a class="btn btn-primary float-right" role="button" href="<?php echo $uri; ?>new"><?php echo $words->getBuffered('ForumNewTopic'); ?></a><?php echo $words->flushBuffer(); ?></div>
<?php
} // end if $User

	
	if ($threads = $boards->getThreads()) {
		require 'boardthreads.php';
	}

?>
</div>