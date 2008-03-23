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


* @author     Original author unknown
* @author     Michael Dettbarn (lupochen) <mail@lupochen.com>

*/
    $i18n = new MOD_i18n('date.php');
    $format = $i18n->getText('format');

    //$i18n = new MOD_i18n('apps/forums/board.php');
    //$boardText = $i18n->getText('boardText');
    $words = new MOD_words();

    $User = APP_User::login();
    $can_del = $User && $User->hasRight('delete@forums');
    $can_edit_own = $User && $User->hasRight('edit_own@forums');
    $can_edit_foreign = $User && $User->hasRight('edit_foreign@forums');

?>

<h2><?php echo $words->fTrad($topic->topicinfo->IdTitle); ?></h2>
<span class="forumsthreadtags"><strong>Tags:</strong> <?php

    $url = 'forums/';
    $breadcrumb = '';
    if (isset($topic->topicinfo->continent) && $topic->topicinfo->continent) {
        $url = $url.'k'.$topic->topicinfo->continent.'-'.Forums::$continents[$topic->topicinfo->continent].'/';
        $breadcrumb .= '<a href="'.$url.'">'.Forums::$continents[$topic->topicinfo->continent].'</a> ';
        
        if (isset($topic->topicinfo->countryname) && $topic->topicinfo->countryname) {
            $url = $url.'c'.$topic->topicinfo->countrycode.'-'.$topic->topicinfo->countryname.'/';
            $breadcrumb .= ':: <a href="'.$url.'">'.$topic->topicinfo->countryname.'</a> ';

            if (isset($topic->topicinfo->adminname) && $topic->topicinfo->adminname) {
                $url = $url.'a'.$topic->topicinfo->admincode.'-'.$topic->topicinfo->adminname.'/';
                $breadcrumb .= ':: <a href="'.$url.'">'.$topic->topicinfo->adminname.'</a> ';
                
                if (isset($topic->topicinfo->geonames_name) && $topic->topicinfo->geonames_name) {
                    $url = $url.'g'.$topic->topicinfo->geonameid.'-'.$topic->topicinfo->geonames_name.'/';
                    $breadcrumb .= ':: <a href="'.$url.'">'.$topic->topicinfo->geonames_name.'</a> ';
                }
            }
        }
    }


	 for ($ii=0;$ii<$topic->topicinfo->NbTags;$ii++) {
		$wordtag=$words->fTrad($topic->topicinfo->IdTag[$ii]) ;
		if ($breadcrumb) {
		   $breadcrumb .= '|| ';
		}
       $url = $url.'t'.$topic->topicinfo->IdTag[$ii].'-'.$wordtag.'/';
        $breadcrumb .= '<a href="'.$url.'">'.$wordtag.'</a> ';
    } // end of for $ii

/* old initial mytravelbook forum	 
    if (isset($topic->topicinfo->tag1) && $topic->topicinfo->tag1) {
        if ($breadcrumb) {
            $breadcrumb .= ':: ';
        }
        $url = $url.'t'.$topic->topicinfo->tag2id.'-'.$topic->topicinfo->tag1.'/';
        $breadcrumb .= '<a href="'.$url.'">'.$topic->topicinfo->tag1.'</a> ';
    }
    if (isset($topic->topicinfo->tag2) && $topic->topicinfo->tag2) {
        if ($breadcrumb) {
            $breadcrumb .= ':: ';
        }
        $url = $url.'t'.$topic->topicinfo->tag2id.'-'.$topic->topicinfo->tag2.'/';
        $breadcrumb .= '<a href="'.$url.'">'.$topic->topicinfo->tag2.'</a> ';
    }
    if (isset($topic->topicinfo->tag3) && $topic->topicinfo->tag3) {
        if ($breadcrumb) {
            $breadcrumb .= ':: ';
        }
        $url = $url.'t'.$topic->topicinfo->tag3id.'-'.$topic->topicinfo->tag3.'/';
        $breadcrumb .= '<a href="'.$url.'">'.$topic->topicinfo->tag3.'</a> ';
    }
    if (isset($topic->topicinfo->tag4) && $topic->topicinfo->tag4) {
        if ($breadcrumb) {
            $breadcrumb .= ':: ';
        }
        $url = $url.'t'.$topic->topicinfo->tag4id.'-'.$topic->topicinfo->tag4.'/';
        $breadcrumb .= '<a href="'.$url.'">'.$topic->topicinfo->tag4.'</a> ';
    }
    if (isset($topic->topicinfo->tag5) && $topic->topicinfo->tag5) {
        if ($breadcrumb) {
            $breadcrumb .= ':: ';
        }
        $url = $url.'t'.$topic->topicinfo->tag5id.'-'.$topic->topicinfo->tag5.'/';
        $breadcrumb .= '<a href="'.$url.'">'.$topic->topicinfo->tag5.'</a> ';
    }
*/
    echo $breadcrumb;

?></span>
<?php
if ($User) {
?>

    <div id="forumsthreadreplytop">
	 <span class="button"><a href="
	 <?php 
	 if (isset($topic->IdSubscribe)) {
	 	echo "forums/subscriptions/unsubscribe/thread/",$topic->IdSubscribe,"/",$topic->IdKey,"\">",$words->getBuffered('ForumUnsubscribe'),"</a></span>",$words->flushBuffer();
	 }
	 else {
	 	echo "forums/subscribe/thread/",$topic->IdThread,"\">",$words->getBuffered('ForumSubscribe'),"</a></span>",$words->flushBuffer(); 
	 }  
	 ?>
	 <span class="button"><a href="<?php echo $uri; ?>reply"><?php echo $words->getBuffered('ForumReply'); ?></a></span><?php echo $words->flushBuffer() ?>
	 </div>

<?php

} // end if ($User)
    
    // counting for background switch trick
    $cntx = '1';
    foreach ($topic->posts as $post) {
        $cnt = $cntx + 1;
        require TEMPLATE_DIR.'apps/forums/singlepost.php';
        $cntx = $cnt;
    }
        
if ($User) {

?>
<div id="forumsthreadreplybottom"><span class="button"><a href="<?php echo $uri; ?>reply"><?php echo $words->getBuffered('ForumReply');; ?></a></span><?php echo $words->flushBuffer() ?></div>
<?php

}
?>