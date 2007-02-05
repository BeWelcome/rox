<?php

function SetupSession()
{
	session_cache_expire(30); // session will expire after 30 minutes
	session_start();
	
	if (!isset ($_GET['showtransarray'])) {
		$_SESSION['TranslationArray'] = array (); // initialize $_SESSION['TranslationArray'] if not currently switching to adminwords
	}
	
	// Previous identity cookie checking	
	if (isset ($_SESSION['IdMember']) and ($_SESSION['IdMember'] != 0)) { // if the session Id is set
		if (isset ($_COOKIE['MyBWId']) and ($_COOKIE['MyBWId'] != 0)) { // If there is already a cookie ide set, we are going to check if it match the data of the connected member 
			if ($_COOKIE['MyBWId'] != $_SESSION['IdMember']) { // Test if it match
				if (!isset ($_COOKIE['MyBWusername'])) {
					$than = "than user id=<b>" . $_COOKIE['MyBWId'] . "</b>";
				} else {
					$than = "than username:<b>" . $_COOKIE['MyBWusername'] . "</b>";
				}
//				LogStr("Using same computer " . $than, "Duplicate ?"); // The error will be log by LogStr
			} // end of test if it match
		}
		setcookie("MyBWId", $_SESSION['IdMember'], time() + 31974000, "/", ".bewelcome.org"); // Record the member id in the cookie
		setcookie("MyBWusername", $_SESSION['Username'], time() + 31974000, "/", ".bewelcome.org"); // record the usename in the cookie
	}
	// end Previous identity cookie checking	
	$_SESSION['testvar'] = 'testtest';
}

?>