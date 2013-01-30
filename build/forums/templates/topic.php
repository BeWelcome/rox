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
  if ( (!isset($topic->topicinfo->IdTitle)) and (!isset($topic->topicinfo->ThreadDeleted))) { 
    echo "<h2 class=\"forumstopic\">",$topic->topicinfo->title,"</h2>" ;
  }
  else  {
    //$i18n = new MOD_i18n('apps/forums/board.php');
    //$boardText = $i18n->getText('boardText');
    $words = new MOD_words();

    $User = APP_User::login();
    $can_del = $User && $User->hasRight('delete@forums'); // Not to use anymore (JeanYves)
    $can_edit_own = $User ;
//    $can_edit_own = $User && $User->hasRight('edit_own@forums');
    $can_edit_foreign = $User && $User->hasRight('edit_foreign@forums');

    if (!isset($topic->topicinfo->IsClosed)) {
      $topic->topicinfo->IsClosed=false ;
    }
    echo "<h2 class=\"forumstopic\">" ;

    if ($topic->topicinfo->ThreadDeleted=='Deleted') {
        echo "[Deleted]" ;
    }
    if ($topic->topicinfo->ThreadVisibility=='ModeratorOnly') {
        echo "[ModOnly]" ;
    }

    echo $words->fTrad($topic->topicinfo->IdTitle);
    if ($User) {
      $url=$_SERVER['REQUEST_URI'] ;
      if (strpos($url,"/reverse")===false) { // THis in order to avoid to concatenate /reverse twice
        $url.="/reverse"  ;
      }
      echo " <a href=\"".$url."\" title=\"reverse the display list\" ><img src=\"images/icons/reverse_order.png\" alt=\"reverse\" /></a> " ;
    }
    echo "</h2>";

    ?>

    <div class="forumthreadinfo">
    <div class="float_left">
    <?php
    if ($topic->topicinfo->IdGroup>0) {
      ?>
      <p class="forumsthreadtags"><strong><?php echo $words->get("group");?>:</strong>
        <a href="groups/<?php echo $this->_model->getGroupName($topic->topicinfo->IdGroup);?>">
          <?php echo $this->_model->getGroupName($topic->topicinfo->GroupName);?>
        </a>
      </p>
      <?php
    }
    ?>
    
    <?php

    $url = ForumsView::getURI().'';
    $breadcrumb = '';

    // Append slash to URL if it's not there yet
    if (substr($url, -1) != '/') {
      $url = $url . '/';
    }
    
    $replyuri = preg_replace('#/page.{1,3}/#', '/', $uri . 'reply');

    $tagBase = $url;

    if (isset($topic->topicinfo->continent) && $topic->topicinfo->continent) {
      $url = $url.'k'.$topic->topicinfo->continent.'-'.Forums::$continents[$topic->topicinfo->continent].'/';
      $breadcrumb .= '<a href="'.$url.'">'.Forums::$continents[$topic->topicinfo->continent].'</a> ';

      if (isset($topic->topicinfo->countryname) && $topic->topicinfo->countryname) {
        $url = $url.'c'.$topic->topicinfo->countrycode.'-'.$topic->topicinfo->countryname.'/';
        $breadcrumb .= '&raquo; <a href="'.$url.'">'.$topic->topicinfo->countryname.'</a> ';

        if (isset($topic->topicinfo->adminname) && $topic->topicinfo->adminname) {
          $url = $url.'a'.$topic->topicinfo->admincode.'-'.$topic->topicinfo->adminname.'/';
          $breadcrumb .= '&raquo; <a href="'.$url.'">'.$topic->topicinfo->adminname.'</a> ';

          if (isset($topic->topicinfo->geonames_name) && $topic->topicinfo->geonames_name) {
            $url = $url.'g'.$topic->topicinfo->geonameid.'-'.$topic->topicinfo->geonames_name.'/';
            $breadcrumb .= '&raquo; <a href="'.$url.'">'.$topic->topicinfo->geonames_name.'</a> ';
          }
        }
      }
    }


    for ($ii=0;$ii<$topic->topicinfo->NbTags;$ii++) {
      $wordtag=$words->fTrad($topic->topicinfo->IdName[$ii]) ;
      if ($breadcrumb) {
        $breadcrumb .= '|| ';
      }
      $tagUrl = $tagBase . 't' . $topic->topicinfo->IdTag[$ii] . '-'
        . $wordtag;
      $breadcrumb .= '<a href="' . $tagUrl . '">' . $wordtag . '</a> ';
    } // end of for $ii
  ?>
  <?php if ($breadcrumb != ""): ?>
    <p class="forumsthreadtags">
      <strong><?php echo $words->get("forum_label_tags");?>:</strong>
      <?php echo $breadcrumb; ?>
    </p>
  <?php endif; ?>
  </div>
  <?php
    $topic->topicinfo->IsClosed=false ;
    if ($topic->topicinfo->expiredate!="0000-00-00 00:00:00") {
      echo "&nbsp;&nbsp;&nbsp;<span class=\"forumsthreadtags\"><strong> expiration date :",ServerToLocalDateTime($topic->topicinfo->expiredate),"</strong>" ;
      $topic->topicinfo->IsClosed=(strtotime($topic->topicinfo->expiredate)<=time()) ;
    }

    if ($topic->topicinfo->IsClosed) {
      echo " &nbsp;&nbsp;&nbsp;<span class=\"forumsthreadtags\"><strong> this thread is closed</strong>" ;
    }
  ?>
  <?php
  if ($User) {
  ?>

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
        <span class="button"><a href="<?php echo $replyuri; ?>"><?php echo $words->getBuffered('ForumReply'); ?></a></span><?php echo $words->flushBuffer() ?>

      <?php
      }
      ?>
    </div>

  <?php

  } // end if ($User)
?>
   </div>
   <?php
  // counting for background switch trick
  $cntx = '1';

  if ($this->_model->ForumOrderList=="No") {
    for ($ii=count($topic->posts)-1;$ii>=0;$ii--) {
      $post=$topic->posts[$ii] ;
      $cnt = $ii + 1;
      require 'singlepost.php';
      $cntx = $cnt;
    }
  }
  else { // Not logged member will always see the forum in ascending order
    for ($ii=0;$ii<count($topic->posts);$ii++) {
      $post=$topic->posts[$ii] ;
      $cnt = $ii + 1;
      require 'singlepost.php';
      $cntx = $cnt;
    }
  }

  if ($User) {

    if (!$topic->topicinfo->IsClosed) {
  ?>
  <div id="forumsthreadreplybottom"><span class="button"><a href="<?php echo $replyuri; ?>"><?php echo $words->getBuffered('ForumReply');; ?></a></span><?php echo $words->flushBuffer() ?></div>
  <?php
    }

  }
}
?>
