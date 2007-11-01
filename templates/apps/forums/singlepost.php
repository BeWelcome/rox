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
    $styles = array( 'highlight', 'blank' );

?>
<div class="forumspost <?php echo $styles[$cnt%2]; //background switch trick, see topic.php for more ?>">
	<div class="forumsauthor">	
		<div class="forumsauthorname">
			<a name="post<?php echo $post->postid; ?>"></a>
			<a href="bw/member.php?cid=<?php echo $post->user_handle; ?>"><?php echo $post->user_handle; ?></a>
			<a href="country/<?php echo $post->fk_countrycode; ?>"><img src="images/icons/flags/<?php echo strtolower($post->fk_countrycode); ?>.png" alt="" /></a>
		</div>	
		<div class="forumsavatar">
			<img class="framed" src="http://<?php $BWImageURL=file_get_contents("http://www.bewelcome.org/myphotos.php?PictForMember=".$post->user_handle); echo $BWImageURL; ?>?xs=1" alt="avatar" title="<?php echo $post->user_handle; ?>"  width="50" height="50" />
		</div>
	</div>
	<div class="forumsmessage">
		<p class="forumstime"><?php echo $words->getFormatted('posted'); ?> <?php echo date($format['short'], $post->posttime); ?><?php
		
		if ($can_edit_foreign || ($can_edit_own && $User && $post->user_id == $User->getId())) {
			$title = 'Edit';
			echo ' [<a href="forums/edit/m'.$post->postid.'">'.$title.'</a>]';
		}
		if ($can_del) {
			if ($post->postid == $topic->topicinfo->first_postid) {
				$title = $words->getFormatted('del_topic_href');
				$warning = $words->getFormatted('del_topic_warning');
			} else {
				$title = $words->getFormatted('del_post_href');
				$warning = $words->getFormatted('del_post_warning');
			}
			echo ' [<a href="forums/delete/m'.$post->postid.'" onclick="return confirm(\''.$warning.'\');">'.$title.'</a>]';
		}
		
		if (isset($post->title) && $post->title) { // This is set if it's a SEARCH
			echo '<br />';
			echo $words->getFormatted('search_topic_text');
			echo ' <b>'.$post->title.'</b> &mdash; <a href="forums/s'.$post->threadid.'-'.$post->title.'">'.$words->getFormatted('search_topic_href').'</a>';
		}
		?></p>
		<p><?php echo $post->message; ?></p>
	</div>
</div>