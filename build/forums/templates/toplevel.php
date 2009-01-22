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
?>

<div id="forum">

  <h3><?php echo $this->words->getFormatted('ForumBrowse'); ?></h3>
    <div class="subcolumns">
<!-- Now displays the by category -->
	<div class="c33l">
        <div class="subcl category">
          <h4 class="floatbox"><?php echo '<img src="styles/YAML/images/iconsfam/folder_page.png" alt="'. $this->words->getBuffered('tags') .'" title="'. $this->words->getBuffered('tags') .'" class="forum_icon" />';?>&nbsp;<?php echo $this->words->flushBuffer(); ?><?php echo $this->words->getFormatted('ForumByCategory'); ?></h4>
          <ul>
          <?php
            foreach ($top_tags as $tagid => $tag) {
			   $TagName=$this->words->fTrad($tag->IdName) ;
              echo '<li><a href="forums/t'.$tagid.'-'.rawurlencode($TagName).'">'.$TagName.'</a><br />
                <span class="forums_tag_description">'.$tag->tag_description.'</span></li>';
            }
            ?>
          </ul>
        </div> <!-- subcl -->
      </div> <!-- c33l -->

<!-- Now displays the by continent -->
      <div class="c33l">
        <div class="subc region">
          <h4 class="floatbox"><?php echo '<img src="styles/YAML/images/iconsfam/world.png" alt="'. $this->words->getBuffered('geo') .'" title="'. $this->words->getBuffered('geo') .'" class="forum_icon" />';?>&nbsp;<?php echo $this->words->flushBuffer(); ?><?php echo $this->words->getFormatted('ForumByContinent'); ?></h4>
          <ul class=" floatbox">
            <li><a href="forums/kAF-Africa"><?php echo $this->words->getBuffered('Africa'); ?></a><?php echo $this->words->flushBuffer(); ?></li>
            <li><a href="forums/kAN-Antarctica"><?php echo $this->words->getBuffered('Antarctica'); ?></a><?php echo $this->words->flushBuffer(); ?></li>
            <li><a href="forums/kAS-Asia"><?php echo $this->words->getBuffered('Asia'); ?></a><?php echo $this->words->flushBuffer(); ?></li>
            <li><a href="forums/kEU-Europe"><?php echo $this->words->getBuffered('Europe'); ?></a><?php echo $this->words->flushBuffer(); ?></li>
            <li><a href="forums/kNA-North America"><?php echo $this->words->getBuffered('NorthAmerica'); ?></a><?php echo $this->words->flushBuffer(); ?></li>
            <li><a href="forums/kSA-South Amercia"><?php echo $this->words->getBuffered('SouthAmerica'); ?></a><?php echo $this->words->flushBuffer(); ?></li>
            <li><a href="forums/kOC-Oceania"><?php echo $this->words->getBuffered('Oceania'); ?></a><?php echo $this->words->flushBuffer(); ?></li>
          </ul>
        </div> <!-- subc -->
      </div> <!-- c33l -->

<!-- Now displays the New Tag Cloud -->
      <div class="c33r">
        <div class="subcr tags">
          <h4 class="floatbox"><?php echo '<img src="styles/YAML/images/iconsfam/tag_blue.png" alt="'. $this->words->getBuffered('tags') .'" title="'. $this->words->getBuffered('tags') .'" class="forum_icon" />';?>&nbsp;<?php echo $this->words->flushBuffer(); ?><?php echo $this->words->getFormatted('ForumByTag'); ?></h4>
<?php
//      	$taglist = '';
//      	foreach ($all_tags as $tagid => $tag) {
//			if 
//      		$taglist .=  '<a href="forums/t'.$tagid.'-'.rawurlencode($tag).'">'.$tag.'</a>&nbsp;:: ';
//      	}
//      	$taglist = rtrim($taglist, ': ');
//      	echo $taglist;
      
    
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
        
	     $TagName=$this->words->fTrad($tag->IdName) ;
        $taglist .=  '<a href="forums/t'.$tag->tagid.'" class="'.$class.'">'.$TagName.'</a>&nbsp;:: ';

    }
   	$taglist = rtrim($taglist, ': ');
    echo $taglist;

?>
</div>
        </div> <!-- subcr -->
      </div> <!-- c33r -->
    </div> <!-- subcolumns -->

<!-- Now displays the recent post list -->	
<br style="clear: both;" />
<?php
    $uri = 'forums/';
    if ($threads = $boards->getThreads()) {
?>
  <div class="row">
<?php  if ($User) { ?>
    <div class="r">
      <span class="button"><a href="forums/new"><?php echo $this->words->getBuffered('ForumNewTopic'); ?></a></span><?php echo $this->words->flushBuffer(); ?>
    </div> <!-- r -->
<?php } ?>    
    <h3><?php echo $this->words->getFormatted('ForumRecentPosts'); $boards->getTotalThreads(); ?></h3>
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