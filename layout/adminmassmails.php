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
	echo "<table><tr><td>Please write here in</td><td bgcolor=yellow>".LanguageName($_SESSION['IdLanguage'])."</td></table>";
	echo "<br>" ;
//	echo "<hr>\n";
	echo "<table>\n";
	echo "<form method=post action=adminmassmails.php>\n";
	echo "<input type=hidden name=IdBroadcast value=",$TData->IdBroadcast,">\n" ;
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
