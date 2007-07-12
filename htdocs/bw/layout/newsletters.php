<?php
require_once ("menus.php");

// This will be to edit to add various news letters along the time
function DisplayNews() {
	$title = ww('NewsLetters');
	require_once "header.php";

	Menu1("", ww('MainPage')); // Displays the top menu

	Menu2("newsletters.php", ww("NewsLetters")); // Displays the second menu

	DisplayHeaderShortUserContent("newsletters.php","",""); // Display the header

	if (IsLoggedIn()) {
		 $_SESSION["Username"] ;
	}
	else { 
			 $Username=" not logged" ;
	}
	echo "<div class=\"info\">\n";
	
	echo ww("BroadCast_Title_NewsJuly2007",$Username),"<br><br>\n"  ;
	echo ww("BroadCast_Body_NewsJuly2007",$Username),"<br>\n"  ;

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
