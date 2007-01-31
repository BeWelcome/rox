<?php
require_once "lib/FunctionsTools.php";

//------------------------------------------------------------------------------
// Logout function unlog member and fisplay the login page 
Function Logout($nextlink = "") {
	if (isset ($_SESSION['IdMember'])) {

		// todo optimize periodically online table because it will be a gruyere 
		// remove from online list
		$str = "delete from online where IdMember=" . $_SESSION['IdMember'];
		sql_query($str);

		unset ($_SESSION['WhoIsOnlineCount']);
		unset ($_SESSION['IdMember']);
		unset ($_SESSION['Username']);
		LogStr("Loging out", "Login");
	}
	if (isset ($_SESSION['MemberCryptKey']))
		unset ($_SESSION['MemberCryptKey']);
	if (isset ($_SESSION['IdMember']))
		unset ($_SESSION['IdMember']);

	if ($nextlink != "") {
		header("Location: login.php?nextlink=" . $nextlink);
	}
} // end of function Logout

//------------------------------------------------------------------------------
// Login function does the proper verification for Login, 
// update members.LastLogin and link to main page or to other proposed
// page in main link
Function Login($UsernameParam, $passwordParam, $nextlink = "main.php") {
	global $_SYSHCVOL;

	if (CountWhoIsOnLine() > $_SYSHCVOL['WhoIsOnlineLimit']) {
		refuse_login(ww("MaxOnlineNumberExceeded", $_SESSION['WhoIsOnlineCount']), $nextlink);
	}

	$Username = strtolower((ltrim(rtrim($UsernameParam)))); // we are cool and help members with big fingers
	$password = ltrim(rtrim($passwordParam)); // we are cool and help members with big fingers

	// todo : improve this security weakness !
	$_SESSION["key_to_tb"] = $password; // storing the password to acces travelbook

	Logout(""); // if was previously logged then force logout

	// Deal with the username which may have been reused
	$rr = LoadRow("select Username,ChangedId from members where Username='" . $Username . "'");
	$count = 0;
	while ($rr->ChangedId != 0) {
		$rr = LoadRow("select Username,ChangedId from members where id=" . $rr->ChangedId);
		$Username = $rr->Username;
		$count++;
		if ($count > 100) {
			LogStr("Infinite loop in Login with " . $UserName, "Bug");
			break; // 
		}
	}
	// End of dal with the username which may have been reused

	$str = "select * from members where Username='" . $Username . "' and PassWord=PASSWORD('" . $password . "')";
	//	echo "\$str=$str","<br>" ;
	$m = LoadRow($str);
	if (!isset ($m->id)) { // If Username does'nt exist
		LogStr("Failed to connect with Username=[<b>" . $Username . "</b>]", "Login");
		refuse_login("no such username and password", $nextlink);
	}

	// Set the session identifier
	$_SESSION['IdMember'] = $m->id;
	$_SESSION['Username'] = $m->Username;

	if ($_SESSION['IdMember'] != $m->id) { // Check is session work of
		LogStr("Session problem detected in FunctionsLogin.php", "Login");
		refuse_login("Session problem detected in FunctionsLogin.php", $next_login);
	}; // end Check is session work of

	$_SESSION['MemberCryptKey'] = crypt($password, "rt"); // Set the key which will be used for member personal cryptation
	$_SESSION['LogCheck'] = Crc32($_SESSION['MemberCryptKey'] . $_SESSION['IdMember']); // Set the key for checking id and LohCheck (will be restricted in future)

	mysql_query("update members set LastLogin=now() where id=" . $_SESSION['IdMember']); // update the LastLogin date

	// Load language prederence (IdPreference=1)
	$rPrefLanguage = LoadRow("select memberspreferences.Value,ShortCode from memberspreferences,languages where IdMember=" . $_SESSION['IdMember'] . " and IdPreference=1 and memberspreferences.Value=languages.id");
	if (isset ($rPrefLanguage->Value)) { // If there is a member selected preference set it
		$_SESSION["IdLanguage"] = $rPrefLanguage->Value;
		$_SESSION["lang"] = $rPrefLanguage->ShortCode;
	}

	// Process the login of the member according to his status
	switch ($m->Status) {
		case "Active" :
			LogStr("Successful login with <b>" . $_SERVER['HTTP_USER_AGENT'] . "</b>", "Login");
			if (HasRight("Words"))
				$_SESSION['switchtrans'] = "on"; // Activate switchtrans oprion if its a translator
				// register in TB
//				$OnePad=mt_rand();
//				$_SESSION['op']=$OnePad;
//				require("http://ecommunity.ifi.unizh.ch/newlayout/htdocs/ExAuth.php?k=fh457Hg36!pg29G&u=".$_SESSION['Username']."&e=".GetEmail($_SESSION['IdMember'])."&OnePad=$OnePad&p=$password");

			break;

		case "ToComplete" :
			LogStr("Login with (needmore)<b>" . $_SERVER['HTTP_USER_AGENT'] . "</b>", "Login");
			header("Location: completeprofile.php");
			exit (0);

		case "Banned" :
			LogStr("Banned member tried to log<b>" . $_SERVER['HTTP_USER_AGENT'] . "</b>", "Login");
			refuse_Login("You are not allowed to log anymore", "index.php");
			exit (0);

		case "TakenOut" :
			LogStr("Takenout member want to Login<b>" . $_SERVER['HTTP_USER_AGENT'] . "</b>", "Login");
			refuse_Login("You have been taken out at your demand, you will certainly be please to see you back, please contact us to re-active your profile", "index.php");
			exit (0);

		case "CompletedPending" :
		case "Pending" :
			$str = "You must wait a bit, your appliance hasn't yet be reviewed by our volunteers <b>" . $_SERVER['HTTP_USER_AGENT'] . "</b>";
			LogStr($str, "Login");
			refuse_Login($str, "index.php");
			break;

		case "NeedMore" :
			header("Location: updatemandatory.php");
			exit (0);
			break;

		default :
			LogStr("Unprocessed status=[<b>" . $m->Status . "</b>] in FunctionsLogin.php with <b>" . $_SERVER['HTTP_USER_AGENT'] . "</b>", "Login");
			refuse_Login("You can't log because your status is set to " . $m->Status . "<br>", $nextlink);
			break;
	}

	if ($nextlink != "") {
		header("Location: " . $nextlink); // go to next page
		exit (0);
	}

}

//------------------------------------------------------------------------------
// function refuse login is called when log fail and display a proper message
function refuse_login($message, $nextlink) {
	$title = ww('login');

	include "layout/header_micha.php";

	Menu1("error.php", ww('MainPage')); // Displays the top menu

	Menu2($_SERVER["PHP_SELF"]);

	echo "\n<div id=\"maincontent\">\n";
	echo "  <div id=\"topcontent\">";
	echo "					<h3>Login error</h3>\n";
	echo "\n  </div>\n";
	echo "</div>\n";

	echo "\n  <div id=\"columns\">\n";
	echo "		<div id=\"columns-low\">\n";

	ShowActions(); // Show the Actions
	ShowAds(); // Show the Ads

	echo "		<div id=\"columns-middle\">\n";
	echo "			<div id=\"content\">\n";
	echo "				<div class=\"info\">\n";

	echo "<center><br><br>\n";
	echo "<p style=\"color:red;font-size:22px\">", $message, "</p>\n";

	echo "<br><br><a href=\"" . $nextlink . "\" style=\"font-size:22px;\">", ww("GoBack"), "</a><br><br><br>\n";
	echo "</center>\n";

	echo "\n         </div>\n"; // Class info 
	echo "       </div>\n"; // content
	echo "     </div>\n"; // columns-midle

	echo "   </div>\n"; // columns-low
	echo " </div>\n"; // columns

	include ("layout/footer.php");

	exit (0);
} // end of refuse_login($message,$nextlink)
?>
