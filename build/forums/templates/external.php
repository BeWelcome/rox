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
$User = APP_User::login();
$words = new MOD_words();

?>


<?php
    $uri = ForumsView::getURI();
    if ($threads = $boards->getThreads()) {
?>

<table class="forumsboardthreads floatbox">

<?php
$threadsliced = array_slice($threads, 0, 5);
    foreach ($threadsliced as $cnt =>  $thread) {
    //[threadid] => 10 [title] => aswf [replies] => 0 [views] => 0 [first_postid] => 1 [first_authorid] => 1 [first_create_time] => 1165322369 [last_postid] => 1 [last_authorid] => 1 [last_create_time] => 1165322369 [first_author] => dave [last_author] => dave )
        //$url = $uri.'s'.$thread->threadid.'-'.$thread->title;
        $url = ForumsView::threadURL($thread);

        $max = $thread->replies + 1;
        $maxPage = ceil($max / $this->_model->POSTS_PER_PAGE);

        $last_url = $url.($maxPage != 1 ? '/page'.$maxPage : '').'/#post'.$thread->last_postid;
//        $last_url = $uri.'s'.$thread->threadid.($maxPage != 1 ? '/page'.$maxPage : '').'/#post'.$thread->last_postid;


        ?>
            <tr>
                <td class="forumsboardthreadtitle"><?php echo '<img src="styles/css/minimal/images/iconsfam/comment_add.png" alt="'. $words->getBuffered('tags') .'" title="'. $words->getBuffered('tags') .'" />' . $words->flushBuffer();?>
                    <a href="<?php echo $url; ?>" class="news">
                    <?php
                    echo $words->fTrad($thread->IdTitle);
                    ?></a><br />
                    <span class="small grey">by <a href="people/<?php echo $thread->last_author; ?>"><?php echo $thread->last_author; ?></a> -
                    <?php echo date($words->getFormatted('DateHHMMShortFormat'), ServerToLocalDateTime($thread->last_create_time)); ?>
                    <?php // echo date($words->getFormatted('DateHHMMShortFormat'), $thread->last_create_time); ?>
                    </span>
                    <a href="<?php echo $last_url; ?>"><img src="styles/css/minimal/images/iconsfam/bullet_go.png" alt="<?php echo $words->getBuffered('to_last'); ?>" title="<?php echo $words->getBuffered('to_last'); ?>" /></a><?php echo $words->flushBuffer(); ?>
                </td>
            </tr>
        <?php
    }


?>

</table>

<?php
    if ($User && $uri != 'forums/') {
    ?>
    <div id="boardnewtopicbottom"><span class="button"><a href="<?php echo ($uri !='') ? $uri : 'forums/'; ?>new"><?php echo $words->getBuffered('ForumNewTopic'); ?></a></span><?php echo $words->flushBuffer(); ?></div>
    <?php
    }

}
?>
