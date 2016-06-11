<?php
/*

Copyright (c) 2007 BeVolunteer

This file is part of BW Rox.

BW Rox is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

BW Rox is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, see <http://www.gnu.org/licenses/> or 
write to the Free Software Foundation, Inc., 59 Temple Place - Suite 330, 
Boston, MA  02111-1307, USA.

*/
$words = new MOD_words();
?>

<div id="forumsboardselect" class="highlight">
  <p><?php echo $words->getFormatted('ForumChooseSubforum'); ?></p>

<select name="board" id="forumsboarddropdown" onchange="window.location.href=this.value;">
<option value=""><?php echo $words->getBuffered('ForumSubforum'); ?></option>

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
</select><?php echo $words->flushBuffer(); ?>
</div>