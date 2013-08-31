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

    $request = PRequest::get()->request;
    $uri = htmlspecialchars(implode('/', $request), ENT_QUOTES);
    $uri = rtrim($uri, '/').'/';
?>
    <h3><?php echo $this->words->getFormatted('Actions'); ?></h3>
    <ul class="linklist">

<?php 
      /*
    <li class="icon fam_commentadd">
        <a href="<?php echo $uri,'new'; ?>"><?php echo $this->words->get('ForumNewTopic'); ?></a>
    </li>
      */
?>

    <li>
        <a href="groups/search"><?php echo $this->words->get('GroupsSearchHeading'); ?></a>
    </li>
    <?php
//	if ($this->_model->GetTopMode()==Forums::CV_TOPMODE_CATEGORY) {
//		echo '<li><a href="forums/landing">' . $this->words->get('ForumLanding') . '</a></li>' ;
//		echo '<li><a href="forums/lastposts">' . $this->words->get('ForumLastPost') . '</a></li>' ;
//	}
//	if ($this->_model->GetTopMode()==Forums::CV_TOPMODE_LASTPOSTS) {
//		echo '<li><a href="forums/landing">' . $this->words->get('ForumLanding') . '</a></li>' ;
//		echo '<li><a href="forums/category">' . $this->words->get('ForumByCategory') . '</a></li>' ;
//	}
//	if ($this->_model->GetTopMode()==Forums::CV_TOPMODE_FORUM) {
//		echo '<li><a href="forums/landing">' . $this->words->get('ForumLanding') . '</a></li>' ;
//		echo '<li><a href="forums/category">' . $this->words->get('ForumByCategory') . '</a></li>' ;
//	}
//	if ($this->_model->GetTopMode()==Forums::CV_TOPMODE_LANDING) {
//		echo '<li><a href="forums/lastposts">' . $this->words->get('ForumLastPost') . '</a></li>' ;
//		echo '<li><a href="forums/category">' . $this->words->get('ForumByCategory') . '</a></li>' ;
//	}
	?>

    <li><a href="forums/rules"><?php echo $this->words->get('ForumRulesShort'); ?></a></li>
    <li><a href="http://www.bewelcome.org/wiki/Howto_Forum"><?php echo $this->words->get('ForumLinkToDoc'); ?></a></li>
<?php  if (isset($_SESSION["IdMember"])) {
			echo "<li><a href=\"forums/subscriptions\">",$this->words->get('forum_YourSubscription'),"</a></li>"; 
			if ($this->BW_Right->HasRight("ForumModerator")) {
				echo '<li><a href="forums/reporttomod/AllMyReport">All reports for me</a></li>' ;
				echo '<li><a href="forums/reporttomod/MyReportActive">Pending reports for me ('.$this->_model->countReportList($_SESSION["IdMember"],"('Open','OnDiscussion')").')</a></li>' ;
				echo '<li><a href="forums/reporttomod/AllActiveReports">All pending reports ('.$this->_model->countReportList(0,"('Open','OnDiscussion')").')</a></li>' ;
			}
		}
		?>
</ul>
