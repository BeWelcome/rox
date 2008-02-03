<?php
$User = APP_User::login();

$words = new MOD_words();
?>

<div id="teaser" class="clearfix">
  <div id="title" class="float_left">
    <h1><?php echo $words->getFormatted('ForumTitle'); ?></h1>
    <!-- CategoryTitle in teaser -->
    <div class="forumtitle">
      <a href="forums"><?php echo $words->getFormatted('ForumOverviewLink') ?></a>
      <?php $title = $boards->getBoardName();
          if ($title != 'Forums') {echo ' > <a href="forums/', $title ,'">', $title,'</a>';}
       ?>
    </div> <!-- small -->
  </div> <!-- title -->
    <div id="" class="float_right">
        <p></p>
          <p><?php echo $words->getFormatted('ForumBrowseCategories'); ?></p>

    <select name="board" id="forumsboarddropdown" onchange="window.location.href=this.value;">
    <option value=""><?php echo $words->getFormatted('ForumChooseCategory'); ?></option>

<?php

	
	foreach ($topboards as $topboard) {
		$url = 'forums/t'. $topboard->tagid.'-'.$topboard->tag;
		?>
			<option value="<?php echo $url; ?>"><?php echo $topboard->tag; ?></option>
		
		<?php 
		/*if ($board->hasSubBoards()) {
			foreach ($board as $b) {
				echo '<a href="'.$uri.$b->getBoardLink().'">'.$b->getBoardName().'</a>';
				echo '<br />';
			}
		}*/
	}


?>
            </select>
    </div>
</div> <!-- teaser -->
