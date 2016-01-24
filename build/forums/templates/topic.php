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


This File display a topic and the messages which are inside it


* @author     Original author unknown
* @author     Michael Dettbarn (lupochen) <mail@lupochen.com>
* @updated    JeanYves
*/

// This means no thread was fetch or that it was outside visibility
    //$i18n = new MOD_i18n('apps/forums/board.php');
    //$boardText = $i18n->getText('boardText');
    $words = new MOD_words();

    $User = APP_User::login();
    $can_del = $User && $User->hasRight('delete@forums'); // Not to use anymore (JeanYves)
    $can_edit_own = $User;
//    $can_edit_own = $User && $User->hasRight('edit_own@forums');
    $can_edit_foreign = $User && $User->hasRight('edit_foreign@forums');

    if (!isset($topic->topicinfo->IsClosed)) {
    }
    echo "<h3 class=\"forumstopic\">" ;

    }
    if ($topic->topicinfo->ThreadVisibility == 'ModeratorOnly') {
        echo "[ModOnly]";
    }

    echo $words->fTrad($topic->topicinfo->IdTitle);
    if ($User) {
    }
    echo "</h3>";

    ?>

    <div id="forumsthreadreplytop" class="pull-xs-right">
      <a class="button" href="
      <?php

      if (isset($topic->IdSubscribe)) {
        echo ForumsView::getURI()."/subscriptions/unsubscribe/thread/",$topic->IdSubscribe,"/",$topic->IdKey,"\">",$words->getBuffered('ForumUnsubscribe'),"</a>",$words->flushBuffer();
      }
      else {
        echo ForumsView::getURI()."/subscribe/thread/",$topic->IdThread,"\">",$words->getBuffered('ForumSubscribe'),"</a>",$words->flushBuffer();
      }
      if ((!$topic->topicinfo->IsClosed)and($topic->topicinfo->CanReply)) {
      ?>
        <a class="button" href="<?php echo $replyuri; ?>"><?php echo $words->getBuffered('ForumReply'); ?></a><?php echo $words->flushBuffer() ?>

      <?php
      }
      ?>
    </div>
    <div class="clearfix"></div>

    <div class="forumthreadinfo">
        <div class="float_left">
            <?php
            if ($topic->topicinfo->IdGroup > 0) {
                ?>
                <p class="forumsthreadtags"><strong><?php echo $words->get("group"); ?>:</strong>
                    <a href="groups/<?php echo $this->_model->getGroupName($topic->topicinfo->IdGroup); ?>">
                        <?php echo $this->_model->getGroupName($topic->topicinfo->GroupName); ?>
                    </a>
                </p>
                <?php
            }
            ?>

            <?php

            $url = ForumsView::getURI() . '';
            $breadcrumb = '';




            if (isset($topic->topicinfo->continent) && $topic->topicinfo->continent) {
                $url = $url . 'k' . $topic->topicinfo->continent . '-' . Forums::$continents[$topic->topicinfo->continent] . '/';
                $breadcrumb .= '<a href="' . $url . '">' . Forums::$continents[$topic->topicinfo->continent] . '</a> ';


                    if (isset($topic->topicinfo->adminname) && $topic->topicinfo->adminname) {
                        $url = $url . 'a' . $topic->topicinfo->admincode . '-' . $topic->topicinfo->adminname . '/';
                        $breadcrumb .= '&raquo; <a href="' . $url . '">' . $topic->topicinfo->adminname . '</a> ';



        }

        if ($topic->topicinfo->IsClosed) {
            echo " &nbsp;&nbsp;&nbsp;<span class=\"forumsthreadtags\"><strong> this thread is closed</strong>";
        }
        ?>
        <?php
        if ($User) {
            ?>


                if ((!$topic->topicinfo->IsClosed) and ($topic->topicinfo->CanReply)) {
                    ?>
                    <a class="button"
                       href="<?= $replyuri ?>"><?= $words->getBuffered('ForumReply') ?></a><?= $words->flushBuffer() ?>
                    <?php
                }
                ?>
            </div>

    <div id="forumsthreadreplytop" >
      <span class="button"><a href="
      <?php

      if (isset($topic->IdSubscribe)) {
        echo ForumsView::getURI()."/subscriptions/unsubscribe/thread/",$topic->IdSubscribe,"/",$topic->IdKey,"\">",$words->getBuffered('ForumUnsubscribe'),"</a></span>",$words->flushBuffer();
      }
      else {
        echo ForumsView::getURI()."/subscribe/thread/",$topic->IdThread,"\">",$words->getBuffered('ForumSubscribe'),"</a></span>",$words->flushBuffer();
      }
      if ((!$topic->topicinfo->IsClosed)and($topic->topicinfo->CanReply)) {
        ?>
    <?php
    // counting for background switch trick
    $cntx = '1';

    }


        if (!$topic->topicinfo->IsClosed) {
            ?>
            <div id="forumsthreadreplybottom"><span class="button"><a
                        href="<?php echo $replyuri; ?>"><?php echo $words->getBuffered('ForumReply');; ?></a></span><?php echo $words->flushBuffer() ?>
            </div>
            <?php
        }

    }
}
?>
