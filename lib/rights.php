<?php
/*
 * Created on 26.3.2007
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */
 
/**
* MustLogIn force the user to log and then call the link passed in parameter
*/
function MustLogIn($paramnextlink = "") {
	require_once ("FunctionsLogin.php");
	$nextlink=$paramnextlink;
	if ($nextlink == "") {
		$nextlink = $_SERVER['PHP_SELF'];
		if (!empty($_SERVER['QUERY_STRING'])) {
		   $nextlink .="?".$_SERVER['QUERY_STRING'];
		}
	}
	if (!IsLoggedIn()) { // Need to be logged
		Logout($nextlink);
		exit (0);
	}
} // end of MustLogIn

/**
 * print an error and die if the user is not logged in or is not an admin
 * @param string $errortext error text to be printed 
 */
function MustBeAdmin()
{
	MustLogIn();
	
	if (!IsAdmin())
		bw_error("Only for admins!");
}

/**
* MustLogIn force the user to log and then call the link passed in parameter
*/
function IsAdmin() {
	return (HasRight('Admin'));
} // end of IsAdmin()

/**
* IsVol return true wether the members is a volunteer or not
* (it mean : if the members has any special right)
* @return boolean
*/
function IsVol() {
	if (isset($_SESSION["IsVol"])) {
	    return($_SESSION["IsVol"]) ;
	}
	if (IsLoggedIn()) {
		$rr=LoadRow("select count(*) as cnt from from rightsvolunteers where IdMember=$IdMember and rights.Level>0");
		$_SESSION["IsVol"]=$rr->cnt ;
	    return($_SESSION["IsVol"]) ;
	}
	else retrun(false) ;
} // end of IsVol()

/**
* check if the user is a logged in member
* @return boolean
*/
function IsLoggedIn() {

	if (empty($_SESSION['IdMember'])) {
		return (false);
	}

	if (empty($_SESSION['MemberCryptKey'])) {
		//	  LogStr("IsLoggedIn() : Anomaly with MemberCryptKey","Bug");
		return (false);
	}

	if ($_SESSION['LogCheck'] != Crc32($_SESSION['MemberCryptKey'] . $_SESSION['IdMember'])) {
		LogStr("Anomaly with Log Check", "Hacking");
		require_once("login.php");
		Logout();
		exit (0);
	}
	return (true);
} // end of IsLoggedIn

// -----------------------------------------------------------------------------
// return the RightLevel if the members has the Right RightName 
// optional Scope value can be send if the RightScope is set to All then Scope
// will always match if not, the sentence in Scope must be find in RightScope
// The function will use a cache in session
// $_SYSHCVOL['ReloadRight']=='True' is used to force RightsReloading
// from scope beware to the "" which must exist in the mysal table but NOT in 
// the $Scope parameter 
// $OptionalIdMember  allow to specify another member than the current one, in this case the cache is not used
function HasRight($RightName, $_Scope = "", $OptionalIdMember = 0) 
{
	global $_SYSHCVOL;

	if (!IsLoggedIn())
		return (0); // No need to search for right if no member logged
	if ($OptionalIdMember != 0) {
		$IdMember = $OptionalIdMember;
	} else {
		$IdMember = $_SESSION['IdMember'];
	}

	$Scope = $_Scope;
	if ($Scope != "") {
		if ($Scope {
			0 }
		!= "\"")
		$Scope = "\"" . $Scope . "\""; // add the " " if they are missing 
	}

	if ((!isset ($_SESSION['Right_' . $RightName])) or 
		($_SYSHCVOL['ReloadRight'] == 'True') or 
		($OptionalIdMember != 0)) {
		$str = "select SQL_CACHE Scope,Level from rightsvolunteers,rights where IdMember=$IdMember and rights.id=rightsvolunteers.IdRight and rights.Name='$RightName'";
		$qry = mysql_query($str) or bw_error("function HasRight");
		$right = mysql_fetch_object(mysql_query($str)); // LoadRow not possible because of recusivity
		if (!isset ($right->Level))
			return (0); // Return false if the Right does'nt exist for this member in the DB
		$rlevel = $right->Level;
		$rscope = $right->Scope;
		if ($OptionalIdMember == 0) { // if its current member cache for next research 
			$_SESSION['RightLevel_' . $RightName] = $rlevel;
			$_SESSION['RightScope_' . $RightName] = $rscope;
		}
	}
	if ($Scope != "") { // if a specific scope is asked
		if ($rscope == "\"All\"") {
			if (($_SESSION["IdMember"]) == 1)
				return (10); // Admin has all rights at level 10
			return ($rlevel);
		} else {
			if ((!(strpos($rscope, $Scope) === false)) or ($Scope == $rscope)) {
				return ($rlevel);
			} else
				return (0);
		}
	} else {
		if (($_SESSION["IdMember"]) == 1)
			return (10); // Admin has all rights at level 10
		return ($rlevel);
	}
} // enf of HasRight

// -----------------------------------------------------------------------------
// return the Scope in the specific right 
// The funsction will use a cache in session
//   $_SYSHCVOL['ReloadRight']=='True' is used to force RightsReloading
//  from scope beware to the "" which must exist in the mysal table but NOT in 
// the $Scope parameter 
function RightScope($RightName, $Scope = "") {
	global $_SYSHCVOL;

	if (!IsLoggedIn())
		return (0); // No need to search for right if no member logged
	$IdMember = $_SESSION['IdMember'];
	if ((!isset ($_SESSION['Right_' . $RightName])) or ($_SYSHCVOL['ReloadRight'] == 'True')) {
		$str = "select SQL_CACHE Scope,Level from rightsvolunteers,rights where IdMember=$IdMember and rights.id=rightsvolunteers.IdRight and rights.Name='$RightName'";
		$qry = mysql_query($str) or die("function RightScope");
		$right = mysql_fetch_object(mysql_query($str)); // LoadRow not possible because of recusivity
		if (!isset ($right->Level)) {
			return (""); // Return false if the Right does'nt exist for this member in the DB
		}
		$_SESSION['RightLevel_' . $RightName] = $right->Level;
		$_SESSION['RightScope_' . $RightName] = $right->Scope;
	}
	return ($_SESSION['RightScope_' . $RightName]);
} // enf of Scope

//------------------------------------------------------------------------------
// check if the current user has some translation rights on IdMember
function CanTranslate($IdMember) {
	$IdTranslator = $_SESSION["IdMember"];
	$IdLanguage = $_SESSION["IdLanguage"];
	if (empty($IdTranslator)) return(false);
	
	$rr = LoadRow("select SQL_CACHE id from intermembertranslations where IdMember=" . $IdMember . " and IdTranslator=" . $IdTranslator . " and IdLanguage=" . $IdLanguage);
	if (!isset ($rr->id))
		return false;
	else
		return ($rr->id);
} // end CanTranslate

?>
