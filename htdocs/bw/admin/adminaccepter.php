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
chdir("..") ;
require_once "lib/init.php";
require_once "lib/f_volunteer_boards.php" ;
require_once "layout/adminaccepter.php";


// $IdEmail allow to list all members having a specific email
// $Status allow to filter a status
// $RestrictToIdMember allow to restrict to a member
function loaddata($Status, $RestrictToIdMember = "",$IdEmail=0) {

	global $AccepterScope,$_SYSHCVOL,$lastaction;

	$TData = array ();

	if (($AccepterScope == "\"All\"") or ($AccepterScope == "All") or ($AccepterScope == "'All'")) {
		$InScope = "";
	} else {
	  $tt=explode(",",$AccepterScope) ;
	  $TheScope="" ;
	  for ($ii=0;((isset($tt)) and $ii<count($tt));$ii++) {
	  	if ($ii!=0) $TheScope .="," ; 
		$val=ltrim(rtrim(str_replace("\"","",$tt[$ii]))) ; // remove the "
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
//	  	 $InScope = "and countries.Name in (\"".$TheScope."\")";
	  	 $InScope = "and (countries.Name in (".$AccepterScope.") or countries.id in (".$AccepterScope."))";
	  }
	}
	
	$emailtable="" ;
	$emailwhere="" ;
	if ($IdEmail!=0) { // If an email was provided then prepare what is needed to filer all member having this email
	   $rr=LoadRow("select * from ".$_SYSHCVOL['Crypted']."cryptedfields". " where id=".$IdEmail) ;
	   $Email=$rr->AdminCryptedValue ;
	   $emailtable=",".$_SYSHCVOL['Crypted']."cryptedfields" ;
	   $emailwhere=" and members.Email=".$_SYSHCVOL['Crypted']."cryptedfields.id and ".$_SYSHCVOL['Crypted']."cryptedfields.AdminCryptedValue='".$Email."'" ;
	   $lastaction=$lastaction." Seek all members with a duplicated email" ;
	   $str = "SELECT countries.Name AS countryname,cities.IdRegion AS IdRegion,cities.Name AS cityname,members.* FROM members,countries,cities".$emailtable." WHERE members.IdCity=cities.id AND countries.id=cities.IdCountry " . $InScope .$emailwhere;
	}
	else {
	   $str = "SELECT countries.Name AS countryname,cities.IdRegion AS IdRegion,cities.Name AS cityname,members.* FROM members,countries,cities".$emailtable." WHERE members.IdCity=cities.id AND countries.id=cities.IdCountry " . $InScope . " AND Status='" . $Status . "'".$emailwhere;
	}

	if ($RestrictToIdMember != "") {
		$str .= " AND members.id=" . $RestrictToIdMember;
	}
	
	$str_desc="desc" ;
	$str=$str." order by members.created ".$str_desc." limit ".GetParam("Limit",50) ;
	
	

	$qry = sql_query($str);
	while ($m = mysql_fetch_object($qry)) {
		$m->regionname=getregionname($m->IdRegion) ;

		$StreetName = "";
		$Zip = "";
		$HouseNumber = "";
		$rAddress = LoadRow("SELECT StreetName,Zip,HouseNumber,countries.id AS IdCountry,cities.id AS IdCity,cities.Name AS cityname,cities.IdRegion AS IdRegion from addresses,countries,cities WHERE IdMember=" . $m->id . " AND addresses.IdCity=cities.id AND countries.id=cities.IdCountry");
		if (isset ($rAddress->IdCity)) {
			$m->StreetName = AdminReadCrypted($rAddress->StreetName);
			$m->Zip = AdminReadCrypted($rAddress->Zip);
			$m->HouseNumber = AdminReadCrypted($rAddress->HouseNumber);
		}
		
		$m->IdEmail=$m->Email ;
		$m->Email=AdminReadCrypted($m->Email);

		$m->FirstName=AdminReadCrypted($m->FirstName);
		$m->LastName=AdminReadCrypted($m->LastName);
		$m->SecondName=AdminReadCrypted($m->SecondName);

		$m->ProfileSummary = FindTrad($m->ProfileSummary);
		$FeedBack = "";
		$qryFeedBack = sql_query("SELECT * FROM feedbacks WHERE IdMember=" . $m->id . " AND IdFeedbackCategory=3 ORDER BY id DESC");
		while ($rrFeedBack = mysql_fetch_object($qryFeedBack)) {
			if ($FeedBack != "") {
				$FeedBack .= "<hr />";
			}
			$FeedBack .= $rrFeedBack->Discussion;
		}
		if ($FeedBack == "") {
			$m->FeedBack = "no FeedBack";
		} else {
			$m->FeedBack = $FeedBack;
		}
		$m->Email=GetEmail($m->id);
		array_push($TData, $m);
	}

	return ($TData);

} // end of load data

//------------------------------------------------------------------------------

MustLogIn(); // need to be log

$IdMember = IdMember(GetStrParam("cid"));

$countmatch = 0;

$RightLevel = HasRight('Accepter'); // Check the rights
if ($RightLevel < 1) {
	echo "This Need the sufficient <b>Accepter</b> rights<br>";
	exit (0);
}
$AccepterScope = RightScope('Accepter');
if ($AccepterScope != "All") { 
	$AccepterScope = str_replace("'", "\"", $AccepterScope); // To be sure than nobody used ' instead of " (todo : this test will be to remoev some day)
}

$LastAction=$StrLog = "";
switch (GetParam("action")) {
	case "batchaccept" :
		$max=GetParam("global_count");
		$StrDuplicated=$StrAccept=$StrNeedMore=$StrReject="";
		$CountAccept=$CountNeedMore=$CountReject=0;
		for ($ii=0;$ii<$max;$ii++) {
			$IdMember=GetParam("IdMember_".$ii);
		   // todo change what need to be change to answer in member default language
		   $defLanguage=0;
			switch (GetParam("action_".$ii)) {
				case "accept" :
				   $m = LoadRow("SELECT * FROM members WHERE id=" . $IdMember);
				   $str = "UPDATE members SET Status='Active' WHERE (Status='Pending' OR Status='NeedMore' OR Status='CompletedPending') AND id=" . $IdMember;
				   $qry = sql_query($str);

				   $Email = AdminReadCrypted($m->Email);
				   $subj = wwinlang("SignupSubjAccepted",$defaultlanguage, "http://".$_SYSHCVOL['SiteName']);
				   $loginurl = "http://".$_SYSHCVOL['SiteName'] .$_SYSHCVOL['MainDir']."/login.php?&Username=" . $m->Username;
				   $text = wwinlang("SignupYouHaveBeenAccepted",$defaultlanguage, $m->Username, "http://".$_SYSHCVOL['SiteName'], $loginurl);
				   bw_mail($Email, $subj, $text, "", $_SYSHCVOL['AccepterSenderMail'], $defLanguage, "yes", "", "");
				   $StrAccept=$StrAccept.$m->Username." ";
				   $CountAccept++;
				   break;
				case "duplicated" :
				   $m = LoadRow("select * from members where id=" . $IdMember);
				   $str = "update members set Status='DuplicateSigned' where (Status='Pending' or Status='NeedMore' or Status='CompletedPending' or Status='MailToConfirm') and id=" . $IdMember;
				   $qry = sql_query($str);
				   $StrDuplicated=$StrDuplicated.$m->Username." ";

//				   $CountReject++;

				   break;
				case "reject" :
				   $m = LoadRow("select * from members where id=" . $IdMember);
				   $str = "update members set Status='Rejected' where (Status='Pending' or Status='NeedMore' or Status='CompletedPending' or Status='MailToConfirm') and id=" . $IdMember;
				   $qry = sql_query($str);

				   $Email = AdminReadCrypted($m->Email);
				   $subj = wwinlang("SignupSubjRejected",$defaultlanguage,$_SYSHCVOL['SiteName']);
				   $text = wwinlang("SignupYouHaveBeenRejected",$defaultlanguage, $m->Username,$_SYSHCVOL['SiteName']);
				   bw_mail($Email,$subj, $text, "", $_SYSHCVOL['AccepterSenderMail'],0, "yes", "", "");
					 
					 $StrReject=$StrReject.$m->Username." ";
				   $CountReject++;

				   break;
				case "needmore" :
				   $m = LoadRow("select * from members where id=" . $IdMember);
				   $needmoretext=GetStrParam("needmoretext_".$ii);
				   $urltoreply = "http://".$_SYSHCVOL['SiteName'] .$_SYSHCVOL['MainDir']. "login.php?Username=".$m->Username;
				   $m = LoadRow("select * from members where id=" . $IdMember);
				   $str = "update members set Status='NeedMore' where (Status='Pending' or Status='Active' or Status='CompletedPending' or Status='MailToConfirm') and id=" . $IdMember;
				   $qry = sql_query($str);
				   $Email = AdminReadCrypted($m->Email);
				   $subj = wwinlang("SignupNeedmoreTitle",$defaultlanguage,$_SYSHCVOL['SiteName']);
				   $text = wwinlang("SignupNeedMoreText",$defaultlanguage, $m->Username,$_SYSHCVOL['SiteName'],$needmoretext,$urltoreply);
				   bw_mail($Email,$subj, $text, "", $_SYSHCVOL['AccepterSenderMail'],0, "yes", "", "");
				   $StrNeedMore=$StrNeedMore.$m->Username." ";
				   $CountNeedMore++;
		   	  	   break;
			}
		} // end of for
		if ($CountAccept>0) {
		   $StrLog=$StrLog."(".$CountAccepted." accepted) ".$StrAccept;
		}
		if ($CountNeedMore>0) {
		   if ($StrLog!="") $StrLog.="<br>\n";
		   $StrLog=$StrLog."(".$CountNeedMore." need more) ".$StrNeedMore;
		}
		if ($CountReject>0) {
		   if ($StrLog!="") $StrLog.="<br>\n";
		   $StrLog=$StrLog."(".$CountReject." rejected) ".$StrReject;
		}
		if ($StrDuplicated!="") {
		   if ($StrLog!="") $StrLog.="<br>\n";
		   $StrLog=$StrLog." the following have been marked as duplicated :".$StrDuplicated." ";
		}
		$lasaction=$Strlog;
		LogStr($StrLog,"accepting");
		break;
	case "accept" :
		$m = LoadRow("select * from members where id=" . $IdMember);
		// todo change what need to be change to answer in member default language
		$defLanguage=0;
		$lastaction = "accepting " . $m->Username;
		$str = "update members set Status='Active' where (Status='Pending' or Status='NeedMore' or Status='CompletedPending') and id=" . $IdMember;
		$qry = sql_query($str);

		$Email = AdminReadCrypted($m->Email);
		// todo change what need to be change to answer in member default language
		$subj = ww("SignupSubjAccepted", "http://".$_SYSHCVOL['SiteName']);
		$loginurl = "http://".$_SYSHCVOL['SiteName'] .$_SYSHCVOL['MainDir']."/login.php?&Username=" . $m->Username;
		$text = ww("SignupYouHaveBeenAccepted", $m->Username, "http://".$_SYSHCVOL['SiteName'], $loginurl);
		bw_mail($Email, $subj, $text, "", $_SYSHCVOL['AccepterSenderMail'], $defLanguage, "yes", "", "");

		break;
	case "reject" :
		$m = LoadRow("select * from members where id=" . $IdMember);
		// todo change what need to be change to answer in member default language
		$defLanguage=0;
		$lastaction = "rejecting " . $m->Username;
		$str = "update members set Status='Rejected' where (Status='Pending' or Status='NeedMore' or Status='CompletedPending') and id=" . $IdMember;
		$qry = sql_query($str);

		$Email = AdminReadCrypted($m->Email);
		$subj = ww("SignupSubjRejected",$_SYSHCVOL['SiteName']);
		$text = ww("SignupYouHaveBeenRejected", $m->Username,$_SYSHCVOL['SiteName']);
//		echo "$subj<br>$text<br> sent to $Email<br> from ".$_SYSHCVOL['AccepterSenderMail'];
//		bw_mail($Email,$subj,"text as test   ", "", $_SYSHCVOL['TestMail'], 0, "yes", "", "");
		bw_mail($Email,$subj, $text, "", $_SYSHCVOL['AccepterSenderMail'],0, "yes", "", "");

		break;
	case "needmore" :
		$m = LoadRow("select * from members where id=" . $IdMember);
		$lastaction = "setting profile of  " . $m->Username . " from " . $m->Status . " to NeedMore";

		$str = "update members set Status='NeedMore' where (Status='Pending' or Status='Active' or Status='CompletedPending') and id=" . $IdMember;
		$qry = sql_query($str);
		// to do manage the need more
		break;

	case "ShowOneMember" :
		$RestrictToIdMember = IdMember(GetStrParam("cid", 0));
		break;
}

UpdateVolunteer_Board("Accepters_board") ; // Test if the accepter boards neesd to be update and update it if so

$Status=GetStrParam("Status","Pending") ;
$TData = loaddata($Status, $RestrictToIdMember,GetParam("IdEmail",0));
DisplayAdminAccepter($TData); // call the layout
?>
