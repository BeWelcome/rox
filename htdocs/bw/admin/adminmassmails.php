<?php
require_once "../lib/init.php";
require_once "../layout/error.php";
require_once "../layout/adminmassmails.php";

$RightLevel = HasRight('MassMail'); // Check the rights
if ($RightLevel < 1) {
	echo "This Need the sufficient <b>MassMail</b> rights<br>";
	exit (0);
}


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
	 	 $ToApprove=Array() ;
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
			   LogStr("Has Triggered ".$count." messages <b>".GetStrParam(Name)."</b>","adminmassmails") ;
		 	}
		 	echo "Triggering message ",GetStrParam(Name)," ",$count," triggered<br>" ;
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
		 if (GetStrParam("Username","")!=="") {
		 		$where=$where." and members.id=".IdMember(GetStrParam("Username","")) ;
		 }
		 if (GetStrParam("MemberStatus","")!=="") {
		 		$where=$where." and members.Status='".GetStrParam("MemberStatus","")."'" ;
		 }
		 if (GetParam("IdGroup",0)!=0) {
		 		$table.=",membersgroups" ;
		 		$where=$where." and members.id=membersgroups.IdMember and membersgroups.Status='In' and membersgroups.IdGroup=".GetParam("IdGroup") ;
		 }
		 $str="select members.id as id,Username,cities.IdCountry,members.Status as Status from ".$table.$where ;
		 
		 if (IsAdmin()) echo "$str<br>\n" ;
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
					LogStr("Has enqueued ".$count." message <b>".GetStrParam(Name)."</b>","adminmassmails") ;
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


	 DisplayAdminMassprepareenque($rBroadCast,$TGroupList,$TCountries,$TData,$count,$countnonews) ;
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
					$str = "insert into words(code,ShortCode,IdLanguage,Sentence,updated,IdMember,Description) values('BroadCast_Title_" . GetStrParam("Name"). "','en',0,'" . addslashes(GetStrParam("BroadCast_Title_")) . "',now(),".$_SESSION['IdMember'].",'".addslashes(GetStrParam("Description"))."')";
					sql_query($str);
					$str = "insert into words(code,ShortCode,IdLanguage,Sentence,updated,IdMember,Description) values('BroadCast_Body_" . GetStrParam("Name"). "','en',0,'" . addslashes(GetStrParam("BroadCast_Body_")) . "',now(),".$_SESSION['IdMember'].",'".addslashes(GetStrParam("Description"))."')";
					sql_query($str);
					LogStr("Creating massmail <b>".GetStrParam(Name)."</b>","adminmassmails") ;
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
	 		 $Name=$rr->Name ;
					 
			 $BroadCast_Title_=wwInLang("BroadCast_Title_".$Name,0) ;
			 $BroadCast_Body_=wwInLang("BroadCast_Body_".$Name,0) ;
			 $rr=LoadRow("select * from words where code='BroadCast_Title_".$Name."' and IdLanguage=0") ;
			 $Description=$rr->Description ;
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
