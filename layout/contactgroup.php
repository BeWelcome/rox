<?php
require_once ("Menus.php");
// $iMes contain eventually the previous messaeg number
function DisplayContactGroup($IdGroup,$Title="", $Message = "", $Warning = "",$JoinMemberPict="") {
	global $title;
	$title = ww('ContactGroupPage');
	include "header.php";

	include "header.php";

	Menu1("", ww('MainPage')); // Displays the top menu

	Menu2("contactgroup.php", ww('MainPage')); // Displays the second menu

	DisplayHeaderShortUserContent($title);


	echo "     <div id=\"columns-middle\">\n";
	if ($Warning != "") {
		echo "<br><br><table width=50%><tr><td><h4><font color=red>";
		echo $Warning;
		echo "</font></h4></td></table>\n";
	}

	echo "<form method=post>";
	echo "<input type=hidden name=action value=sendmessage>";
	echo "<input type=hidden name=IdGroup value=$IdGroup>";
	echo "<table width=70%>\n";
	echo "<tr><td colspan=3 align=center>", ww("YourMessageForGroup", LinkWithGroup($IdGroup)), "<br>" ;
	echo "<textarea name=Title rows=1 cols=80>", $Title, "</textarea><br>";
	echo "<textarea name=Message rows=15 cols=80>", $Message, "</textarea></td>";
	echo "<tr><td colspan=2>", ww("IamAwareOfSpamCheckingRules"), "</td><td width=20%>", ww("IAgree"), " <input type=checkbox name=IamAwareOfSpamCheckingRules><br>" ;
	echo ww("JoinMyPicture")," <input type=checkbox name=JoinMemberPict " ;
	if ($JoinMemberPict=="on") echo "checked" ;
	echo ">" ;
	echo "</td>" ;
	echo "<tr>" ;
	echo "<td align=center colspan=3 align=center><input type=submit name=submit value=submit></td>";
	echo "</table>\n";
	echo "</form>";
	echo "     </div>\n";

	include "footer.php";

}

function DisplayResult($Group,$Title,$Message, $Result = "") {
	global $title;
	$title = ww('ContactGroupPage', $m->Username);
	include "header.php";

	Menu1("", ww('MainPage')); // Displays the top menu

	Menu2("contactgroup.php", ww('MainPage')); // Displays the second menu

	DisplayHeaderShortUserContent($title);

	echo "<center>";
	echo "<H1>Contact ", LinkWithGroup($Group), "</H1>\n";

	echo "<br><br><table width=50%>" ;
	echo "<tr><td><i>",$Title,"</i></td>" ;
	echo "<tr><td>",$Message,"</td>" ;
	echo "<tr><td><h4>";
	echo $Result;
	echo "</h4></td></table>\n";

	include "footer.php";

} // end of display result
?>
