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

?>
    <div class="row">
    <div class="col-12 mb-2">
<?php
// This means no thread was fetch or that it was outside visibility
if ((!isset($topic->topicinfo->IdTitle)) and (!isset($topic->topicinfo->ThreadDeleted))) {
    echo "<h2>", $topic->topicinfo->title, "</h2>";
} else {

    $topic->topicinfo->IsClosed = false;
    $expireDate = $topic->topicinfo->expiredate;
    if ($expireDate !== null && $expireDate != "0000-00-00 00:00:00") {
        $topic->topicinfo->IsClosed = (strtotime($topic->topicinfo->expiredate) <= time());
    }

    $words = new MOD_words();

    $User = $this->_model->getLoggedInMember();
    $can_del = $User && $User->hasRight('delete@forums'); // Not to use anymore (JeanYves)
    $can_edit_own = $User;
    //    $can_edit_own = $User && $User->hasRight('edit_own@forums');
    $can_edit_foreign = $User && $User->hasRight('edit_foreign@forums');

    echo '<div class="clearfix">';
    echo '<h2 class="mb-0 float-left">';

    if ($topic->topicinfo->ThreadDeleted == 'Deleted') {
        echo "[Deleted]";
    }
    if ($topic->topicinfo->ThreadVisibility == 'ModeratorOnly') {
        echo "[ModOnly]";
    }

    echo $words->fTrad($topic->topicinfo->IdTitle);
    if ($User) {
        echo '<a href="/forums/reverse" class="h6 ml-2" title="' . $words->getSilent('ReverseOrder') . '" ><i class="fa fa-exchange-alt fa-rotate-90" title="'
            . $words->getSilent('ReverseOrder') . '" /></i></a></h2> ' . $words->flushBuffer();
    }


    echo "<div class='float-right'>";
    if ($User) {
        if (isset($topic->isGroupSubscribed) && ($topic->isGroupSubscribed)) {
            if (isset($topic->IdSubscribe)) {
                if ($topic->notificationsEnabled > 0) {
                    echo '<a class="btn btn-sm btn-light float-right" href="' . $this->getURI() . '/subscriptions/disable/thread/' . $topic->IdThread
                        . '">' . $words->getBuffered('ForumDisable') . '</a>' . $words->flushBuffer() . PHP_EOL;
                } else {
                    echo '<a class="btn btn-sm btn-light float-right" href="' . $this->getURI() . '/subscriptions/enable/thread/' . $topic->IdThread
                        . '">' . $words->getBuffered('ForumEnable') . '</a>' . $words->flushBuffer() . PHP_EOL;
                }
            } else {
                if ($topic->notificationsEnabled) {
                    echo '<a class="btn btn-sm btn-light float-right" href="' . $this->getURI() . '/subscriptions/disable/thread/' . $topic->IdThread
                        . '">' . $words->getBuffered('ForumDisable') . '</a>' . $words->flushBuffer() . PHP_EOL;
                } else {
                    echo '<a class="btn btn-sm btn-light float-right" href="' . $this->getURI() . '/subscriptions/enable/thread/' . $topic->IdThread
                        . '">' . $words->getBuffered('ForumEnable') . '</a>' . $words->flushBuffer() . PHP_EOL;
                }
            }
        } else {
            if (isset($topic->IdSubscribe)) {
                if ($topic->notificationsEnabled > 0) {
                    echo '<a class="btn btn-sm btn-light float-right" href="' . $this->getURI() . '/subscriptions/disable/thread/' . $topic->IdThread
                        . '">' . $words->getBuffered('ForumDisable') . '</a>' . $words->flushBuffer() . PHP_EOL;
                } else {
                    echo '<a class="btn btn-sm btn-light float-right" href="' . $this->getURI() . '/subscriptions/enable/thread/' . $topic->IdThread
                        . '">' . $words->getBuffered('ForumEnable') . '</a>' . $words->flushBuffer() . PHP_EOL;
                }
                echo '<a class="btn btn-sm btn-light float-right" href="' . $this->getURI() . '/subscriptions/unsubscribe/thread/' . $topic->IdSubscribe
                    . '/' . $topic->IdKey . '">' . $words->getBuffered('ForumUnsubscribe') . '</a>' . $words->flushBuffer() . PHP_EOL;
            } else {
                echo '<a class="btn btn-sm btn-light float-right" href="' . $this->getURI() . '/subscribe/thread/' . $topic->IdThread . '">'
                    . $words->getBuffered('ForumSubscribe') . '</a>' . $words->flushBuffer() . PHP_EOL;
            }
        }
        $replyuri = preg_replace('#/page.{1,3}/#', '/', $uri . 'reply');
        if ((!$topic->topicinfo->IsClosed) and ($topic->topicinfo->CanReply)) {
            ?>
            <a class="btn btn-primary btn-sm float-right"
               href="<?= $replyuri ?>"><?= $words->getBuffered('ForumReply') ?></a><?= $words->flushBuffer() ?>
            <?php
        }

    }
    ?>

    </div>
    </div>
    <?php
    $replyuri = preg_replace('#/page.{1,3}/#', '/', $uri . 'reply');
    ?>
    <?php
    if ($topic->topicinfo->IsClosed) {
        echo "<span class='forumsthreadtags'><strong>"
            . $words->getFormatted('threadclosed',
                substr(ServerToLocalDateTime($topic->topicinfo->expiredate, $this->getSession()), 0, 10)
            )
            . "</strong></span>";
    }
    ?>
    </div>
    </div>
    <?php
// counting for background switch trick
    $cntx = '1';

    for ($ii = 0; $ii < count($topic->posts); $ii++) {
        $post = $topic->posts[$ii];
        $cnt = $ii + 1;
        require 'singlepost.php';
        $cntx = $cnt;
    }

    if ($User) {

        if (!$topic->topicinfo->IsClosed) {
            ?>
            <div class="row align-content-between mb-2">
                <div class="col">
                    <a href="<?php echo $replyuri; ?>"
                       class="btn btn-primary btn-sm"><?php echo $words->getBuffered('ForumReply'); ?></a>
                    <?php echo $words->flushBuffer() ?>
                </div>
            </div>
            <?php
        }

    }
}

