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

<div id="forum">

  <h3><?php echo $words->getFormatted('ForumBrowse'); ?></h3>
    <div class="subcolumns">
      <div class="c33l">
        <div class="subcl category">
          <h4 class="floatbox"><?php echo '<img src="styles/YAML/images/iconsfam/folder_page.png" alt="'. $words->getBuffered('tags') .'" title="'. $words->getBuffered('tags') .'" class="forum_icon" />';?>&nbsp;<?php echo $words->flushBuffer(); ?><?php echo $words->getFormatted('ForumByCategory'); ?></h4>
          <ul>
          <?php
            foreach ($top_tags as $tagid => $tag) {
//              echo '<li><a href="forums/t'.$tagid.'-'.rawurlencode($tag->tag).'">'.$tag->tag.'</a><br />
			   $TagName=$words->fTrad($tag->IdName) ;
              echo '<li><a href="forums/t'.$tagid.'-'.rawurlencode($TagName).'">'.$TagName.'</a><br />
                <span class="forums_tag_description">'.$tag->tag_description.'</span></li>';
            }
            ?>
          </ul>
        </div> <!-- subcl -->
      </div> <!-- c33l -->

      <div class="c33l">
        <div class="subc region">
          <h4 class="floatbox"><?php echo '<img src="styles/YAML/images/iconsfam/world.png" alt="'. $words->getBuffered('geo') .'" title="'. $words->getBuffered('geo') .'" class="forum_icon" />';?>&nbsp;<?php echo $words->flushBuffer(); ?><?php echo $words->getFormatted('ForumByContinent'); ?></h4>
          <ul class=" floatbox">
            <li><a href="forums/kAF-Africa"><?php echo $words->getBuffered('Africa'); ?></a><?php echo $words->flushBuffer(); ?></li>
            <li><a href="forums/kAN-Antarctica"><?php echo $words->getBuffered('Antarctica'); ?></a><?php echo $words->flushBuffer(); ?></li>
            <li><a href="forums/kAS-Asia"><?php echo $words->getBuffered('Asia'); ?></a><?php echo $words->flushBuffer(); ?></li>
            <li><a href="forums/kEU-Europe"><?php echo $words->getBuffered('Europe'); ?></a><?php echo $words->flushBuffer(); ?></li>
            <li><a href="forums/kNA-North America"><?php echo $words->getBuffered('NorthAmerica'); ?></a><?php echo $words->flushBuffer(); ?></li>
            <li><a href="forums/kSA-South Amercia"><?php echo $words->getBuffered('SouthAmerica'); ?></a><?php echo $words->flushBuffer(); ?></li>
            <li><a href="forums/kOC-Oceania"><?php echo $words->getBuffered('Oceania'); ?></a><?php echo $words->flushBuffer(); ?></li>
          </ul>
        </div> <!-- subc -->
      </div> <!-- c33l -->

      <div class="c33r">
        <div class="subcr tags">
          <h4 class="floatbox"><?php echo '<img src="styles/YAML/images/iconsfam/tag_blue.png" alt="'. $words->getBuffered('tags') .'" title="'. $words->getBuffered('tags') .'" class="forum_icon" />';?>&nbsp;<?php echo $words->flushBuffer(); ?><?php echo $words->getFormatted('ForumByTag'); ?></h4>
<?php
//      	$taglist = '';
//      	foreach ($all_tags as $tagid => $tag) {
//			if 
//      		$taglist .=  '<a href="forums/t'.$tagid.'-'.rawurlencode($tag).'">'.$tag.'</a>&nbsp;:: ';
//      	}
//      	$taglist = rtrim($taglist, ': ');
//      	echo $taglist;
      
// New Tag Cloud
    
    echo '<div id="tagcloud">';
    if($all_tags_maximum == 0)
        $all_tags_maximum = 1;
    $maximum = $all_tags_maximum;
    $taglist = '';
    foreach ($all_tags as $tagid => $tag) {

        $percent = floor(($tag->counter / $maximum) * 100);
    
        if ($percent <20) {
            $class = 'tag_smallest';
            } elseif ($percent>= 20 and $percent <40) {
                $class = 'tag_small';
            } elseif ($percent>= 40 and $percent <60) {
                $class = 'tag_medium';
            } elseif ($percent>= 60 and $percent <80) {
                $class = 'tag_large';
            } else {
            $class = 'tag_largest';
        }
        
	     $TagName=$words->fTrad($tag->IdName) ;
        $taglist .=  '<a href="forums/t'.$tag->tagid.'-'.rawurlencode($TagName).'" class="'.$class.'">'.$TagName.'</a>&nbsp;:: ';

    }
   	$taglist = rtrim($taglist, ': ');
    echo $taglist;

?>
</div>
        </div> <!-- subcr -->
      </div> <!-- c33r -->
    </div> <!-- subcolumns -->
  
  
<br style="clear: both;" />
<?php
    $uri = 'forums/';
    if ($threads = $boards->getThreads()) {
?>
  <div class="row">
<?php  if ($User) { ?>
    <div class="r">
      <span class="button"><a href="forums/new"><?php echo $words->getBuffered('ForumNewTopic'); ?></a></span><?php echo $words->flushBuffer(); ?>
    </div> <!-- r -->
<?php } ?>    
    <h3><?php echo $words->getFormatted('ForumRecentPosts'); $boards->getTotalThreads(); ?></h3>
  </div><!--  row -->
<?php
        require 'boardthreads.php';
?>
</div> <!-- Forum-->
<?php
    }
?>
<br /><br />
<a href="rss/forumthreads"><img src="images/icons/feed.png" alt="RSS feed" /></a>