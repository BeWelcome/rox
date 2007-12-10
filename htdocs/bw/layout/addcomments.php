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

function DisplayAddComments($TCom, $Username, $IdMember, $m2) {
	global $title;
	global $_SYSHCVOL;
	$title = ww('AddComments');

	require_once "header.php";


	Menu1(); // Displays the top menu
	Menu2("member.php?cid=".$m2->Username); // even if in viewcomment we can be in the myprofile menu

	// Header of the profile page
	DisplayProfilePageHeader( $m2 );

	menumember("viewcomments.php?cid=" . $m2->id, $m2);
  // Prepare the $MenuAction for ShowAction()  
	$MenuAction = "";
	$MenuAction .= "          <li class=\"icon contactmember16\"><a href=\"contactmember.php?cid=" . $m->id . "\">" . ww("ContactMember") . "</a></li>\n";
	$MenuAction .= "          <li class=\"icon addcomment16\"><a href=\"addcomments.php?cid=" . $m->id . "\">" . ww("addcomments") . "</a></li>\n";
	// $MenuAction .= "          <li class=\"icon forumpost16\"><a href=\"todo.php\">".ww("ViewForumPosts")."</a></li>\n";

	if (GetPreference("PreferenceAdvanced")=="Yes") {
      if ($m->IdContact==0) {
	   	  $MenuAction .= "          <li class=\"icon mylist16\"><a href=\"mycontacts.php?IdContact=" . $m->id . "&amp;action=add\">".ww("AddToMyNotes")."</a> </li>\n";
	   }
	   else {
	   	  $MenuAction .= "          <li class=\"icon mylist16\"><a href=\"mycontacts.php?IdContact=" . $m->id . "&amp;action=view\">".ww("ViewMyNotesForThisMember")."</a> </li>\n";
	   }
	}

	if (GetPreference("PreferenceAdvanced")=="Yes") {
      if ($m->IdRelation==0) {
	   	  $MenuAction .= "        <li class=\"icon myrelations16\"><a href=\"myrelations.php?IdRelation=" . $m->id . "&amp;action=add\">".ww("AddToMyRelations")."</a> </li>\n";
	   }
	   else {
	   		$MenuAction .= "        <li class=\"icon myrelations16\"><a href=\"myrelations.php?IdRelation=" . $m->id . "&amp;action=view\">".ww("ViewMyRelationForThisMember")."</a> </li>\n";
	   }
	}

	if (HasRight("Logs")) {
		$MenuAction .= "          <li><a href=\"admin/adminlogs.php?Username=" . $m->Username . "\">see logs</a> </li>\n";
	}
	if (isset($CanBeEdited) && $CanBeEdited) {
		$MenuAction .= "          <li><a href=\"editmyprofile.php?cid=" . $m->id . "\">".ww("TranslateProfileIn",LanguageName($_SESSION["IdLanguage"]))." ".FlagLanguage(-1,$title="Translate this profile")."</a> </li>\n";
	}
	$VolAction=ProfileVolunteerMenu($m); // This will receive the possible vol action for this member

	ShowActions($MenuAction); // Show the Actions
	ShowAds(); // Show the Ads

	// middle column
	echo "    <div id=\"col3\"> \n"; 
	echo "      <div id=\"col3_content\" class=\"clearfix\"> \n"; 
    
  echo "      <div class=\"info\"\n";
    echo "<h1>",ww("AddComments"),"</h1>"; 
	// Display the previous comment if any
	$ttLenght = array ();
	if (isset ($TCom->Quality)) { // if there allready a comment display it
		echo "<h3>",ww("PreviousComments"),"</h3>";    
		echo "<table valign=center style=\"font-size:12;\" class=\"framed highlight\">";
		echo "<tr><th colspan=3>", LinkWithUsername($Username), "</th>";
		$color = "black";
		if ($TCom->Quality == "Good") {
			$color = "#808000";
		}
		if ($TCom->Quality == "Bad") {
			$color = "red";
		}
		echo "<tr><td>";
		echo "<b>", $TCom->Commenter, "</b><br>";
		echo "<i>", $TCom->TextWhere, "</i>";
		echo "<br><font color=$color>", $TCom->TextFree, "</font>";
		echo "</td>";
		$ttLenght = explode(",", $TCom->Lenght);
		echo "<td width=\"30%\">";
		for ($jj = 0; $jj < count($ttLenght); $jj++) {
			if ($ttLenght[$jj]=="") continue; // Skip blank category comment : todo fix find the reason and fix this anomaly
			echo ww("Comment_" . $ttLenght[$jj]), "<br>";
		}

		echo "</td>";
		echo "</table>\n";
		echo "<br />\n";
        }

	// Display the form to propose to add a comment	
//	echo "<br><br><form method=\"post\" name=\"addcomment\" OnSubmit=\"return(VerifSubmit());\">\n"; 
	echo "<form method=\"post\" name=\"addcomment\" OnSubmit=\"return DoVerifSubmit('addcomment');\">\n";
	echo "<table valign=center style=\"font-size:12;\">";
	echo "<tr><td colspan=2><h3>", ww("CommentQuality"),"</h3><br>",ww("RuleForNeverMetComment"),"</td>";

	echo "<tr><td><select name=Quality>\n";
	echo "<option value=\"Neutral\" selected >"; // by default
	echo ww("CommentQuality_Neutral"), "</option>\n";

	echo "<option value=\"Good\"";
	if ($TCom->Quality == "Good")
		echo " selected ";
	echo ">", ww("CommentQuality_Good"), "</option>\n";

	echo "<option value=\"Bad\"";
	if ($TCom->Quality == "Bad")
		echo " selected ";
	echo ">", ww("CommentQuality_Bad"), "</option>\n";
	echo "</selected>";
	echo "</td>";
	echo "<td><p class=\"grey\">", ww("CommentQualityDescription", $Username, $Username, $Username), "</p></td></tr>";

	$tt = $_SYSHCVOL['LenghtComments'];
	$max = count($tt);
	echo "<tr><td colspan=2><h3>", ww("CommentLength"), "</h3></td></tr>";
	echo "<tr><td><table valign=center style=\"font-size:12;\">";
	for ($ii = 0; $ii < $max; $ii++) {
		echo "<tr><td>", ww("Comment_" . $tt[$ii]), "</td>";
		echo "<td><input type=checkbox name=\"Comment_" . $tt[$ii] . "\"";
		if (in_array($tt[$ii], $ttLenght))
			echo " checked ";
		echo ">\n</td>\n";

	}
	echo "</table></td>";

	echo "<td><p class=\"grey\">", ww("CommentLengthDescription", $Username, $Username, $Username), "</p></td></tr>";
	echo "<tr><td colspan=2><h3>", ww("CommentsWhere"), "</h3></td></tr><tr><td><textarea name=TextWhere cols=40 rows=3></textarea></td><td><p class=\"grey\">", ww("CommentsWhereDescription", $Username), "</p></td></tr>";
	echo "<tr><td colspan=2><h3>", ww("CommentsCommenter"), "</h3></td></tr><tr><td><textarea name=Commenter cols=40 rows=8></textarea></td><td style=\"vertical-align=top\"><p class=\"grey\">", ww("CommentsCommenterDescription", $Username), "</p></td></tr>";

	echo "<tr><td align=center colspan=2><input type=hidden value=" . $IdMember . " name=cid>";
	echo "<input type=hidden name=action value=add>";
 	echo "<input type=submit id=submit name=valide value=submit ></td>";

	echo "\n</table>";
	echo "\n</form>\n";

	echo "<SCRIPT  TYPE=\"text/javascript\">\n";
	echo "function DoVerifSubmit(nameform) {\n";
	echo "nevermet=document.forms[nameform].elements['Comment_NeverMetInRealLife'].checked;\n";
echo "	if ((document.forms[nameform].elements['Quality'].value!='Negative') && (nevermet)) {\n";
echo "	   alert('",addslashes(ww("RuleForNeverMetComment")),"');\n";
echo "	   return (false);\n";
echo "	}\n";
echo "	return(true);\n";
	echo "}\n";
	echo "</SCRIPT>\n";


	echo "        </div>\n";

	require_once "footer.php";
}
?>
