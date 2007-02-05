<?php
require_once "lib/init.php";
require_once "layout/adminmandatory.php";

function loaddata($Status, $RestrictToIdMember = "") {

	global $AccepterScope;

	$TData = array ();

	if (($AccepterScope == "\"All\"") or ($AccepterScope == "All") or ($AccepterScope == "'All'")) {
		$InScope = "";
	} else {
		$InScope = "and countries.id in (" . $AccepterScope . ")";
	}

	$str = "select pendingmandatory.*,countries.Name as countryname,regions.Name as regionname,cities.Name as cityname,members.Username,members.FirstName as OldFirstName,members.IdCity,members.SecondName as OldSecondName,members.LastName as OldLastName,members.Status as Status from members,pendingmandatory,countries,regions,cities where cities.IdRegion=regions.id and regions.IdCountry=countries.id and cities.id=members.IdCity and members.id=pendingmandatory.IdMember and pendingmandatory.Status='Pending' and members.Status='" . $Status . "'";
	if ($RestrictToIdMember != "") {
		$str .= " and members.id=" . $RestrictToIdMember;
	}

	echo $str,"<br>" ;
	$qry = sql_query($str);
	while ($m = mysql_fetch_object($qry)) {

		$rAddress = LoadRow("select StreetName,Zip,HouseNumber,countries.id as IdCountry,cities.id as IdCity,regions.Name as regionname,cities.Name as cityname,regions.id as IdRegion from addresses,countries,regions,cities where IdMember=" . $m->IdMember . " and addresses.IdCity=cities.id and regions.id=cities.IdRegion and countries.id=regions.IdCountry");
		if (isset ($rAddress->IdCity)) {
			$m->OldStreetName = AdminReadCrypted($rAddress->StreetName);
			$m->OldZip = AdminReadCrypted($rAddress->Zip);
			$m->OldHouseNumber = AdminReadCrypted($rAddress->HouseNumber);
		}
		
		$m->OldFirstName=AdminReadCrypted($m->OldFirstName);
		$m->OldLastName=AdminReadCrypted($m->OldLastName);
		$m->OldSecondName=AdminReadCrypted($m->OldSecondName);
		
		$m->Email=AdminReadCrypted($m->Email);

		$m->ProfileSummary = FindTrad($m->ProfileSummary);
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
		// todo change what need to be change to answer in member default language
		$defLanguage=0 ;
		$lastaction = "accepting " . $m->Username;
		$str = "update members set Status='Active' where (Status='Pending' or Status='NeedMore' or Status='CompletedPending') and id=" . $IdMember;
		$qry = sql_query($str);

		$Email = AdminReadCrypted($m->Email);
		// todo change what need to be change to answer in member default language
		$subj = ww("SignupSubjAccepted", "http://".$_SYSHCVOL['SiteName']);
		$loginurl = "http://".$_SYSHCVOL['SiteName'] . $_SYSHCVOL['MainDir']."/login.php?&Username=" . $m->Username;
		$text = ww("SignupYouHaveBeenAccepted", $m->Username, "http://".$_SYSHCVOL['SiteName'], $loginurl);
		hvol_mail($Email, $subj, $text, "", $_SYSHCVOL['AccepterSenderMail'], $defLanguage, "yes", "", "");

		break;
	case "reject" :
		$m = LoadRow("select * from members where id=" . $IdMember);
		// todo change what need to be change to answer in member default language
		$defLanguage=0 ;
		$lastaction = "rejecting " . $m->Username;
		$str = "update members set Status='Rejected' where (Status='Pending' or Status='NeedMore' or Status='CompletedPending') and id=" . $IdMember;
		$qry = sql_query($str);

		$Email = AdminReadCrypted($m->Email);
		$subj = ww("SignupSubjRejected",$_SYSHCVOL['SiteName']);
		$text = ww("SignupYouHaveBeenRejected", $m->Username,$_SYSHCVOL['SiteName']);
//		echo "$subj<br>$text<br> sent to $Email<br> from ".$_SYSHCVOL['AccepterSenderMail'] ;
//		hvol_mail($Email,$subj,"text as test   ", "", $_SYSHCVOL['TestMail'], 0, "yes", "", "");
		hvol_mail($Email,$subj, $text, "", $_SYSHCVOL['AccepterSenderMail'],0, "yes", "", "");

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

$TData = loaddata("Active", $RestrictToIdMember);

DisplayAdminMandatory($TData, $lastaction); // call the layout
?>