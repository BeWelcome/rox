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
require_once(dirname(__FILE__)."/tbinit.php");
require_once(dirname(__FILE__)."/../../../build/user/lib/user.lib.php");
require_once "FunctionsTools.php";
error_reporting(E_ALL& ~E_NOTICE);


/**
 * Logout member and display start page
 * Must be called before any HTML is written.
 */
function Logout()
{
	APP_User::get()->logout();
	header("Location: " . PVars::getObj('env')->baseuri);
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
