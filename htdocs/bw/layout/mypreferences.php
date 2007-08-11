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
require_once ("profilepage_header.php");

function DisplayMyPreferences($TPref, $m) {
	global $title;
	$title = ww('MyPreferences');
	require_once "header.php";

	Menu1(); // Displays the top menu
	Menu2("member.php?cid=".$m->Username); // even if in preference we are in the myprofile menu

	// Header of the profile page
	DisplayProfilePageHeader( $m );

	menumember("mypreferences.php?cid=" . $m->id, $m);
	ShowActions(""); // Show the Actions
	ShowAds(); // Show the Ads

	// middle column
	echo "      <div id=\"col3\"> \n"; 
	echo "	    <div id=\"col3_content\" class=\"clearfix\"> \n"; 
	echo "				<div class=\"info\">";
	echo "						<form method=\"post\" action=\"\" id=\"preferences\">";

	echo "<table id=\"preferencesTable\">";
	echo "<input type=hidden name=cid value=$m->id>";
	echo "<input type=hidden name=action value=Update>";

	$iiMax = count($TPref);
	for ($ii = 0; $ii < $iiMax; $ii++) {
		$rr = $TPref[$ii];
		echo "<tr><td>";
		echo "<p class=\"preflabel\">", ww($rr->codeName), "</p>";
		echo "</td>";
		echo "<td>";
		echo ww($rr->codeDescription);
		echo "</td>";
		echo "<td>";

		if ($rr->Value != "") {
			$Value = $rr->Value;
		} else {
			$Value = $rr->DefaultValue;
		}
		echo eval ($rr->EvalString);
		echo "</td>";
	} // end of for ii
	echo "<tr><td>";
	echo "<p class=\"preflabel\">", ww("PreferencePublicProfile"), "</p>";
	echo "</td>";
	echo "<td>";
	echo ww("PreferencePublicProfileDesc");
	echo "</td>";
	echo "<td>";
	if (isset ($m->TPublic->IdMember))
		$Value = "Yes"; // Public profile is not in preference table but in memberspublicprofiles
	else
		$Value = "No";
	echo "\n<select name=PreferencePublicProfile  class=\"prefsel\">";
	echo "<option value=Yes ";
	if ($Value == "Yes")
		echo " selected ";
	echo ">", ww("Yes"), "</option>\n";
	echo "<option value=No";
	if ($Value == "No")
		echo " selected ";
	echo ">", ww("No"), "</option>\n";
	echo "</select>\n";
	echo "</td>";

	echo "\n<tr><td align=center colspan=3><input type=submit id=submit></td>";
	echo "</table>\n";
	echo "</form>\n";
	echo "	</div>";

	require_once "footer.php";
	exit(0) ;
}

function DisplayOneUpdate($m,$PrefName, $NewValue) {
	global $title;
	$title = ww('MyPreferences');
	require_once "header.php";

	Menu1(); // Displays the top menu
	Menu2("member.php?cid=".$m->Username); // even if in preference we are in the myprofile menu

	// Header of the profile page
	DisplayProfilePageHeader( $m );

	menumember("mypreferences.php?cid=" . $m->id, $m);

	ShowActions(""); // Show the Actions
	ShowAds(); // Show the Ads

	// middle column
	echo "      <div id=\"col3\"> \n"; 
	echo "	    <div id=\"col3_content\" class=\"clearfix\"> \n"; 
	echo "				<div class=\"info\">";
	echo ww("OnePreferenceUpdated",ww($PrefName),$NewValue) ;
	echo "<br><br><center><a href=\"".bwlink("mypreferences.php")."\">", ww("MyPreferences"), "</a></center>\n";
	echo "				</div>";
	echo "			</div>";
	echo "	</div>";

	require_once "footer.php";
	exit(0) ;
} // DisplayOneUpdate

?>
