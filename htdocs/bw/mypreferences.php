<?php
require_once "lib/init.php";
require_once "lib/prepare_profile_header.php";
require_once "layout/error.php";
require_once "layout/mypreferences.php";

MustLogIn();

$IdMember = $_SESSION['IdMember'];
$photorank = 0; // Alway use picture 0 on preference page 

if (HasRight(Admin)) { // Admin will have access to any member right thru cid
	$IdMember = IdMember(GetStrParam("cid", $_SESSION['IdMember']));
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
			$Value = GetStrParam($rWhile->codeName);
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
				//					echo "str=",$str,"<br>";
				sql_query($str);
			}
		}
		LogStr("updating/inserting " . $countupdate . "/" . $countinsert . " preferences", "Update Preference");

		$rPublicPref = LoadRow("select * from memberspublicprofiles where IdMember=" . $IdMember);
		if (GetStrParam(PreferencePublicProfile) == "Yes") {
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
		
		if (isset($_SESSION["stylesheet"])) unset($_SESSION["stylesheet"]) ; // clean the style sheet cache


		break;
		
	case "UpdateOne" : //this is supposed to be called via a link mypreferences.php?action=UpdateOne&IdPreference=1&NewValue=fr for example (force french language)
		$rPref = LoadRow("select * from preferences where id=".GetParam("IdPreference"));
		$IdMember=$_SESSION["IdMember"] ;
		$IdPreference=GetStrParam("IdPreference") ;
		$Value=GetStrParam("NewValue") ;
		
		$rr = LoadRow("select memberspreferences.id as id from memberspreferences,preferences where IdMember=" . $IdMember . " and IdPreference=preferences.id and preferences.id=" . $IdPreference );
		if (isset ($rr->id)) {
					$str = "update memberspreferences set Value='" . addslashes($Value) . "' where id=" . $rr->id;
					sql_query($str);
					LogStr("updating one preference " . $rPref->codeName . "To Value <b>/" . $Value . " </b>", "Update Preference");
		} else {
					$str = "insert into memberspreferences(IdPreference,IdMember,Value,created) values(" . $IdPreference . "," . $IdMember . ",'" . addslashes($Value) . "',now() )";
					sql_query($str);
					LogStr("inserting one preference " . $rPref->codeName . "To Value <b>/" . $Value . " </b>", "Update Preference");
		}

		$m = prepareProfileHeader($IdMember,"",0);
		
		DisplayOneUpdate($m,$rPref->codeName, $Value) ; // call the display
		 

		


		break;
}

// Try to load or reload the Preferences, prepare the layout data
//  $str="select preferences.*,Value from preferences left join memberspreferences on memberspreferences.IdPreference=preferences.id and memberspreferences=".$IdMember;
$str = "select preferences.*,Value from preferences left join memberspreferences on memberspreferences.IdPreference=preferences.id and memberspreferences.IdMember=" . $IdMember." where preferences.Status!='Inactive'";
$qry = sql_query($str);
$TPref = array ();
while ($rWhile = mysql_fetch_object($qry)) {
	array_push($TPref, $rWhile);
}

$m = prepareProfileHeader($IdMember,"",0); 

// Load wether its inside the public profiles	
$m->TPublic = LoadRow("select * from memberspublicprofiles where IdMember=" . $IdMember);

DisplayMyPreferences($TPref, $m, $IdMember); // call the layout
?>
