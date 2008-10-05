<?php
/*

Copyright (c) 2007 BeVolunteer

This file is part of BW Rox.

BW Rox is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

BW Rox is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, see <http://www.gnu.org/licenses/> or 
write to the Free Software Foundation, Inc., 59 Temple Place - Suite 330, 
Boston, MA  02111-1307, USA.

*/
require_once "../lib/init.php";
require_once "../layout/adminmandatory.php";

function loaddata($Status, $RestrictToIdMember = "") {

	global $AccepterScope;

	$TData = array ();

	if (($AccepterScope == "\"All\"") or ($AccepterScope == "All") or ($AccepterScope == "'All'")) {
		$InScope = "";
	} else {
	  $tt=explode(",",$AccepterScope) ;
	  $TheScope="" ;
	  for ($ii=0;((isset($tt)) and $ii<count($tt));$ii++) {
	  	if ($ii!=0) $TheScope .="," ; 
		$val=ltrim(rtrim(str_replace("\""," ",$tt[$ii]))) ; // remove the "
		if ($val>0) {
		   $TheScope = $TheScope.GetCountryName($val); // If it was an IdCcountry (numeric) retrieve the countryname 
		}
		else {
		   $TheScope = $TheScope."'".$val."'"; // else it is supposed to be a country name
		}
	  }
	  if ($TheScope=="") {
	  	 $InScope = " and 1=0"; // no way, user has no scope
	  }
	  else {
	  	 $InScope = "and countries.Name in (\"".$TheScope."\")";
	  }
	}
	

	$str = "select cities.IdRegion as IdRegion,pendingmandatory.*,countries.Name as countryname,cities.Name as cityname,members.Username,members.FirstName as OldFirstName,pendingmandatory.IdCity,members.SecondName as OldSecondName,members.LastName as OldLastName,members.Status as Status from members,pendingmandatory,countries,cities where cities.IdCountry=countries.id and cities.id=pendingmandatory.IdCity and members.id=pendingmandatory.IdMember and pendingmandatory.Status='Pending' and members.Status='" . $Status . "' ".$InScope;
	if ($RestrictToIdMember != "") {
		$str .= " and members.id=" . $RestrictToIdMember;
	}
	$str.=" order by members.id,pendingmandatory.created desc";

//	echo $str,"<br>";
	$qry = sql_query($str);
	while ($m = mysql_fetch_object($qry)) {

		$m->regionname=getregionname($m->IdRegion);
		$rAddress = LoadRow("select StreetName,Zip,HouseNumber,countries.id as IdCountry,cities.id as IdCity,cities.IdRegion as IdRegion,cities.Name as cityname,countries.Name as countryname from addresses,countries,cities where IdMember=" . $m->IdMember . " and addresses.IdCity=cities.id and countries.id=cities.IdCountry and addresses.Rank=0");
		if (isset ($rAddress->IdCity)) {
			$m->OldStreetName = AdminReadCrypted($rAddress->StreetName);
			$m->OldZip = AdminReadCrypted($rAddress->Zip);
			$m->OldHouseNumber = AdminReadCrypted($rAddress->HouseNumber);
			
			$m->OldCountryName=$rAddress->countryname;
			$m->OldRegionName=getregionname($rAddress->IdRegion);
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
	echo "<p>For this you need the <strong>Accepter</strong> rights</p>";
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
		LogStr("Updating mandatory data mark done address for <b>".fUsername($pp->Idmember)."</b> #id=".$pp->id,"adminmandatory");
		break;
	case "updatename" :
		$pp = LoadRow("select * from pendingmandatory where id=" . $IdPending);
		$m=LoadRow("select * from members where id=".$pp->IdMember);
		$str="update members set FirstName =".ReplaceInCrypted($pp->FirstName, $m->FirstName, $m->id);
		$str.=",SecondName = ".ReplaceInCrypted($pp->SecondName, $m->SecondName, $m->id);
		$str.=",LastName=".ReplaceInCrypted($pp->LastName, $m->LastName, $m->id);
		$str.=" where members.id=".$m->id;
		sql_query($str);
		LogStr("Updating mandatory data name address for <b>".$m->Username."</b>","adminmandatory");
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
				$str = "update addresses set IdCity=" . $pp->IdCity . ",HouseNumber=" . NewReplaceInCrypted($pp->HouseNumber,"addresses.HouseNumber",$IdAddress, $rr->HouseNumber, $m->id) . ",StreetName=" . NewReplaceInCrypted($pp->StreetName,"addresses.StreetName",$IdAddress, $rr->StreetName, $m->id) . ",Zip=" . ReplaceInCrypted($pp->Zip,"addresses.Zip",$IdAddress, $rr->Zip, $m->id) . " where id=" . $IdAddress;
				sql_query($str);
		} else {
				$str = "insert into addresses(IdMember,IdCity,HouseNumber,StreetName,Zip,created,Explanation) Values(" . $_SESSION['IdMember'] . "," . $IdCity . "," . NewInsertInCrypted($pp->HouseNumber,"addresses.HouseNumber",0) . "," . NewInsertInCrypted($pp->StreetName,"addresses.StreetName",0) . "," . NewInsertInCrypted($pp->Zip,"addresses.Zip",0) . ",now(),\"Address created by adminmandatory\")";
				sql_query($str);
			    $IdAddress=mysql_insert_id();
		}


		$str="update members set IdCity =".$pp->IdCity." where members.id=".$m->id;
		sql_query($str);
		LogStr("Updating mandatory data address for <b>".$m->Username."</b>","adminmandatory");
		break;

	case "reject" :
		$pp = LoadRow("select * from pendingmandatory where id=" . $IdPending);
		$str="update pendingmandatory set Status='Rejected' where id=".$pp->id;
		sql_query($str);
		LogStr("Updating mandatory data rejecting address for <b>".fUsername($pp->IdMember)."</b>","adminmandatory");
		break;

	case "ShowOneMember" :
		$RestrictToIdMember = IdMember(GetParam("cid", 0));
		break;
}

$TData = loaddata("Active", $RestrictToIdMember);

DisplayAdminMandatory($TData, $lastaction); // call the layout
?>