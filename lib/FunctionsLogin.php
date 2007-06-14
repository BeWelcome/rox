<?php
require_once "FunctionsTools.php";
error_reporting(E_ALL& ~E_NOTICE);

function DeleteLoginInSession()
{
	if (isset ($_SESSION['IdMember'])) {

		// todo optimize periodically online table because it will be a gruyere 
		// remove from online list
		$str = "delete from online where IdMember=" . $_SESSION['IdMember'];
		sql_query($str);

		LogStr("Logging out", "Login");
		unset ($_SESSION['WhoIsOnlineCount']);
		unset ($_SESSION['IdMember']);
		unset ($_SESSION['IsVol']);
		unset ($_SESSION['Username']);
		unset ($_SESSION['Status']);
	}
	if (isset ($_SESSION['MemberCryptKey']))
		unset ($_SESSION['MemberCryptKey']);
}

//------------------------------------------------------------------------------
// Logout function unlog member and fisplay the login page 
function Logout($nextlink = "") {
	
	DeleteLoginInSession();
//	session_destroy();
	if ($nextlink != "") {
		header("Location: ".bwlink("index.php?nextlink=".urlencode($nextlink)));
	}
} // end of function Logout

//------------------------------------------------------------------------------
// Login function does the proper verification for Login, 
// update members.LastLogin and link to main page or to other proposed
// page in main link
function Login($UsernameParam, $passwordParam, $nextlink = "main.php") {
	global $_SYSHCVOL;
	
	if (CountWhoIsOnLine() > $_SYSHCVOL['WhoIsOnlineLimit']) {
		refuse_login(ww("MaxOnlineNumberExceeded", $_SESSION['WhoIsOnlineCount']), $nextlink,"");
	}

	$Username = strtolower(trim($UsernameParam)); // we are cool and help members with big fingers
	$password = trim($passwordParam); // we are cool and help members with big fingers

	DeleteLoginInSession();

	// todo : improve this security weakness ! NOT NEEDED and commented by MARCO
	// $_SESSION["key_to_tb"] = $password; // storing the password to acces travelbook


	// Deal with the username which may have been reused
	$rr = LoadRow("select Username,ChangedId from members where Username='" . $Username . "'");
	$count = 0;
	while ($rr->ChangedId != 0) {
		$rr = LoadRow("select Username,ChangedId from members where id=" . $rr->ChangedId);
		$Username = $rr->Username;
		$count++;
		if ($count > 100) {
			LogStr("Infinite loop in Login with " . $Username, "Bug");
			break; // 
		}
	}
	// End of while with the username which may have been reused

	$str = "select * from members where Username='" . $Username . "' and PassWord=PASSWORD('" . $password . "')";
	//	echo "\$str=$str","<br>";
	$m = LoadRow($str);
	if (!isset ($m->id)) { // If Username does'nt exist
		LogStr("Failed to connect with Username=[<b>" . $Username . "</b>]", "Login");
		refuse_login("no such username and password", $nextlink,"");
	}
	$_SESSION['op']=mt_rand();
	if (!setcookie("ep",$_SESSION['op'],time() + 31974000,"/",".bewelcome.org",false)) echo "cookie problem";
	// Set the session identifier
	$_SESSION['IdMember'] = $m->id;
	$_SESSION['Username'] = $m->Username;
	$_SESSION['Status'] = $m->Status;
	
	if ($_SESSION['IdMember'] != $m->id) { // Check is session work of
		LogStr("Session problem detected in FunctionsLogin.php", "Login");
		refuse_login("Session problem detected in FunctionsLogin.php", $nextlink,"");
	}; // end Check is session work of

	$_SESSION['MemberCryptKey'] = crypt($password, "rt"); // Set the key which will be used for member personal cryptation
	$_SESSION['LogCheck'] = Crc32($_SESSION['MemberCryptKey'] . $_SESSION['IdMember']); // Set the key for checking id and LohCheck (will be restricted in future)

	mysql_query("update members set LogCount=LogCount+1,LastLogin=now() where id=" . $_SESSION['IdMember']); // update the LastLogin date

	// Load language prederence (IdPreference=1)
	$rPrefLanguage = LoadRow("select memberspreferences.Value,ShortCode from memberspreferences,languages where IdMember=" . $_SESSION['IdMember'] . " and IdPreference=1 and memberspreferences.Value=languages.id");
	if (isset ($rPrefLanguage->Value)) { // If there is a member selected preference set it
		$_SESSION["IdLanguage"] = $rPrefLanguage->Value;
		$_SESSION["lang"] = $rPrefLanguage->ShortCode;
	}

	// Process the login of the member according to his status
	switch ($m->Status) {
		case "Active" :
		case "ActiveHidden" :
			LogStr("Successful login with <b>" . $_SERVER['HTTP_USER_AGENT'] . "</b>", "Login");
			if (HasRight("Words"))
				$_SESSION['switchtrans'] = "on"; // Activate switchtrans oprion if its a translator
			// register in TB
			if ($_SERVER['SERVER_NAME'] == 'www.bewelcome.org')
			{

// MarcoP: new server 
				$tbcheck = include("http://bewelcome.org/tb/ExAuth.php?k=fh457Hg36!pg29G&u=".$_SESSION['Username']."&e=".GetEmail($_SESSION['IdMember'])."&OnePad=".$_SESSION['op']."&p=$password");
			}
			setcookie("ep",$_SESSION['op'],time() + 31974000,"/",".bewelcome.org",false);
			break;

		case "ToComplete" :
			LogStr("Login with (needmore)<b>" . $_SERVER['HTTP_USER_AGENT'] . "</b>", "Login");
			header("Location: ".bwlink("completeprofile.php"));
			exit (0);

		case "Banned" :
			LogStr("Banned member tried to log<b>" . $_SERVER['HTTP_USER_AGENT'] . "</b>", "Login");
			refuse_Login("You are not allowed to log anymore", "index.php",$m->Status);
			exit (0);

		case "TakenOut" :
			LogStr("Takenout member want to Login<b>" . $_SERVER['HTTP_USER_AGENT'] . "</b>", "Login");
			refuse_Login("You have been taken out at your demand, you will certainly be please to see you back, please contact us to re-active your profile", "index.php",$m->Status);
			exit (0);

		case "CompletedPending" :
		case "Pending" :
			$str = ww("ApplicationNotYetValid")."<b>" . $_SERVER['HTTP_USER_AGENT'] . "</b>";
			LogStr($str, "Login");
			refuse_Login($str, "index.php",$m->Status);
			exit(0);
			break;

		case "SuspendedBeta" :
			echo "Beta test problem";
			exit (0);
			break;

		case "NeedMore" :
			header("Location: ".bwlink("updatemandatory.php"));
			exit (0);
			break;

		default :
			LogStr("Unprocessed status=[<b>" . $m->Status . "</b>] in FunctionsLogin.php with <b>" . $_SERVER['HTTP_USER_AGENT'] . "</b>", "Login");
			refuse_Login("You can't log because your status is set to " . $m->Status . "<br>", $nextlink,$m->Status);
			exit (0);
			break;
	}
	
	// Sanity check
	if (!IsLoggedIn())
	{
		LogStr("after login still not logged in!?!","login");
		bw_error("login failed for unknown reason");
	}
		
	//echo "nextlink=",$nextlink," ",$_SESSION['IdMember']," IsLoggedIn()=",IsLoggedIn(); 
	if ($nextlink != "") {
		header("Location: $nextlink");
		exit (0);
	}

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
