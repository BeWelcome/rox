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
        </div> <!-- forumsauthorname -->
        <div class="forumsavatar">
            <img
                class="framed"
                src="<?php echo MOD_layoutbits::smallUserPic_username($post->user_handle) ?>"
                alt="avatar"
                title="<?php echo $post->user_handle; ?>"
                height="56"
                width="56"
                style="height:auto; width:auto;"
            /> <!-- img -->
        </div> <!-- forumsavatar -->
    </div> <!-- forumsauthor -->
    <div class="forumsmessage">
        <p class="forumstime">
            <?php echo $words->getFormatted('posted'); ?> <?php echo date($format['short'], $post->posttime); ?>
            <?php
            
            if ($can_edit_own && $User && $post->user_id == $User->getId()) {
                echo ' [<a href="forums/edit/m'.$post->postid.'">'.$words->getFormatted('forum_EditTranslate').'</a>]';
            }
            if ((HasRight("ForumModerator","Edit")) ||(HasRight("ForumModerator","All")) ) {
                echo ' [<a href="forums/edit/m'.$post->postid.'">Mod Edit</a>]';
                echo ' [<a href="forums/modeditpost/'.$post->postid.'">Full Edit</a>]';
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
//                echo ' <b>'.$post->title.'</b> &mdash; <a href="'.ForumsView::postURL($post).'">'.$words->getFormatted('search_topic_href').'</a>';
                echo ' <b>'.$words->fTrad($post->IdTitle).'</b> &mdash; <a href="'.ForumsView::postURL($post).'">'.$words->getFormatted('search_topic_href').'</a>';
            }
            ?>
        </p>
        <hr />
        <p><?php 
		 // echo $post->message;
		 $Sentence=$words->fTrad($post->IdContent) ; 
		 echo $Sentence,"</p>";
 	     echo "    </div> <!-- forumsmessage -->" ;
		 ?>
</div> <!-- forumspost -->
<?php
		 if ($topic->WithDetail) { // If the details of trads are available, we will display them
		 	$max=count($post->Trad) ;
			if ($max>1) { // we will display the list of trads only if there is more than one trad
			  echo "<p>Available trads :" ; 
		 	  for ($jj=0;$jj<$max;$jj++) {
				$Trad=$post->Trad[$jj] ;
				if ($jj==0) {
				   echo "[Original <a title=\"".$Trad->Sentence."\">".$Trad->ShortCode."</a>] " ;
				}
				else {
				   echo "[<a title=\" [translated by ".$Trad->TranslatorUsername."]".$Trad->Sentence."\">".$Trad->ShortCode."</a>] " ;
				} 
			  }
			  echo "</p>" ;
			}
		 } // end If the details of trads are available, we will display them
?>
