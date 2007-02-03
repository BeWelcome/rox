<?php
require_once ("Menus.php");
// $iMes contain eventually the previous messaeg number
function DisplayContactMember($m, $Message = "", $iMes = 0, $Warning = "",$JoinMemberPict="") {
	global $title;
	$title = ww('ContactMemberPageFor', $m->Username);
	include "header.php";

	Menu1(); // Displays the top menu

	Menu2("member.php");
	// Header of the profile page
	require_once ("profilepage_header.php");

	echo "	<div id=\"columns\">";
	menumember("contactmember.php?cid=" . $m->id, $m->id, $m->NbComment);
	echo "		<div id=\"columns-low\">";

	ShowActions("<li><a href=\"todo.php\">Add to my list</a></li>\n<li><a href=\"todo.php\">View forum posts</a></li>\n"); // Show the Actions
	ShowAds(); // Show the Ads

	echo "			<div class=\"clear\" />";

	echo "     <div id=\"columns-middle\">\n";
	if ($Warning != "") {
		echo "<br><br><table width=50%><tr><td><h4><font color=red>";
		echo $Warning;
		echo "</font></h4></td></table>\n";
	}

	echo "<form method=post>";
	echo "<input type=hidden name=action value=sendmessage>";
	echo "<input type=hidden name=cid value=\"" . $m->id . "\">\n";
	echo "<input type=hidden name=iMes value=\"" . $iMes . "\">\n";
	echo "<table>\n";
	echo "<tr><td colspan=3 width=70%>", ww("YourMessageFor", LinkWithUsername($m->Username)), "<br><textarea name=Message rows=15 cols=80>", $Message, "</textarea>";
	echo "<tr><td colspan=3>", ww("IamAwareOfSpamCheckingRules"), "</td><td colspan>", ww("IAgree"), " <input type=checkbox name=IamAwareOfSpamCheckingRules></td>";
	echo "<tr>" ;
	echo "<td>" ;
	echo ww("JoinMyPicture")," <input type=checkbox name=JoinMemberPict " ;
	if ($JoinMemberPict=="on") echo "checked" ;
	echo ">" ;
	echo "</td>" ;
	echo "<td align=center colspan=2><input type=submit name=submit value=submit> <input type=submit name=action value=\"", ww("SaveAsDraft"), "\"></td>";
	echo "</table>\n";
	echo "</form>";
	echo "     </div>\n";

	include "footer.php";

}

function DisplayResult($m, $Message = "", $Result = "") {
	global $title;
	$title = ww('ContactMemberPageFor', $m->Username);
	include "header_micha.php";

	Menu1(); // Displays the top menu

	Menu2("member.php");
	// Header of the profile page
	require_once ("profilepage_header.php");

	echo "	<div id=\"columns\">";
	menumember("contactmember.php?cid=" . $m->id, $m->id, $m->NbComment);
	echo "		<div id=\"columns-low\">";

	ShowActions("<li><a href=\"todo.php\">Add to my list</a></li>\n<li><a href=\"todo.php\">View forum posts</a></li>\n"); // Show the Actions
	ShowAds(); // Show the Ads

	echo "			<div class=\"clear\" />";
	echo "<center>";
	echo "<H1>Contact ", $m->Username, "</H1>\n";

	echo "<br><br><table width=50%><tr><td><h4>";
	echo $Result;
	echo "</h4></td></table>\n";

	include "footer.php";

} // end of display result
?>
