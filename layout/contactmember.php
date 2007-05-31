<?php
require_once ("menus.php");
require_once ("profilepage_header.php");

// $iMes contain eventually the previous messaeg number
function DisplayContactMember($m, $Message = "", $iMes = 0, $Warning = "",$JoinMemberPict="") {
	global $title;
	$title = ww('ContactMemberPageFor', $m->Username);
	require_once "header.php";

	Menu1(); // Displays the top menu

	Menu2("member.php");
	// Header of the profile page
	DisplayProfilePageHeader( $m );

	menumember("contactmember.php?cid=" . $m->id, $m);

	$MenuAction = "";
//	$MenuAction .= "               <li><a href=\"contactmember.php?cid=" . $m->id . "\">" . ww("ContactMember") . "</a></li>\n";
	$MenuAction .= "               <li><a href=\"addcomments.php?cid=" . $m->id . "\">" . ww("addcomments") . "</a></li>\n";
	$MenuAction .= "               <li><a href=\"todo.php\">".ww("ViewForumPosts")."</a></li>\n";
	if (GetPreference("PreferenceAdvanced")=="Yes") {
      if ($m->IdContact==0) {
	   	  $MenuAction .= "<li><a href=\"mycontacts.php?IdContact=" . $m->id . "&action=add\">".ww("AddToMyNotes")."</a> </li>\n";
	   }
	   else {
	   	  $MenuAction .= "<li><a href=\"mycontacts.php?IdContact=" . $m->id . "&action=view\">".ww("ViewMyNotesForThisMember")."</a> </li>\n";
	   }
	}
	ShowActions($MenuAction); // Show the Actions
	ShowAds(); // Show the Ads

	// col3 (middle column)
	echo "      <div id=\"col3\"> \n";
	echo "      <div id=\"col3_content\"> \n"; 
  echo "					<div class=\"info\">";

	if ($Warning != "") {
		echo "<br><table width=50%><tr><td><h4><font color=red>";
		echo $Warning;
		echo "</font></h4></td></table>\n";
	}

	echo "<form method=post action=contactmember.php>\n";
	echo "<input type=hidden name=action value=sendmessage>\n";
	echo "<input type=hidden name=cid value=\"" . $m->id . "\">\n";
	echo "<input type=hidden name=iMes value=\"" . $iMes . "\">\n";
	echo "<table>\n";
	echo "<tr><td colspan=3 align=center>", ww("YourMessageFor", LinkWithUsername($m->Username)), "<br><textarea name=Message rows=15 cols=80>", $Message, "</textarea></td>";
	echo "<tr><td colspan=3>", ww("IamAwareOfSpamCheckingRules"), "</td>\n";
	echo "\n<tr>";
	echo "<td align=center colspan=3>";
	echo ww("IAgree"), " <input type=checkbox name=IamAwareOfSpamCheckingRules>";
	echo "&nbsp;&nbsp;&nbsp;";
	echo ww("JoinMyPicture")," <input type=checkbox name=JoinMemberPict ";
	if ($JoinMemberPict=="on") echo "checked";
	echo "></td>\n";
	echo "<tr><td align=center colspan=3><input type=submit name=submit value=submit>";
	if (GetPreference("PreferenceAdvanced")=="Yes") echo " <input type=submit name=action value=\"", ww("SaveAsDraft"), "\">";
	echo "</td>";
	echo "</table>\n";
	echo "</form>";
  echo "    </div>\n";
  echo "    </div>\n"; 

	require_once "footer.php";

}

function DisplayResult($m, $Message = "", $Result = "") {
	global $title;
	$title = ww('ContactMemberPageFor', $m->Username);
	require_once "header.php";

	Menu1(); // Displays the top menu

	Menu2("member.php");
	// Header of the profile page
	DisplayProfilePageHeader( $m );

	echo "	<div id=\"columns\">";
	menumember("contactmember.php?cid=" . $m->id, $m);
	echo "		<div id=\"columns-low\">";

	ShowActions("<li><a href=\"todo.php\">Add to my list</a></li>\n<li><a href=\"todo.php\">View forum posts</a></li>\n"); // Show the Actions
	ShowAds(); // Show the Ads

	echo "<center>";
	echo "<H1>Contact ", $m->Username, "</H1>\n";

	echo "<table width=50%><tr><td><h4>";
	echo $Result;
	echo "</h4></td></table>\n";
	echo "</center>";

	require_once "footer.php";

} // end of display result
?>
