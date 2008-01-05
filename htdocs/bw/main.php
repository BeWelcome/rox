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
require_once "lib/init.php";
require_once "lib/FunctionsLogin.php";
require_once "layout/error.php";
require_once "lib/prepare_profile_header.php";

switch (GetParam("action")) {
	case "confirmsignup" : // case a new signupper confirm his mail
		LogStr("ConfirmSignup for [".GetStrParam("username")."] IdMember=".IdMember(GetStrParam("username")), "Debug");
		$m = prepareProfileHeader(IdMember(GetStrParam("username"))," and Status='MailToConfirm' or Status='Pending' "); // pending members can edit their profile
//		$m = LoadRow("select * from members where id=".IdMember(GetStrParam("username"))." and Status='MailToConfirm' "); // pending members can edit their profile

		if (isset ($m->id)) {

			if (($m->Status != "MailToConfirm")and($m->Status != "Pending")) {
				$errcode = "ErrorMailAllreadyConfimed";
				LogStr("action confirm signup ErrorMailAllreadyConfimed Status=" . $m->Status, "login");
				DisplayError(ww($errcode, $m->Status));
				exit (0);
			}

			$_SESSION['IdMember'] = $m->id; // In this case we must have an identified member

			// todo here use something else that AdminReadCrypted (will not work when crypted right will be added)
			$key = CreateKey($m->Username, AdminReadCrypted($m->LastName), $m->id, "registration"); // retrieve the nearly unique key

			/*				
							  echo "key=",$key,"<br />";
							  echo " GetParam(\"key\")=",GetParam("key"),"<br />"; 
								echo "\$m->id=",$m->id,"<br />";
								echo "ReadCrypted(\$m->LastName)=",AdminReadCrypted($m->LastName),"<br />";
								echo "\$m->Username=",$m->Username,"<br />";
			*/

			if ($key != GetStrParam("key")) {
				$errcode = "ErrorBadKey";
				LogStr("Bad Key proposed=[".GetStrParam("key")."] expected [".$key."]", "hacking");
				DisplayError(ww($errcode));
				exit (0);
			}

			if (GetParam("StopBoringMe",0)==1) { // Case in fact the member doesn't want to be signup, but want to be removed
				 $str = "update members set Status='StopBoringMe' where id=" . $m->id;
				 sql_query($str);
				 LogStr("While his mail was not yet confirmed, member has ask us to stop boring him with confirmation request","StopBoringMe") ;
				 echo "OK, <b>",$m->Username,"</b> we will not send you this confirmation request anymore, thanks for visiting us" ;
				 die(0) ;
			}


			$str = "update members set Status='Pending' where id=" . $m->id; // The email is confirmed make the status Pending
			sql_query($str);
			LogStr("New Member is now at status <b>Pending</b>","Login") ;
			$m->Status = "Pending";
		}
		break;
	case "logout" :
		Logout();
		exit (0);
}
if ($m->Status == "Pending") { // Members with Pending status can only update ther profile
	if ($m->IdCity > 0) {
		$rWhere = LoadRow("select cities.Name as cityname,regions.Name as regionname,countries.Name as countryname from cities,countries,regions where cities.IdRegion=regions.id and countries.id=cities.IdCountry and cities.id=" . $m->IdCity);
	}
	include "layout/editmyprofile.php";
	$Message = ww("YouCanCompleteProfAndWait", $m->Username);
	DisplayEditMyProfile($m, "", "", 0, $rWhere->cityname, $rWhere->regionname, $rWhere->countryname, $Message, array ());
	exit (0);
}

if (IsLoggedIn()) {
	$m = LoadRow("select * from members where id=" . $_SESSION['IdMember']);
	$rr=LoadRow("select count(*) as cnt from mycontacts where IdMember=".$_SESSION['IdMember']);
	$m->NbContacts=$rr->cnt;
	include "layout/main.php";

	$TVisits=array() ;
   $str = "select profilesvisits.updated as datevisite,members.Username,members.ProfileSummary,cities.Name as cityname,regions.Name as regionname,countries.Name as countryname,membersphotos.FilePath as photo,membersphotos.Comment";
   $str .= " from cities,countries,regions,profilesvisits,members left join membersphotos on membersphotos.IdMember=members.id and membersphotos.SortOrder=0 where cities.IdRegion=regions.id and countries.id=cities.IdCountry and cities.id=members.IdCity and status='Active' and members.id=profilesvisits.IdVisitor and profilesvisits.IdMember=" . $_SESSION["IdMember"] . " and members.Status='Active' GROUP BY members.id order by profilesvisits.updated desc limit 3";
   $qry = sql_query($str);
  	while ($rr = mysql_fetch_object($qry)) {
	  if ($rr->Comment > 0) {
		$rr->phototext = FindTrad($rr->Comment);
	  } else {
		$rr->phototext = "no comment";
     }
	  if ($rr->ProfileSummary > 0) {
		$rr->ProfileSummary = FindTrad($rr->ProfileSummary);
	  } else {
		$rr->ProfileSummary = "";
	  }
//	  $rr->photo = getthumb($_SYSHCVOL['IMAGEDIR'] . substr($rr->photo,(strrpos($rr->photo,"/"))),80,80);
	  array_push($TVisits, $rr);
   } // end of while on visits
	
// retrieve the last member
	$mlast=LoadRow("select SQL_CACHE members.*,membersphotos.FilePath as photo,membersphotos.id as IdPhoto,countries.Name as countryname from members,membersphotos,cities,countries where membersphotos.IdMember=members.id and membersphotos.SortOrder=0 and members.Status='Active' and members.IdCity=cities.id and countries.id=cities.IdCountry order by members.id desc limit 1") ;
//	$mlast->photo = getthumb($_SYSHCVOL['IMAGEDIR'] . substr($mlast->photo,(strrpos($mlast->photo,"/"))),80,80);

	$rr=LoadRow("select SQL_CACHE count(*) as cnt from words where IdLanguage=0 and code like 'NewsTitle_%'") ;
	DisplayMain($m,$mlast,$TVisits,$rr->cnt);
} else {
	Logout();
	exit (0);
}
?>
