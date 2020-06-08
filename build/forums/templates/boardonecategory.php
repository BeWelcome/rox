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
    $styles = array( 'highlight', 'blank' );
    $words = new MOD_words();
?>

<table class="forumsboardthreads">

<tr>
    <th><?php echo $words->getFormatted('Thread'); ?></th>
    <th><?php echo $words->getFormatted('Replies'); ?></th>
    <th><?php echo $words->getFormatted('Author'); ?></th>
    <th><?php echo $words->getFormatted('Views'); ?></th>
    <th><?php echo $words->getFormatted('LastPost'); ?></th>
</tr>

<?php

    foreach ($threads as $cnt =>  $thread) {
        $url = ForumsView::threadURL($thread);

        $max = $thread->replies + 1;
        $maxPage = ceil($max / $this->_model->POSTS_PER_PAGE);

        $last_url = $url.($maxPage != 1 ? '/page'.$maxPage : '').'/#post'.$thread->last_postid;


        ?>
            <tr class="<?php echo $styles[$cnt%2]; ?>">
                <td class="forumsboardthreadtitle">
                    <?php
                    if ($thread->ThreadDeleted=="Deleted") {
                        echo "[Deleted]" ;
                    }
                    if ($thread->ThreadVisibility=="ModeratorOnly") {
                        echo "[ModOnly]" ;
                    }
                    echo "<a href=\"",$url,"\">" ;
                    echo $words->fTrad($thread->IdTitle);
                    ?></a>
                    <br />
                </td>
                <td class="forumsboardthreadreplies"><?php echo $thread->replies; ?></td>
                <td class="forumsboardthreadauthor"><a href="bw/member.php?cid=<?php echo $thread->first_author; ?>"><?php echo $thread->first_author; ?></a></td>
                <td class="forumsboardthreadviews"><?php echo number_format($thread->views); ?></td>
                <td class="forumsboardthreadlastpost">
                    <span class="small grey"><?php
//                  echo "#### [",$thread->last_create_time,"] " ;
                    echo date($words->getFormatted('DateHHMMShortFormat'), ServerToLocalDateTime($thread->last_create_time, $this->getSession()));
                    ?></span><br />
                    <a href="bw/member.php?cid=<?php echo $thread->last_author; ?>"><?php echo $thread->last_author; ?></a>
                    <a href="<?php echo $last_url; ?>"><img src="styles/css/minimal/images/iconsfam/bullet_go.png" alt="<?php echo $words->getBuffered('to_last'); ?>" title="<?php echo $words->getBuffered('to_last'); ?>" /></a><?php echo $words->flushBuffer(); ?>

                </td>
            </tr>
        <?php
    }

?>

</table>
