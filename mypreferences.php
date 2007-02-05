<?php
require_once "lib/init.php";
require_once "lib/FunctionsLogin.php";
require_once "layout/error.php";

if (!IsLoggedIn()) {
	$errcode = "ErrorMustBeLogged";
	DisplayError(ww($errcode));
	exit (0);
}

$IdMember = $_SESSION['IdMember'];
$photorank = 0; // Alway use picture 0 on preference page 

if (HasRight(Admin)) { // Admin will have access to any member right thru cid
	$IdMember = IdMember(GetParam("cid", $_SESSION['IdMember']));
}

// Try to load the member
$str = "select * from members where id=" . $IdMember . " and Status='Active'";

$m = LoadRow($str);

switch (GetParam("action")) {
	case "logout" :
		Logout("main.php");
		exit (0);
	case "Update" :
		$str = "select * from preferences";
		$qry = mysql_query($str);
		$countinsert = 0;
		$countupdate = 0;
		while ($rWhile = mysql_fetch_object($qry)) { // browse all preference
			$Value = GetParam($rWhile->codeName);
			if ($Value != "") {
				$rr = LoadRow("select memberspreferences.id as id from memberspreferences,preferences where IdMember=" . $IdMember . " and IdPreference=preferences.id and preferences.codeName='" . $rWhile->codeName . "'");
				if (isset ($rr->id)) {
					$str = "update memberspreferences set Value='" . addslashes($Value) . "' where id=" . $rr->id;
					$countupdate++;
				} else {
					$str = "insert into memberspreferences(IdPreference,IdMember,Value,created) values(" . $rWhile->id . "," . $IdMember . ",'" . addslashes($Value) . "',now() )";
					$countinsert++;
				}
				$count++;
				//					echo "str=",$str,"<br>" ;
				sql_query($str);
			}
		}
		LogStr("updating/inserting " . $countupdate . "/" . $countinsert . " preferences", "Update Preference");

		$rPublicPref = LoadRow("select * from memberspublicprofiles where IdMember=" . $IdMember);
		if (GetParam(PreferencePublicProfile) == "Yes") {
			if (!isset ($rPublicPref->id)) {
				$str = "insert into memberspublicprofiles(IdMember,created,type) values(" . $IdMember . ",now(),'normal')";
				sql_query($str);
				LogStr("Set public profile", "Update Preference");
			}
		} else {
			if (isset ($rPublicPref->id)) {
				$str = "delete from memberspublicprofiles where IdMember=" . $IdMember;
				sql_query($str);
				LogStr("Remove public profile", "Update Preference");
			}
		}

		break;
}

// Try to load or reload the Preferences, prepare the layout data
//  $str="select preferences.*,Value from preferences left join memberspreferences on memberspreferences.IdPreference=preferences.id and memberspreferences=".$IdMember ;
$str = "select preferences.*,Value from preferences left join memberspreferences on memberspreferences.IdPreference=preferences.id and memberspreferences.IdMember=" . $IdMember;
$qry = sql_query($str);
$TPref = array ();
while ($rWhile = mysql_fetch_object($qry)) {
	array_push($TPref, $rWhile);
}

// Load photo data
$photo = "";
$phototext = "";
$str = "select * from membersphotos where IdMember=" . $IdMember . " and SortOrder=" . $photorank;
$rr = LoadRow($str);
if (!isset ($rr->FilePath) and ($photorank > 0)) {
	$rr = LoadRow("select * from membersphotos where IdMember=" . $IdMember . " and SortOrder=0");
}
if (isset ($rr->FilePath)) {
	$photo = $rr->FilePath;
	$phototext = FindTrad($rr->Comment);
	$photorank = $rr->SortOrder;
}
$m->photo = $photo;
$m->photorank = $photorank;
$m->phototext = $phototext;

// Load geography
if ($m->IdCity > 0) {
	$rWhere = LoadRow("select cities.Name as cityname,regions.Name as regionname,countries.Name as countryname from cities,countries,regions where cities.IdRegion=regions.id and countries.id=regions.IdCountry and cities.id=" . $m->IdCity);
	$m->cityname = $rWhere->cityname;
	$m->regionname = $rWhere->regionname;
	$m->countryname = $rWhere->countryname;
}

// Load nbcomments nbtrust
$m->NbTrust = 0;
$m->NbComment = 0;
$rr = LoadRow("select count(*) as cnt from comments where IdToMember=" . $m->id . " and Quality='Good'");
if (isset ($rr->cnt))
	$m->NbTrust = $rr->cnt;
$rr = LoadRow("select count(*) as cnt from comments where IdToMember=" . $m->id);
if (isset ($rr->cnt))
	$m->NbComment = $rr->cnt;

if ($m->LastLogin == "11/30/99 00:00:00")
	$m->LastLogin = ww("NeverLog");
else
	$m->LastLogin = localdate($m->LastLogin);

// Load Age
$m->age = fage($m->BirthDate, $m->HideBirthDate);

// Load wether its inside the public profiles	
$m->TPublic = LoadRow("select * from memberspublicprofiles where IdMember=" . $IdMember);

require_once "layout/mypreferences.php";
DisplayMyPreferences($TPref, $m, $IdMember); // call the layout
?>
