<?php
include "lib/dbaccess.php";
require_once "layout/adminaccepter.php";

function loaddata($Status, $RestrictToIdMember = "") {

	global $AccepterScope;

	$TData = array ();

	if (($AccepterScope == "\"All\"") or ($AccepterScope == "All") or ($AccepterScope == "'All'")) {
		$InScope = "";
	} else {
		$InScope = "and countries.id in (" . $AccepterScope . ")";
	}

	$str = "select countries.Name as countryname,regions.Name as regionname,cities.Name as cityname,members.* from members,countries,regions,cities where members.IdCity=cities.id and regions.id=cities.IdRegion and countries.id=regions.IdCountry " . $InScope . " and Status='" . $Status . "'";
	if ($RestrictToIdMember != "") {
		$str .= " and members.id=" . $RestrictToIdMember;
	}
	echo "str=$str<br>" ;
	$qry = sql_query($str);
	while ($m = mysql_fetch_object($qry)) {
		  echo $m->Username," ",$m->Status,"<br>" ;

		$StreetName = "";
		$Zip = "";
		$HouseNumber = "";
		$rAddress = LoadRow("select StreetName,Zip,HouseNumber,countries.id as IdCountry,cities.id as IdCity,regions.Name as regionname,cities.Name as cityname,regions.id as IdRegion from addresses,countries,regions,cities where IdMember=" . $m->id . " and addresses.IdCity=cities.id and regions.id=cities.IdRegion and countries.id=regions.IdCountry");
		if (isset ($rAddress->IdCity)) {
			$m->StreetName = AdminReadCrypted($rAddress->StreetName);
			$m->Zip = AdminReadCrypted($rAddress->Zip);
			$m->HouseNumber = AdminReadCrypted($rAddress->HouseNumber);
		}
		
		$m->Email=AdminReadCrypted($m->Email);

		$m->ProfileSummary = FindTrad($m->ProfileSummary);
		$FeedBack = "";
		$qryFeedBack = sql_query("select * from feedbacks where IdMember=" . $m->id . " and IdFeedbackCategory=3 order by id desc");
		while ($rrFeedBack = mysql_fetch_object($qryFeedBack)) {
			if ($FeedBack != "") {
				$FeedBack .= "<hr>";
			}
			$FeedBack .= $rrFeedBack->Discussion;
		}
		if ($FeedBack == "") {
			$m->FeedBack = "no FeedBack";
		} else {
			$m->FeedBack = $FeedBack;
		}
		array_push($TData, $m);
	}

	return ($TData);

} // end of load data

//------------------------------------------------------------------------------

MustLog(); // need to be log

$IdMember = GetParam("cid");

$countmatch = 0;

$RightLevel = HasRight('Accepter'); // Check the rights
if ($RightLevel < 1) {
	echo "This Need the sufficient <b>Accepter</b> rights<br>";
	exit (0);
}

$AccepterScope = RightScope('Accepter');
if ($AccepterScope != "All") {
	$AccepterScope = str_replace("\"", "'", $AccepterScope);
}

$lastaction = "";
switch (GetParam("action")) {
	case "logout" :
		Logout("main.php");
		exit (0);
		break;
	case "accept" :
		$m = LoadRow("select * from members where id=" . $IdMember);
		$lastaction = "accepting " . $m->Username;
		$str = "update members set Status='Active' where (Status='Pending' or Status='NeedMore' or Status='CompletedPending') and id=" . $IdMember;
		$qry = sql_query($str);

		$Email = AdminReadCrypted($m->Email);
		// todo change what need to be change to answer in member default language
		$subj = ww("SignupSubjAccepted", "http://".$_SYSHCVOL['SiteName']);
		$loginurl = "http://".$_SYSHCVOL['SiteName'] . "/login.php?&Username=" . $m->Username;
		$text = ww("SignupYouHaveBeenAccepted", $m->Username, "http://".$_SYSHCVOL['SiteName'], $loginurl);
		hvol_mail($Email, $subj, $text, $hh, $_SYSHCVOL['AccepterSenderMail'], $_SESSION['IdLanguage'], "", "", "");

		break;
	case "reject" :
		$m = LoadRow("select * from members where id=" . $IdMember);
		$lastaction = "rejecting " . $m->Username;
		$str = "update members set Status='Rejected' where (Status='Pending' or Status='NeedMore' or Status='CompletedPending') and id=" . $IdMember;
		$qry = sql_query($str);

		$Email = AdminReadCrypted($m->Email);
		// todo change what need to be change to answer in member default language
		$subj = ww("SignupSubjRejected", $_SYSHCVOL['SiteName']);
		$loginurl = $_SYSHCVOL['SiteName'] . "/login.php?&Username=" . $m->Username;
		$text = ww("SignupYouHaveBeenRejected", $m->Username);
		hvol_mail($Email, $subj, $text, $hh, $_SYSHCVOL['AccepterSenderMail'], $_SESSION['IdLanguage'], "", "", "");

		break;
	case "needmore" :
		$m = LoadRow("select * from members where id=" . $IdMember);
		$lastaction = "setting profile of  " . $m->Username . " from " . $m->Status . " to NeedMore";

		$str = "update members set Status='NeedMore' where (Status='Pending' or Status='Active' or Status='CompletedPending') and id=" . $IdMember;
		$qry = sql_query($str);
		// to do manage the need more
		break;

	case "ShowOneMember" :
		$RestrictToIdMember = IdMember(GetParam("cid", 0));
		break;
}

$Taccepted = loaddata("Active", $RestrictToIdMember);
$Tmailchecking = loaddata("MailToConfirm", $RestrictToIdMember);
$Tpending = loaddata("Pending", $RestrictToIdMember);
$TNeedMore = loaddata("Needmore", $RestrictToIdMember);

DisplayAdminAccepter($Taccepted, $Tmailchecking, $Tpending, $TNeedMore, $lastaction); // call the layout
?>