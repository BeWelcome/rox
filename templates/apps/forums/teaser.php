<?php
$User = APP_User::login();

$words = new MOD_words();
?>

<div id="teaser" class="clearfix">
  <div id="title">
    <h1><?php echo $words->getFormatted('ForumTitle'); ?></h1>
    <!-- CategoryTitle in teaser -->
    <div class="forumtitle">
      <a href="forums"><?php echo $words->getFormatted('ForumOverviewLink') ?></a>
      <?php $title = $boards->getBoardName();
          if ($title != 'Forums') {echo ' > <a href="forums/', $title ,'">', $title,'</a>';}
       ?>
    </div> <!-- small -->
  </div> <!-- title -->
</div> <!-- teaser -->