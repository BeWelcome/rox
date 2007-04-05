<?php

function SetupSession()
{
	global $_SYSHCVOL;
 	global $MayBeDuplicate; // This string will be filled with someting in case a duplicate cookie is found
	$MayBeDuplicate="";
	
	if (empty($_SYSHCVOL['SessionDirectory']))
		bw_error("Setup SessionDirectory in config.php");

	session_save_path($_SYSHCVOL['SessionDirectory']) ; // creating a dedicated session directory

	session_cache_expire(30); // session will expire after 30 minutes
	session_start();
	
	if (!isset ($_GET['showtransarray'])) {
		$_SESSION['TranslationArray'] = array (); // initialize $_SESSION['TranslationArray'] if not currently switching to adminwords
	}
	
	// Previous identity cookie checking	
	if (!empty($_SESSION['IdMember'])) { // if the session Id is set
		if (!empty($_COOKIE['MyBWId'])) { // If there is already a cookie ide set, we are going to check if it match the data of the connected member 
			if ($_COOKIE['MyBWId'] != $_SESSION['IdMember']) { // Test if it match
				if (!isset ($_COOKIE['MyBWusername'])) {
					$than = "than user id=<b>" . $_COOKIE['MyBWId'] . "</b>";
				} else {
					$than = "than username:<b>" . $_COOKIE['MyBWusername'] . "</b>";
				}
				$MayBeDuplicate="Using same computer " . $than. "Duplicate ?"; // The error will be log by LogStr
			} // end of test if it match
		}
		setcookie("MyBWId", $_SESSION['IdMember'], time() + 31974000, "/", ".bewelcome.org"); // Record the member id in the cookie
		setcookie("MyBWusername", $_SESSION['Username'], time() + 31974000, "/", ".bewelcome.org"); // record the usename in the cookie
	}
	// end Previous identity cookie checking	
}

?>