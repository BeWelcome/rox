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

*/
$User = APP_User::login();

$words = new MOD_words();

//BW to be cut:
if ($navichain_items = $boards->getNaviChain()) {
	$navichain = '<span class="forumsboardnavichain">';
	foreach ($navichain_items as $link => $title) {
		$navichain .= '<a href="'.$link.'">'.$title.'</a> :: ';
	}
	$navichain .= '<br /></span>';
} else {
	$navichain = '';
}

?>

<h2><?php 
	 
	echo $boards->getBoardName(); 
	echo "</h2>" ;

	if ((HasRight("ForumModerator","Edit")) ||(HasRight("ForumModerator","All")) ) {
	   if (isset($boards->IdTag)) echo " <a href=\"forums/modedittag/".$boards->IdTag."\">Edit Tag</a>" ;
   }

   if (isset($boards->IdSubscribe)) {
	 	echo " <span class=\"button\"><a href=\"forums/subscriptions/unsubscribe/tag/",$boards->IdSubscribe,"/",$boardspic->IdKey,"\">",$words->getBuffered('ForumUnsubscribe'),"</a></span>",$words->flushBuffer();
	}
	else {
	 	if (isset($boards->IdTag)) echo " <span class=\"button\"><a href=\"forums/subscribe/tag/",$boards->IdTag,"\">",$words->getBuffered('ForumSubscribe'),"</a></span>",$words->flushBuffer(); 
	}  
	
?>
<p><?php
    $tags = $boards->getBoardDescription();
?></p>
<!-- cut end -->
<?php

	if ($boards->hasSubBoards()) {
		require 'boardboards.php';
	}

?>

<h3><?php

	$number = $boards->getTotalThreads(); 
	if ($number == 0) {
		echo $words->getFormatted("Found0Threads");
		$this->page->SetMetaRobots("NOINDEX, NOFOLLOW") ;
	} else if ($number == 1) {
		echo $words->getFormatted("Found1Threads");
	} else {
		echo $words->getFormatted("FoundXThreads", $number);
	}

?></h3>

<?php
if ($User) {
?>
	<div id="boardnewtopictop">
    <div class="l"><?php echo $navichain; ?></div>
    <span class="button"><a href="<?php echo $uri; ?>new"><?php echo $words->getBuffered('ForumNewTopic'); ?></a></span><?php echo $words->flushBuffer(); ?></div>
<?php
} // end if $User

	
	if ($threads = $boards->getThreads()) {
		require 'boardthreads.php';
	}

?>