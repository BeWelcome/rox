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


require_once ("menus.php");

function DisplayMissions() {
	global $title;
	$title = ww('MissionsPage');
	require_once "header.php";
	Menu1("missions.php", ww('MissionsPage')); // Displays the top menu
	Menu2("aboutus.php", ww('GetAnswers')); // Displays the second menu

	?>
	  <div id="main">
	     <div id="teaser_bg">
	     <div id="teaser">
	     <h1><?php echo $title; ?></h1>
</div>
<?php menugetanswers("missions.php", $title); ?>

</div>
<?php ShowAds(); ?>
<div id="col3" class="twocolumns">
<div id="col3_content" class="clearfix">
<div class="info">

<h3><?php echo ww("OurMission"); ?></h3>
<q><?php echo ww("OurMissionQuote") ?></q>
<p><?php echo ww("OurMissionText") ?></p>
<h3><?php echo ww("OurAim") ?></h3>
<p><?php echo ww("OurAimText") ?></p>
</div>
	<?php
	require_once "footer.php";
}
?>