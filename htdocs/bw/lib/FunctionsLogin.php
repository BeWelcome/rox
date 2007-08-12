<?php
require_once(dirname(__FILE__)."/tbinit.php");
require_once(dirname(__FILE__)."/../../../build/user/lib/user.lib.php");
require_once "FunctionsTools.php";
error_reporting(E_ALL& ~E_NOTICE);

//------------------------------------------------------------------------------
// Logout function unlog member and fisplay the login page 
function Logout( $forward )
{
	APP_User::get()->logout();
	if (!empty($forward))
		header("Location: $forward");
}

//------------------------------------------------------------------------------
// Login function does the proper verification for Login, 
// page in main link
function Login( $username, $password, $forward )
{
	APP_User::get()->login( $username, $password );

	if (APP_User::loggedIn())
		if (!empty($forward))
			header("Location: $forward");
}


// TODO: Fix this and move the layout to other files
//------------------------------------------------------------------------------
// function refuse login is called when log fail and display a proper message
function refuse_login($message, $nextlink,$Status) {
	$title = ww('login');

	include "layout/header.php";
	$title = ww('LoginError');

	Menu1("error.php", ww('MainPage')); // Displays the top menu
	Menu2($_SERVER["PHP_SELF"]);

	DisplayHeaderShortUserContent(ww("LoginError")); // Display the header


	echo "          <div class=\"info\" style=\"text-align: center\">\n";
	echo "            <p style=\"color:red;font-size:22px\">", $message, "</p>\n";

	echo "            <p><a href=\"" . $nextlink . "\" style=\"font-size:22px;\">", ww("GoBack"), "</a></p>\n";
	echo "            <br />\n";
	echo "            <p>",ww("IndexPageWord18"); // This is a forgot yout pssword link
	if ($Status=="MailToConfirm") {
	   echo "</p>\n",ww("ProposeSendAgainMailToConfirm") ;
	}
	echo "          </div>\n";

	include ("layout/footer.php");

	exit (0);
} // end of refuse_login($message,$nextlink)
?>
