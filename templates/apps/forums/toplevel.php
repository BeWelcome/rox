<?php
$User = APP_User::login();

$i18n = new MOD_i18n('apps/forums/board.php');
$boardText = $i18n->getText('boardText');

?>

<h2><?php echo $boardText['title']; ?></h2>

<div id="forums_introduction"><?php echo $boardText['intro']; ?></div>


<div class="row"><div class="forums_subtitle"><?php echo $boardText['browse']; ?></div>
<div class="l" style="width: 25%;"><?php echo $boardText['browse_category']; ?>
<ul>
<?php
	foreach ($top_tags as $tagid => $tag) {
		echo '<li><a href="forums/t'.$tagid.'-'.rawurlencode($tag->tag).'">'.$tag->tag.'</a><br />
			<span class="forums_tag_description">'.$tag->tag_description.'</span>
		</li>';
	}

?>
</ul>
</div>


<div class="l" style="width: 25%;"><?php echo $boardText['browse_continent']; ?>
<ul class="floatbox">
	<li><a href="forums/kAF-Africa">Africa</a></li>
	<li><a href="forums/kAN-Antarctica">Antarctica</a></li>
	<li><a href="forums/kAS-Asia">Asia</a></li>
	<li><a href="forums/kEU-Europe">Europe</a></li>
	<li><a href="forums/kNA-North America">North America</a></li>
	<li><a href="forums/kSA-South Amercia">South Amercia</a></li>
	<li><a href="forums/kOC-Oceania">Oceania</a></li>
</ul>
</div>

<div class="l floatbox" style="width: 45%;"><?php echo $boardText['browse_tag']; ?><br />
<?php
	$taglist = '';
	foreach ($all_tags as $tagid => $tag) {
		$taglist .=  '<a href="forums/t'.$tagid.'-'.rawurlencode($tag).'">'.$tag.'</a>&nbsp;:: ';
	}
	$taglist = rtrim($taglist, ': ');
	echo $taglist;

?>
</div>


</div>
<br style="clear: both;" />
<?php
	$uri = 'forums/';
	if ($threads = $boards->getThreads()) {
?>
		<div class="row"><div class="forums_subtitle"><?php printf($boardText['newest'], $boards->getTotalThreads()); ?></div>
<?php
		require TEMPLATE_DIR.'apps/forums/boardthreads.php';
?>
		</div>
<?php
	}
?>