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
<?php
	$ToogleTagCloud=false ;
// if ($User) $ToogleTagCloud=true ;
	if ($ToogleTagCloud) { // If We want to see the TagCloud
		require 'tagcloud_and_toptags.php';
	}

	$uri = 'forums/';


	if ($User) { 
		?>
		<div class="r">
		<span class="button"><a href="forums/new"><?php echo $this->words->getBuffered('ForumNewTopic'); ?></a></span>
		<?php echo $this->words->flushBuffer(); ?>
		</div> <!-- r -->
		<?php 
	}

	foreach ($this->_model->ListBoards as $list) {
		if ($threads = $list->threads) {
?>
			<br style="clear: both;" />
			<?php
			if (isset($list->IdName)) {
				$TagName=$this->words->fTrad($list->IdName) ;			
				$tag_description=$this->words->fTrad($list->IdDescription) ;			
				echo '<h3>' ;
				echo '<div class="row">';
				if ($_SESSION["IdMember"]) { // Not needed for not logged in member (like google)
					echo '<a href="javascript:void();" id="HideUnhide_',$list->IdTagCategory,'">+/-</a> ' ;
					?>
					<script language="Javascript" type="text/javascript">
					<!--
						$('HideUnhide_<?=$list->IdTagCategory?>').observe('click', function(){
							show_hide('IdForum_<?=$list->IdTagCategory?>') ;
						});
					//!-->
					</script>
					<?php
				}
				echo '<a href="forums/t'.$list->IdTagCategory.'-'.rawurlencode($TagName).'" title="'.$tag_description.'">'.$TagName.'</a>' ;
				echo '</h3>' ;
			}
			else {
				$TagName=$this->words->getFormatted('ForumNoSpecificCategories') ;			
				$tag_description="here goes the unclassfied forums post" ;			
				$list->IdTagCategory="NoCategory" ;
				echo '<h3>' ;
				if ($_SESSION["IdMember"]) { // Not needed for not logged in member (like google)
					echo '<a href="javascript:void();" id="HideUnhide_',$list->IdTagCategory,'">+/-</a> ' ;
					?>
					<script language="Javascript" type="text/javascript">
					<!--
						$('HideUnhide_<?=$list->IdTagCategory?>').observe('click', function(){
							show_hide('IdForum_<?=$list->IdTagCategory?>') ;
						});
					//!-->
					</script>
					<?php
				}
				echo '<a  title="'.$tag_description.'">',$TagName,'</a></h3>' ;
			}
			?>
			</div><!--  row -->
<?php		echo '<span  id="IdForum_',$list->IdTagCategory,'">' ;
			require 'boardonecategory.php';
			echo '</span>' ;
		}
    } // end of for $this->_model->ListBoards 
?>
</div> <!-- Forum-->
<?php
if ($User) {
?>
<div id="boardnewtopicbottom"><span class="button"><a href="<?php echo $uri; ?>new"><?php echo $this->words->getBuffered('ForumNewTopic'); ?></a></span><?php echo $this->words->flushBuffer(); ?></div>
<?php
}
?>

<script language="Javascript" type="text/javascript">
<!--
	function show_hide(tblid, show) {
		if (tbl = document.getElementById(tblid)) {
			if (null == show) show = tbl.style.display == 'none';
			tbl.style.display = (show ? '' : 'none');
		}
	}
//!-->
</script>

<br /><br />
<a href="rss/forumthreads"><img src="images/icons/feed.png" alt="RSS feed" /></a>