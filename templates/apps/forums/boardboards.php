
<div id="forumsboardselect">
<?php echo $boardText['choose_subforum']; ?><br />
<br />

<select name="board" id="forumsboarddropdown" onchange="window.location.href=this.value;">
<option value=""><?php echo $boardText['subforum']; ?></option>

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