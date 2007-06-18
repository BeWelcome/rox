<?php
require_once "../lib/init.php";
require_once "../layout/adminmandatory.php";

function loaddata($Status, $RestrictToIdMember = "") {

	global $AccepterScope;

	$TData = array ();

	if (($AccepterScope == "\"All\"") or ($AccepterScope == "All") or ($AccepterScope == "'All'")) {
		$InScope = "";
	} else {
		$InScope = "and countries.id in (" . $AccepterScope . ")";
	}

	$str = "select pendingmandatory.*,countries.Name as countryname,regions.Name as regionname,cities.Name as cityname,members.Username,members.FirstName as OldFirstName,pendingmandatory.IdCity,members.SecondName as OldSecondName,members.LastName as OldLastName,members.Status as Status from members,pendingmandatory,countries,regions,cities where cities.IdRegion=regions.id and cities.IdCountry=countries.id and cities.id=pendingmandatory.IdCity and members.id=pendingmandatory.IdMember and pendingmandatory.Status='Pending' and members.Status='" . $Status . "'";
	if ($RestrictToIdMember != "") {
		$str .= " and members.id=" . $RestrictToIdMember;
	}
	$str.=" order by members.id,pendingmandatory.created desc";

//	echo $str,"<br>";
	$qry = sql_query($str);
	while ($m = mysql_fetch_object($qry)) {

		$rAddress = LoadRow("select StreetName,Zip,HouseNumber,countries.id as IdCountry,cities.id as IdCity,regions.Name as regionname,cities.Name as cityname,countries.Name as countryname,regions.id as IdRegion from addresses,countries,regions,cities where IdMember=" . $m->IdMember . " and addresses.IdCity=cities.id and regions.id=cities.IdRegion and countries.id=cities.IdCountry");
		if (isset ($rAddress->IdCity)) {
			$m->OldStreetName = AdminReadCrypted($rAddress->StreetName);
			$m->OldZip = AdminReadCrypted($rAddress->Zip);
			$m->OldHouseNumber = AdminReadCrypted($rAddress->HouseNumber);
			
			$m->OldCountryName=$rAddress->countryname;
			$m->OldRegionName=$rAddress->regionname;
			$m->OldCityName=$rAddress->cityname;
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

MustLogIn(); // need to be log

$IdMember = GetParam("cid");

$countmatch = 0;

$RightLevel = HasRight('Accepter'); // Check the rights
if ($RightLevel < 1) {
	echo "<p>This Need the sufficient <strong>Accepter</strong> rights</p>";
	exit (0);
}

$AccepterScope = RightScope('Accepter');
if ($AccepterScope != "All") {
	$AccepterScope = str_replace("\"", "'", $AccepterScope);
}

$lastaction = "";
$IdPending=GetParam("IdPending");
switch (GetParam("action")) {
	case "done" :
		$pp = LoadRow("select * from pendingmandatory where id=" . $IdPending);
		$str="update pendingmandatory set Status='Processed' where id=".$pp->id;
		sql_query($str);
		LogStr("Updating mandatory data mark done address for <b>",$m->Username,"</b>","adminmandatory");
		break;
	case "updatename" :
		$pp = LoadRow("select * from pendingmandatory where id=" . $IdPending);
		$m=LoadRow("select * from members where id=".$pp->IdMember);
		$str="update members set FirstName =".ReplaceInCrypted($pp->FirstName, $m->FirstName, $m->id);
		$str.=",SecondName = ".ReplaceInCrypted($pp->SecondName, $m->SecondName, $m->id);
		$str.=",LastName=".ReplaceInCrypted($pp->LastName, $m->LastName, $m->id);
		$str.=" where members.id=".$m->id;
		sql_query($str);
		LogStr("Updating mandatory data name address for <b>",$m->Username,"</b>","adminmandatory");
		break;
	case "updateaddress" :
		$pp = LoadRow("select * from pendingmandatory where id=" . $IdPending);
		$m=LoadRow("select * from members where id=".$pp->IdMember);
		
     	$IdAddress=0;
		// in case the update is made by a volunteer
		$rr = LoadRow("select * from addresses where IdMember=" . $m->id." and Rank=0");
		if (isset ($rr->id)) { // if the member already has an address
			$IdAddress=$rr->id;
		}
		if ($IdAddress!=0) { // if the member already has an address
				$str = "update addresses set IdCity=" . $pp->IdCity . ",HouseNumber=" . ReplaceInCrypted($pp->HouseNumber, $rr->HouseNumber, $m->id) . ",StreetName=" . ReplaceInCrypted($pp->StreetName, $rr->StreetName, $m->id) . ",Zip=" . ReplaceInCrypted($pp->Zip, $rr->Zip, $m->id) . " where id=" . $IdAddress;
				sql_query($str);
		} else {
				$str = "insert into addresses(IdMember,IdCity,HouseNumber,StreetName,Zip,created,Explanation) Values(" . $_SESSION['IdMember'] . "," . $IdCity . "," . InsertInCrypted($pp->HouseNumber) . "," . InsertInCrypted($pp->StreetName) . "," . InsertInCrypted($pp->Zip) . ",now(),\"Address created by adminmandatory\")";
				sql_query($str);
			    $IdAddress=mysql_insert_id();
		}


		$str="update members set IdCity =".$pp->IdCity." where members.id=".$m->id;
		sql_query($str);
		LogStr("Updating mandatory data address for <b>",$m->Username,"</b>","adminmandatory");
		break;

	case "reject" :
		$pp = LoadRow("select * from pendingmandatory where id=" . $IdPending);
		$str="update pendingmandatory set Status='Rejected' where id=".$pp->id;
		sql_query($str);
		LogStr("Updating mandatory data rejecting address for <b>",$m->Username,"</b>","adminmandatory");
		break;

	case "ShowOneMember" :
		$RestrictToIdMember = IdMember(GetParam("cid", 0));
		break;
}

$TData = loaddata("Active", $RestrictToIdMember);

DisplayAdminMandatory($TData, $lastaction); // call the layout
?>