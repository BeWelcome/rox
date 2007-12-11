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
  <div class="row">
    <h3><?php echo $words->getFormatted('ForumBrowse'); ?></h3>
    <div class="l category">
      <h4 class="floatbox"><?php echo '<img src="styles/YAML/images/iconsfam/folder_page.png" alt="'. $words->get('tags') .'" title="'. $words->get('tags') .'" class="forum_icon" />';?><?php echo $words->getFormatted('ForumByCategory'); ?></h4>
      <ul>
<?php
	foreach ($top_tags as $tagid => $tag) {
		echo '<li><a href="forums/t'.$tagid.'-'.rawurlencode($tag->tag).'">'.$tag->tag.'</a><br />
			<span class="forums_tag_description">'.$tag->tag_description.'</span>
		</li>';
	}

?>
      </ul>
    </div> <!-- l -->


    <div class="l region">
      <h4 class="floatbox"><?php echo '<img src="styles/YAML/images/iconsfam/world.png" alt="'. $words->get('geo') .'" title="'. $words->get('geo') .'" class="forum_icon" />';?><?php echo $words->getFormatted('ForumByContinent'); ?></h4>
      <ul class=" floatbox">
        <li><a href="forums/kAF-Africa"><?php echo $words->getFormatted('Africa'); ?></a></li>
        <li><a href="forums/kAN-Antarctica"><?php echo $words->getFormatted('Antarctica'); ?></a></li>
        <li><a href="forums/kAS-Asia"><?php echo $words->getFormatted('Asia'); ?></a></li>
        <li><a href="forums/kEU-Europe"><?php echo $words->getFormatted('Europe'); ?></a></li>
        <li><a href="forums/kNA-North America"><?php echo $words->getFormatted('NorthAmerica'); ?></a></li>
        <li><a href="forums/kSA-South Amercia"><?php echo $words->getFormatted('SouthAmerica'); ?></a></li>
        <li><a href="forums/kOC-Oceania"><?php echo $words->getFormatted('Oceania'); ?></a></li>
      </ul>
    </div> <!-- l -->

    <div class="l floatbox tags">
      <h4 class="floatbox"><?php echo '<img src="styles/YAML/images/iconsfam/tag_blue.png" alt="'. $words->get('tags') .'" title="'. $words->get('tags') .'" class="forum_icon" />';?><?php echo $words->getFormatted('ForumByTag'); ?></h4>
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
		
   		$taglist .=  '<a href="forums/t'.$tag->tagid.'-'.rawurlencode($tag->tag).'" class="'.$class.'">'.$tag->tag.'</a>&nbsp;:: ';

	}
   	$taglist = rtrim($taglist, ': ');
    echo $taglist;


	echo '</div>';
?>
    </div> <!-- l floatbox tags -->
  </div> <!-- row -->
  
<br style="clear: both;" />
<?php
	$uri = 'forums/';
	if ($threads = $boards->getThreads()) {
?>
  <div class="row">
<?php  if ($User) { ?>
    <div class="r">
      <span class="button"><a href="forums/new"><?php echo $words->getFormatted('ForumNewTopic'); ?></a></span>
    </div> <!-- r -->
<?php } ?>    
    <h3><?php echo $words->getFormatted('ForumRecentPosts'); $boards->getTotalThreads(); ?></h3>
  </div><!--  row -->
<?php
		require TEMPLATE_DIR.'apps/forums/boardthreads.php';
?>

</div> <!-- Forum-->
<?php
	}
?>