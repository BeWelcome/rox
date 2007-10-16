<?php
$words = new MOD_words();
?>

<div id="forumsboardselect" class="highlight">
  <p><?php echo $words->getFormatted('ForumChooseSubforum'); ?></p>

<select name="board" id="forumsboarddropdown" onchange="window.location.href=this.value;">
<option value=""><?php echo $words->getFormatted('ForumSubforum'); ?></option>

<?php

	
	foreach ($boards as $board) {
		$url = $uri.$board->getBoardLink();
		?>
			<option value="<?php echo $url; ?>"><?php echo $board->getBoardName(); ?></option>
		
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