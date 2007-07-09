<?php
require_once ("menus.php");

function DisplayAdminMassMails($TData) {
	global $title;
	$title = "Admin Mass Mails";
	require_once "header.php";

	Menu1("", ww('MainPage')); // Displays the top menu

	Menu2("admin/adminmassmails.php", ww('MainPage')); // Displays the second menu

	DisplayHeaderShortUserContent($title);// Display the header

	$max = count($TData);
	$max=0 ;
	echo "<table><tr><td align=right>Please write here in </td><td bgcolor=yellow align=left>".LanguageName($_SESSION['IdLanguage'])."</td></table>";
	echo "<br>" ;
//	echo "<hr>\n";
	echo "<table>\n";
	echo "<form method=post action=adminmassmails.php>\n";
	echo "<input type=hidden name=IdBroadCast value=",$TData->IdBroadcast,">\n" ;
	echo "<tr><td>subject</td><td> <textarea name=subject  rows=1 cols=80>", GetParam(subject), "</textarea></td>";
	echo "<tr><td>body</td><td> <textarea name=body rows=10 cols=80>", GetParam(body), "</textarea></td>";
	echo "<tr><td>greetings</td><td> <textarea name=greetings rows=2 cols=80>", GetParam(greetings), "</textarea></td>";
	echo "\n<tr><td colspan=2 align=center>";
	echo "<input type=submit name=action value=find>";
	if (empty($TData->IdBroadcast)) echo " <input type=submit name=action value=update>";
	else echo " <input type=submit name=action value=update>";
	echo "</td><td align=center>" ;
   if (HasRight('MassMail','Send')) {
	   echo "Send <input type=checkbox name=send> ";
	   echo " <input type=submit name=action value=send>";
	}
	echo "</td> ";
	echo "</form>\n";
	echo "</table>\n";

	require_once "footer.php";

}


// This function propose to create a broadcast
function DisplayFormCreateBroadcast($IdBroadCast=0, $Name = "",$BroadCast_Title_,$BroadCast_Body_,$Description, $Type = "") {
	global $title;
	$title = "Create a new broadcast";
	require_once "header.php";

	Menu1("", ww('MainPage')); // Displays the top menu

	Menu2("admin/adminmassmails.php", ww('MainPage')); // Displays the second menu
	
	DisplayHeaderShortUserContent($title);

	echo "<br><center>";
	echo "\n<form method=post action=adminmassmails.php>";
	echo "\n<input type=hidden name=IdBroadCast value=$IdBroadCast>";
	echo "<table><tr><td >Please write here in <b>".LanguageName($_SESSION['IdLanguage'])."</b></td></tr></table><br>";
	echo "<table>";
	echo "<tr><td width=30%>Give the code name of the broadcast as a word entry (must not exist in words table previously) like<br> <b>NewsJuly2007</b> or <b>NewsAugust2007</b> without spaces !<br>";
	echo "</td>";
	echo "<td>";
	echo "<input type=text ";
	if ($Name != "")
		echo "readonly"; // don't change a group name because it is connected to words
	echo " name=Name value=\"$Name\">";
	echo "</td>";

	echo "<tr><td width=30%>Title for the massmail</td>";
	echo "<td align=left><textarea name=BroadCast_Title_ cols=80 rows=1>",$BroadCast_Title_,"</textarea></td>" ;
	echo "<tr><td>text of the mass mail (first %s, if any, will be replaced by the username at sending)</td>";
	echo "<td align=left><textarea name=BroadCast_Body_ cols=80 rows=15>",$BroadCast_Body_,"</textarea></td>" ;
	echo "<tr><td>Description (as translators will see it in words) </td>";
	echo "<td align=left><textarea name=Description cols=60 rows=5>",$Description,"</textarea></td>" ;

	echo "\n<tr><td colspan=2 align=center>";

	if ($IdBroadCast != 0)
		echo "<input type=submit name=submit value=\"update massmail\">";
	else
		echo "<input type=submit name=submit value=\"create massmail\">";

	echo "<input type=hidden name=action value=createbroadcast>";
	echo "</td>\n</table>\n";
	echo "</form>\n";
	echo "</center>";

	require_once "footer.php";
} // DisplayFormCreateBroadcast

function DisplayResults($TData,$Message) {
	global $title;
	$title = "Admin Mass Mails";
	require_once "header.php";

	Menu1("", ww('MainPage')); // Displays the top menu

	Menu2("admin/adminmassmails.php", ww('MainPage')); // Displays the second menu

	DisplayHeaderWithColumns($Message); // Display the header

	require_once "footer.php";

}
?>
