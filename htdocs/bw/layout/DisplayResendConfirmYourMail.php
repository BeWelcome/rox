<?php
require_once ("menus.php");

function DisplayResendConfirmYourMail($IdMember,$Email) {
	global $title;
	require_once "header.php";

	Menu1("", ww('MainPage')); // Displays the top menu

	Menu2("resendconfirmyourmail.php",""); // Displays the second menu

	DisplayHeaderWithColumns(); // Display the header
	
	if ($IdMember==$_SESSION["IdMember"]) {
	   echo ww("ResendConfirmYourMailDone") ;
	}
	else {
	   echo  "<br>request for confirmation sent again to <b>",$Email,"</b>" ;
	}



	require_once "footer.php";
}
?>
