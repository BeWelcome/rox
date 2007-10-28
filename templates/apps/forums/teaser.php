<?php
$User = APP_User::login();

$words = new MOD_words();
?>
<div id="teaser" class="clearfix">
<div id="title">
  <h1><?php echo $words->getFormatted('ForumTitle'); ?></h1>
</div>
<div id="forums_introduction" class="note">
  <?php echo $words->getFormatted('ForumIntroduction'); ?>
</div>
<?php echo $boards->getBoardName(); ?>
</div>