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
require_once "layout/error.php";
require_once "layout/adminmassmails.php";

$RightLevel = HasRight('MassMail'); // Check the rights
if ($RightLevel < 1) {
	echo "For this you need the <b>MassMail</b> rights<br>";
	exit (0);
}

// Adding data initialization to avoid warnings
$Name="" ;
$countnews=0 ;
$count=0 ;
$countnonews=0 ;
$ToApprove=Array() ;
$BroadCast_Title_="" ;
$BroadCast_Body_="" ;
$Description="" ;
$where="" ;

$IdBroadCast=GetParam("IdBroadCast",0) ;
$greetings=GetParam("greetings",0) ;
/*
This is the right wich allow to send MassMail to several members using the adminmassmails.php page

It require Level 1 to check the effect of a massmail (without sending it)
It require Level 5 to send it for true

Scope (todo) will allow specific massmails
*/

$TData =array() ;

switch (GetParam("action")) {

	case "ShowPendingTrigs" :
	 	 if (HasRight("MassMail","Send")) { // if has right to trig
	 	 	$str = "select broadcastmessages.*,broadcast.Name,count(*) as cnt from broadcastmessages,broadcast where broadcast.id=broadcastmessages.IdBroadcast and broadcastmessages.Status='ToApprove' group by broadcast.id order by broadcast.created asc";
	 		$qry = sql_query($str);
	 		while ($rr = mysql_fetch_object($qry)) { // building the possible pending mails to trigger
				  array_push($ToApprove, $rr);
		  	}
		}

		DisplayAdminMassToApprove($ToApprove) ;
		exit(0) ;
		break ;

	case "Trigger" :
   	 if (HasRight("MassMail","Send")) { // if has right to trig
		 	$str="update broadcastmessages set Status='ToSend' where Status='ToApprove' and IdBroadcast=".$IdBroadCast ;
		 	sql_query($str) ;
		 	$count=mysql_affected_rows() ;
		 	if ($count>0) {
			   LogStr("Has Triggered ".$count." messages <b>".GetStrParam("Name")."</b>","adminmassmails") ;
		 	}
		 	echo "Triggering message ",GetStrParam("Name")," ",$count," triggered<br>" ;
		 	$str="update broadcast set Status='Triggered' where Status='Created' and id=".$IdBroadCast ; // mark the message has sent
		 	sql_query($str) ;
		 }
		 break ;

	case "enqueue" :
	case "test" :
		 $where=" where members.IdCity=cities.id " ;
		 $table="members,cities" ;
		 if (GetParam("IdCountry",0)!=0) {
		 		$where=$where." and cities.IdCountry=".GetParam("IdCountry",0) ;
		 }
		 if (GetStrParam("Usernames","")!=="") { // the list can be for one or several usernames
		 		$TUsernames=explode(";",GetStrParam("Usernames")) ;
				for ($ii=0;$ii<count($TUsernames);$ii++) {
						$Username=$TUsernames[$ii] ;
						if ($ii==0) {
		 					 $where=$where." and (members.id=".IdMember($Username) ;
						}
						else {
		 					 $where=$where." or members.id=".IdMember($Username) ;
						}
				}
 				$where=$where.") " ;
		 } // end if they are one or several usernames
		 
		 if (GetStrParam("MemberStatus","")!=="") {
		 		$where=$where." and members.Status='".GetStrParam("MemberStatus","")."'" ;
		 }
		 if (GetParam("IdGroup",0)!=0) {
		 		$table.=",membersgroups" ;
		 		$where=$where." and members.id=membersgroups.IdMember and membersgroups.Status='In' and membersgroups.IdGroup=".GetParam("IdGroup") ;
		 }
		 
		 // If the option use the OpenQuery is activated and the user has proper right
		 if (IsAdmin() and (GetStrParam("UseOpenQuery","")=="on") and (GetStrParam("query","")!="")) {
		 		$where=stripslashes(GetStrParam("query","")) ;
				echo "<br />USING OPEN QUERY ! " ;
		 }
		 $str="select members.id as id,Username,cities.IdCountry,members.Status as Status from ".$table.$where ;
		 
		 if (IsAdmin()) {
		 		echo "<table><tr><td bgcolor=yellow>$str</td></tr></table>\n" ;
		 }
	 	 $qry = sql_query($str);

		 reset($TData) ;		 
		 $count=0 ;
		 $countnonews=0 ;
	 	 while ($rr = mysql_fetch_object($qry)) { // building the list of members who can receive
		 			 if (GetPreference("PreferenceAcceptNewsByMail",$rr->id)!='Yes') {  // Skip members who have choose not to have news
		 			 				$countnonews++ ;
		 					 		continue ;
					 }
					 array_push($TData, $rr);
					 if (HasRight('MassMail',"enqueue")) { // if effective enqueue action
					 		if ((GetStrParam("action")=="enqueue") and (GetStrParam("enqueuetick","")=="on")) {
										$str="replace into broadcastmessages(IdBroadcast,IdReceiver,IdEnqueuer,Status) values(".$IdBroadCast.",".$rr->id.",".$_SESSION["IdMember"].",'ToApprove')" ;
										sql_query($str) ;
					 			  $count++ ;
					 		}
					 } // end if (HasRight('MassMail',"enqueue"))
	 	 }
		 if ($count>0) {
					LogStr("Has enqueued ".$count." message <b>".GetStrParam("Name")."</b>","adminmassmails") ;
		 }
		
  case "prepareenque" :
	 $str="select * from broadcast where id=".$IdBroadCast ;
	 $rBroadCast=LoadRow($str) ;
	 $TGroupList = array ();
	 $str = "select id,Name from groups order by Name";
	 $qry = sql_query($str);
	 while ($rr = mysql_fetch_object($qry)) { // building the possible  groups
			array_push($TGroupList, $rr);
	 }

	 $TCountries = array ();
	 $str = "select id,Name from countries order by Name";
	 $qry = sql_query($str);
	 while ($rr = mysql_fetch_object($qry)) { // building the possible countries
			array_push($TCountries, $rr);
	 }


	 DisplayAdminMassprepareenque($rBroadCast,$TGroupList,$TCountries,$TData,$count,$countnonews,$where) ;
	 exit(0) ;

	case "edit" :
	case "createbroadcast" :
	
	  $count=0 ;
	  if (GetStrParam("Name","")!="") { // if they are parameters for a create or an update
			 $Name=GetStrParam("Name") ;
			 $IdBroadCast = GetParam("IdBroadCast",0);
			 if ($IdBroadCast == 0) { // case insert
			 		$rr=LoadRow("select * from broadcast where Name='".GetStrParam("Name")."'") ;
			 		if (!empty($rr->id)) {
		   			 echo "broadcast ",GetStrParam("Name"), " allready exist" ;
		   			 break ;
					}
					$str = "insert into broadcast(Type,Name,created,Status,IdCreator) values('" . GetStrParam("Type","Normal") . "','". GetStrParam("Name") . "',Now(),'Created'," . $_SESSION["IdMember"].")";
					sql_query($str);
					$IdBroadCast = mysql_insert_id();
					$str = "insert into words(code,ShortCode,IdLanguage,Sentence,updated,IdMember,Description) values('BroadCast_Title_" . GetStrParam("Name"). "','en',0,'" . mysql_real_escape_string(GetStrParam("BroadCast_Title_")) . "',now(),".$_SESSION['IdMember'].",'".mysql_real_escape_string(GetStrParam("Description"))."')";
					sql_query($str);
					$str = "insert into words(code,ShortCode,IdLanguage,Sentence,updated,IdMember,Description) values('BroadCast_Body_" . GetStrParam("Name"). "','en',0,'" . mysql_real_escape_string(GetStrParam("BroadCast_Body_")) . "',now(),".$_SESSION['IdMember'].",'".mysql_real_escape_string(GetStrParam("Description"))."')";
					sql_query($str);
					LogStr("Creating massmail <b>".GetStrParam("Name")."</b>","adminmassmails") ;
			} else { // case update
						$str = "update words set Sentence='".GetStrParam("BroadCast_Title_")."',updated=now(),IdMember=".$_SESSION['IdMember'].",Description='".GetStrParam("Description")."' where code='BroadCast_Title_" . GetStrParam("Name"). "' and IdLanguage=0";
						sql_query($str);
						$str = "update words set Sentence='".GetStrParam("BroadCast_Body_")."',updated=now(),IdMember=".$_SESSION['IdMember'].",Description='".GetStrParam("Description")."' where code='BroadCast_Body_" . GetStrParam("Name"). "' and IdLanguage=0";
						sql_query($str);
						LogStr("Updating massmail <b>".GetStrParam("Name")."</b>","adminmassmails") ;
			}
		} // end if they are parameters for a create or an update

  		if ($IdBroadCast!=0) {
	 		 $rr=LoadRow("select * from broadcast where id=".$IdBroadCast) ;
	 		 if (isset($rr->Name)) {
			 		$Name=$rr->Name ;
			 }
			 else {
			 		$Name="" ;
			 }
					 
			 $BroadCast_Title_=wwInLang("BroadCast_Title_".$Name,0) ;
			 $BroadCast_Body_=wwInLang("BroadCast_Body_".$Name,0) ;
			 $rr=LoadRow("select * from words where code='BroadCast_Title_".$Name."' and IdLanguage=0") ;
			 if (isset($rr->Description)) {
			 		$Description=$rr->Description ;
			 }
			 else {
			 		$Description="" ;
			 }
		}
		DisplayFormCreateBroadcast($IdBroadCast,$Name,$BroadCast_Title_,$BroadCast_Body_,$Description,$count,$countnews,$ToApprove) ; // Display the form
		
		exit (0);
		break;
}

$TData = array ();

//$str = "select logs.*,Username from BW_ARCH.logs,members where members.id=logs.IdMember " . $where . "  order by created desc limit 0," . $limit;
$str = "select * from broadcast" ;
if (!empty($IdBroadCast)) {
	 $str = "select * from broadcast where id=".$IdBroadCast ;
	 
}
$qry = sql_query($str);
while ($rr = mysql_fetch_object($qry)) {
		array_push($TData, $rr);
}

DisplayAdminMassMailsList($TData);
?>
