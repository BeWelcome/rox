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
	$i18n = new MOD_i18n('date.php');
	$format = $i18n->getText('format');

	$words = new MOD_words();
//	$i18n = new MOD_i18n('apps/forums/board.php');
//	$boardText = $i18n->getText('boardText');
	
	$can_del = false;
	$can_edit_own = false;
	$can_edit_foreign = false;
	
?>

<h2><?php echo $words->getFormatted('SearchResults'); ?></h2>

<?php


	foreach ($posts as $post) {
		require TEMPLATE_DIR.'apps/forums/singlepost.php';
	}
		
?>