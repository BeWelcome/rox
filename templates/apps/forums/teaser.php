<?php
$User = APP_User::login();

$words = new MOD_words();
?>

<div id="teaser" class="clearfix">
<div id="title">
  <h1><a href="forums"><?php echo $words->getFormatted('ForumTitle'); ?></a></h1>
	<?php 
	// <a onClick=”Effect.toggle('forums_introduction','blind');">Hide it</a>
    // TODO better backlink or breadcrumbs. Not decided yet.  
    // <a href="forums">back to index</a>
    ?>
</div>
<div id="forums_introduction" class="note"><div>
  <?php echo $words->getFormatted('ForumIntroduction'); ?>
  </div>
</div>
<?php 
$title = $boards->getBoardName();
if ($title != 'Forums') {echo $title;} ?>
</div>