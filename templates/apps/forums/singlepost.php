<div class="forumspost">
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
		<p class="forumstime"><?php echo $boardText['posted']; ?> <?php echo date($format['short'], $post->posttime); ?><?php
		
		if ($can_edit_foreign || ($can_edit_own && $User && $post->user_id == $User->getId())) {
			$title = 'Edit';
			echo ' [<a href="forums/edit/m'.$post->postid.'">'.$title.'</a>]';
		}
		if ($can_del) {
			if ($post->postid == $topic->topicinfo->first_postid) {
				$title = $boardText['del_topic_href'];
				$warning = $boardText['del_topic_warning'];
			} else {
				$title = $boardText['del_post_href'];
				$warning = $boardText['del_post_warning'];
			}
			echo ' [<a href="forums/delete/m'.$post->postid.'" onclick="return confirm(\''.$warning.'\');">'.$title.'</a>]';
		}
		
		if (isset($post->title) && $post->title) { // This is set if it's a SEARCH
			echo '<br />';
			echo $boardText['search_topic_text'];
			echo ' <b>'.$post->title.'</b> &mdash; <a href="forums/s'.$post->threadid.'-'.$post->title.'">'.$boardText['search_topic_href'].'</a>';
		}
		?></p>
		<p><?php echo $post->message; ?></p>
	</div>
</div>