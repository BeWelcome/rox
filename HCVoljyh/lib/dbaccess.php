<?php
session_cache_expire(30); // session will expire after 30 minutes
session_start();

if (!isset ($_GET['showtransarray'])) {
	$_SESSION['TranslationArray'] = array (); // initialize $_SESSION['TranslationArray'] if not currently switching to adminwords
}
if (!isset ($_SERVER['SERVER_NAME']) or ($_SERVER['SERVER_NAME'] == 'ns20516.ovh.net')) {
	$mysqlusername = "hcvoltestdbusr";
	$dbname = "hcvoltest";
	$password = "aJ1Feklef342";
	$db = mysql_connect("localhost", $mysqlusername, $password) or die("localhost bad connection with dbname=" . $dbname . " and mysqlusername=" . $mysqlusername . " " . mysql_error()); // remote on old server
}
elseif ($_SERVER['SERVER_NAME'] == 'www.bewelcome.org') {
	$mysqlusername = "hcvoltestdbusr";
	$dbname = "hcvoltest";
	$password = "aJ1Feklef342";
	$db = mysql_connect("localhost", $mysqlusername, $password) or die("localhost bad connection with dbname=" . $dbname . " and mysqlusername=" . $mysqlusername . " " . mysql_error()); // remote on old server
}
elseif ($_SERVER['SERVER_NAME'] == 'www.hcvolunteers.org') {
	$mysqlusername = "hcvoltestdbusr";
	$dbname = "hcvoltest";
	$password = "aJ1Feklef342";
	$db = mysql_connect("localhost", $mysqlusername, $password) or die("localhost bad connection with dbname=" . $dbname . " and mysqlusername=" . $mysqlusername . " " . mysql_error()); // remote on old server
}
elseif ($_SERVER['SERVER_NAME'] == 'localhost') {
	$mysqlusername = "remoteuser";
	$dbname = "hcvoltest";
	$password = "e3bySxW32WcmXamn";
	$db = mysql_connect("localhost", $mysqlusername, $password) or die("localhost bad connection with dbname=" . $dbname . " and mysqlusername=" . $mysqlusername . " " . mysql_error()); // remote on old server
} else {

	echo "\$_SERVER['SERVER_NAME']=", $_SERVER['SERVER_NAME'];
	die(" this server was not expected");

	// hcvoltestdbusr aJ1Feklef342
	$mysqlusername = "remoteuser";
	$username = $dbname = "hcvoltest";
	$password = "e3bySxW32WcmXamn";
	$db = mysql_connect("localhome", $mysqlusername, $password) or die("bad connection " . mysql_error()); // remote on old server
}

if (!$db) {
	$str = "bad mysql_connect " . mysql_error();
	error_log($str . " mysqlusername=$mysqlusername");
	die($str);
}

if (!mysql_select_db($dbname, $db)) {
	$str = "bad mysql_connect " . mysql_error();
	error_log($str . " select db $dbname");
	die($str);
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
			$errortolog = "Using same computer " . $than; // The error will be log by LogStr when database will be opened
		} // end of test if it match
	}
	setcookie("MyBWId", $_SESSION['IdMember'], time() + 31974000, "/"); // Record the member id in the cookie
	setcookie("MyBWusername", $_SESSION['Username'], time() + 31974000, "/"); // record the usename in the cookie
}
// end Previous identity cookie checking	

require_once ("HCVol_Config.php");
require_once ("FunctionsTools.php");
EvaluateMyEvents(); // evaluate the events (messages received, keep uptodate whoisonline ...)
?>
