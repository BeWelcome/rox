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


This file displays the list of threads under a given tag/category

TODO: CAN THIS PAGE BE DELETED COMPLETELY SINCE WE DON'T USE TAGS???

*/
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


if ($User && empty($noForumNewTopicButton)) {
?>
	<div class="col-12 mb-2 px-0">
    <a class="btn btn-primary float-right" role="button" href="<?php echo $uri; ?>new"><?php echo $words->getBuffered('ForumNewTopic'); ?></a><?php echo $words->flushBuffer(); ?></div>
<?php
} // end if $User

	
	if ($threads = $boards->getThreads()) {
		require 'boardthreads.php';
	}

?>
