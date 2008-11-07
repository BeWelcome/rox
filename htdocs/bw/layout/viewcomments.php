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

function DisplayComments($m, $TCom) {
	global $title;
	$title = ww('ViewComments');
	require_once "header.php";

	Menu1(); // Displays the top menu
	Menu2("member.php?cid=".$m->Username); // even if in viewcomment we can be in the myprofile menu

	// Header of the profile page
	DisplayProfilePageHeader( $m );

	menumember("viewcomments.php?cid=" . $m->id, $m);
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

	$iiMax = count($TCom);
	$tt = array ();
	$info_styles = array(0 => "        <div class=\"info clearfix\">\n", 1 => "        <div class=\"info highlight clearfix\">\n");
	for ($ii = 0; $ii < $iiMax; $ii++) {
		$color = "black";
		if ($TCom[$ii]->Quality == "Good") {
			$color = "#4e9a06";
		}
		if ($TCom[$ii]->Quality == "Bad") {
			$color = "#cc0000";
		}
		echo $info_styles[($ii%2)];
		echo "            <div class=\"subcolumns\">\n";
		echo "              <div class=\"c75l\">\n";
		echo "                <div class=\"subcl\">\n";
		$picturelink = LinkWithPicture($TCom[$ii]->Commenter,$TCom[$ii]->photo);
		echo str_replace("\"framed\"","\"float_left framed\"",$picturelink);
    echo "                  <p><strong>", ww("CommentFrom", LinkWithUsername($TCom[$ii]->Commenter)), "</strong></p>\n";
 		echo "                  <p><em>", $TCom[$ii]->TextWhere, "</em></p>";
		echo "                  <p><font color=$color>", $TCom[$ii]->TextFree, "</font></p>\n";
		$tt = explode(",", $TCom[$ii]->Lenght);
		echo "                </div>\n"; // end subcl
		echo "              </div>\n"; // end c75l
		echo "              <div class=\"c25r\">\n";
		echo "                <div class=\"subcr\">\n";		
		echo "                  <ul class=\"linklist\">\n";
		echo "                    <li>", LinkWithUsername($m->Username), "</li>\n";
		for ($jj = 0; $jj < count($tt); $jj++) {
			if ($tt[$jj]=="") continue; // Skip blank category comment : todo fix find the reason and fix this anomaly
			echo "                    <li>", ww("Comment_" . $tt[$jj]), "</li>\n";
		}
    echo "                  </ul>\n";
    echo "                  <ul class=\"linklist\">\n";
		if (HasRight("Comments"))
			echo "                      <li><a href=\"admin/admincomments.php?action=editonecomment&IdComment=", $TCom[$ii]->id, "\">edit</a></li>\n";
		if (isset($_SESSION["IdMember"]) && $m->id==$_SESSION["IdMember"]) echo "<li><a href=\"feedback.php?IdCategory=4\">",ww("ReportCommentProblem"),"</a></li>\n"; // propose owner of comment to report about the comment
		echo "                  </ul>\n";
    echo "                </div>\n"; // end subcr
    echo "              </div>\n"; // end c25r
    echo "            </div>\n"; // end subcolumns
    echo "        </div>\n"; // end info
	}
	
		require_once "footer.php";
}
?>