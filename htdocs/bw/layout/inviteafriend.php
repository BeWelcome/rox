<?php
require_once ("menus.php");
function DisplayForm($m,$JoinMemberPict="") {
	global $title;
	$title = ww('InviteAFriendPage');
	require_once "header.php";

	Menu1("", ww('MainPage')); // Displays the top menu

	Menu2("inviteafriend.php", ww('MainPage')); // Displays the second menu

	DisplayHeaderShortUserContent("inviteafriend.php","",""); // Display the header

	echo "<div class=\"info\">\n";

	// TODO: check the meaning of the next row. $TData is not defined
	$iiMax = count($TData);
	$CurrentCategory="";
	echo "<form method=\"post\" action=\"inviteafriend.php\">\n";
	echo "<input type=\"hidden\" name=\"action\" value=\"Send\">\n";
	echo "<p>",ww("InviteAFriendRule",$m->FullName),"</p>\n";
	echo "<p>",ww("EmailOfYourFriend")," <input type=\"text\" name=\"Email\" value=\"",GetStrParam("Email"),"\"></p>\n";
	if (IsPublic($_SESSION["IdMember"])) {
		 echo "<p>","<textarea name=\"Message\" rows=\"20\" cols=\"80\">",str_replace("<br />","\n",ww("InviteAFriendStandardText","<a href=\"http://www.bewelcome.org/member.php?cid=".$_SESSION["Username"]."\">".$_SESSION["Username"]."</a>")),"</textarea></p>\n";
	}
	else {
		 echo "<p>","<textarea name=\"Message\" rows=\"20\" cols=\"80\">",str_replace("<br />","\n",ww("InviteAFriendTextPrivateProf","<a href=\"http://www.bewelcome.org/member.php?cid=".$_SESSION["Username"]."\">".$_SESSION["Username"]."</a>")),"</textarea></p>\n";
	}
	echo "<p><input type=\"checkbox\" name=\"JoinMemberPict\" ";
	if ($JoinMemberPict=="on") echo "checked";
	echo "> ",ww("JoinMyPicture")," </p>\n";
	echo "<p align=\"center\"><input type=\"submit\"></p>\n";
	echo "</div>\n";

	require_once "footer.php";

} // DisplayForm


function DisplayResults($m,$Message) {
	global $title;
	$title = ww('InviteAFriendPage');
	require_once "header.php";

	Menu1("inviteafriend.php", ww('MainPage')); // Displays the top menu

	Menu2($_SERVER["PHP_SELF"]);

	DisplayHeaderWithColumns(ww("InviteAFriendPage")); // Display the header
	
   echo $Message;
	require_once "footer.php";
} // end of DisplayResults


?>
