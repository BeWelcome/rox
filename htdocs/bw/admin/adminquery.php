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
require_once "../lib/FunctionsLogin.php";
require_once "../layout/error.php";
require_once "../layout/adminquery.php";
$IdMember = GetParam("cid");

$countmatch = 0;

$RightLevel = HasRight('SqlForVolunteers'); // Check the rights
if ($RightLevel < 1) {
	echo "This Need the suffcient <b>SqlForVolunteers</b> rights<br>";
	exit (0);
}

$GroupeScope = RightScope('SqlForVolunteers');

$lastaction = "";
switch (GetParam("action")) {
	case "logout" :
		Logout();
		exit (0);
		break;
	case "See Users" :
		$IdQuery=(int)GetParam("IdQuery",0) ;
		$rrQuery=LoadRow("select * from sqlforvolunteers where id=".$IdQuery) ;
//		print_r($rrQuery) ;
		$ss="select rightsvolunteers.*,rights.Name as RightName,members.Username,members.STatus as MemberStatus from rightsvolunteers,rights,members where rights.id=rightsvolunteers.IdRight and rightsvolunteers.Level>=1 and rights.Name='SqlForVolunteers' and (Scope like '%\"".$IdQuery."\"%' or Scope like '%\"All\"%') and members.id=rightsvolunteers.IdMember" ;
//		echo "ss=",$ss ;
		$TResult=array() ; 

		$qry=sql_query($ss) ;
		while ($rr=mysql_fetch_object($qry)) {
		   array_push($TResult, $rr);
		}
		
 	    DisplayUsers($rrQuery,$TResult) ;
		break ;
		
   case "grant query" :
		$Message="" ;
		$IdQuery=(int)GetParam("IdQuery",0) ;
		$Username=GetStrParam("Username","") ;
		if (HasRight('Rights','SqlForVolunteers') < 1) {
		   LogStr("Trying to grant a right without right to grant it","adminquery") ;
		   echo "This Need the sufficient scope <b>SqlForVolunteers</b> and right <b>Rights</b>";
		   exit (0);
		}
		
		$rUser=LoadRow("select * from members where Username='".$Username."'") ;
		if (empty($rUser->id)) {
		   $Message="No Such user ".$Username ;
		}
		else {
			 $ss="select rightsvolunteers.*,rightsvolunteers.id as IdRightForVol,rights.Name as RightName,members.Username,members.STatus as MemberStatus from rightsvolunteers,rights,members where rights.id=rightsvolunteers.IdRight and rightsvolunteers.Level>=1 and rights.Name='SqlForVolunteers' and members.id=rightsvolunteers.IdMember and members.Username='".$Username."'" ;
			 $rRight=LoadRow($ss) ;
			 if (!isset($rRight->Scope))  {
		   	 	$Message="You first need to grant ".$Username. " with right <b>SqlForVolunteers</b>" ;
			 }
			 else {
			 	  if ($rRight->Scope=="\"All\"") {
		   	 	  	 $Message=$Username. " Allready has full scope" ;
				  }
				  else {
				  	 if (stripos($rRight->Scope,'"'.$IdQuery.'"')!==false) {
		   	 	  	 	$Message=$Username. " Allready has right for this query" ;
					 } 
					 else {
				  	 	  if ($rRight->Scope=="") {
					 	  		$rRight->Scope='"'.$IdQuery.'"' ;
					 	  }
					 	  else {
					 	  		$rRight->Scope=$rRight->Scope.',"'.$IdQuery.'"' ;
					 	  }
		   				  LogStr("Granting right for query #".$IdQuery." to <b>".$Username."<b>","adminquery") ;
						  $ss="update rightsvolunteers set Scope='".$rRight->Scope."' where id=".$rRight->IdRightForVol ;
						  sql_query($ss) ;
						  $Message=" Query #".$IdQuery." granted to ".$Username ;
					 }  
				  }
			 }
		}
		
	
		// Reload the data
		$rrQuery=LoadRow("select * from sqlforvolunteers where id=".$IdQuery) ;
		$ss="select rightsvolunteers.*,rights.Name as RightName,members.Username,members.STatus as MemberStatus from rightsvolunteers,rights,members where rights.id=rightsvolunteers.IdRight and rightsvolunteers.Level>=1 and rights.Name='SqlForVolunteers' and (Scope like '%\"".$IdQuery."\"%' or Scope like '%\"All\"%') and members.id=rightsvolunteers.IdMember" ;
		$TResult=array() ; 

		$qry=sql_query($ss) ;
		while ($rr=mysql_fetch_object($qry)) {
		   array_push($TResult, $rr);
		}
		
 	    DisplayUsers($rrQuery,$TResult,$Message) ;
		break ;
	
   case "remove access" :
		$Message="" ;
		$IdQuery=(int)GetParam("IdQuery",0) ;
		$IdMember=(int)GetParam("IdMember",0) ;
		if (HasRight('Rights','SqlForVolunteers') < 1) {
		   LogStr("Trying to remove access for a query without right to grant it","adminquery") ;
		   echo "This Need the sufficient scope <b>SqlForVolunteers</b> and right <b>Rights</b>";
		   exit (0);
		}
		
		$ss="select rightsvolunteers.*,rightsvolunteers.id as IdRightForVol,rights.Name as RightName,members.Username,members.STatus as MemberStatus from rightsvolunteers,rights,members where rights.id=rightsvolunteers.IdRight and rightsvolunteers.Level>=1 and rights.Name='SqlForVolunteers' and members.id=rightsvolunteers.IdMember and members.id='".$IdMember."'" ;
		$rRight=LoadRow($ss) ;
		$Username=$rRight->Username ;
 	    if ($rRight->Scope=="\"All\"") {
			 $Message=$Username. " Allready has full scope (use admin right to do this)" ;
	    }
	  	else {
			 $rRight->Scope=str_replace('"'.$IdQuery.'",','',$rRight->Scope) ;
			 $rRight->Scope=str_replace(',"'.$IdQuery.'"','',$rRight->Scope) ;
			 $rRight->Scope=str_replace('"'.$IdQuery.'"','',$rRight->Scope) ;
			 LogStr("Removing right for query #".$IdQuery." to <b>".$Username."<b>","adminquery") ;
			 $ss="update rightsvolunteers set Scope='".$rRight->Scope."' where id=".$rRight->IdRightForVol ;
//			 echo "ss=",$ss ;
//			 sql_query($ss) ;
			 $Message=" Query #".$IdQuery." removed for ".$Username ;
		}
		
	
		// Reload the data
		$rrQuery=LoadRow("select * from sqlforvolunteers where id=".$IdQuery) ;
		$ss="select rightsvolunteers.*,rights.Name as RightName,members.Username,members.STatus as MemberStatus from rightsvolunteers,rights,members where rights.id=rightsvolunteers.IdRight and rightsvolunteers.Level>=1 and rights.Name='SqlForVolunteers' and (Scope like '%\"".$IdQuery."\"%' or Scope like '%\"All\"%') and members.id=rightsvolunteers.IdMember" ;
		$TResult=array() ; 

		$qry=sql_query($ss) ;
		while ($rr=mysql_fetch_object($qry)) {
		   array_push($TResult, $rr);
		}
		
 	    DisplayUsers($rrQuery,$TResult,$Message) ;
		break ;
	
	case "execute" :
		$IdQuery=(int)GetParam("IdQuery",0) ;
		$rrQuery=LoadRow("select * from sqlforvolunteers where id=".$IdQuery) ;
		
		if (!isset($rrQuery->id)) {
		   DisplayMyResults(array(),array(),$rrQuery,"Sorry your query has failed #IdQuery=<b>".$IdQuery."</b>") ;
		   break ;
		}
		
		$Message="" ;
		$TResult=array() ;
		$TTitle=array() ;
		if (!HasRight('SqlForVolunteers','"'.$IdQuery.'"')) {
		   DisplayMyResults(array(),array(),$rrQuery,"Sorry you miss right scope for query <b>".$rrQuery->Name."</b>") ;
		   LogStr("Trying to use a not allowed query (".$rrQuery->Name.")","adminquery") ;
		   break ;
		}
		$Param1=mysql_escape_string(stripslashes(GetStrParam("param1",""))) ;
		$Param2=mysql_escape_string(stripslashes(GetStrParam("param2",""))) ;
//		echo " \$rrQuery->Query=",$rrQuery->Query,"<br>"  ;
		$sQuery=sprintf($rrQuery->Query,$Param1,$Param2) ;
		if ($rrQuery->LogMe=="True") {
		   LogStr("Doing query [".$sQuery."]","adminquery") ;
		}
		
		echo "sQuery=",$sQuery," \$rrQuery->Query=",$rrQuery->Query,"<br>"  ;

		
		$qry=sql_query($sQuery) ;

		if (!qry) {
		   DisplayMyResults(array(),array(),"Sorry your query [".$sQuery."] has failed #IdQuery=<b>".$IdQuery."</b>") ;
		   break ;
		}


		if ((stripos ($sQuery,"delete")===0) or (stripos ($sQuery,"update")===0) or (stripos ($sQuery,"replace")===0) or (stripos ($sQuery,"insert")===0) ){
		   $AffectedRows=mysql_affected_rows($qry) ;
		   $Message=$AffectedRows." affected rows" ;
		   $iCount=0 ;
		   LogStr($AffectedRows." affected rows by query IdQuery=#".$IdQuery,"adminquery") ;
		}
		else {
		   $AffectedRows=0 ;
		   $iCount=mysql_num_fields($qry) ;
		}
		
		for ($ii=0;$ii<$iCount;$ii++) {
			$TTitle[$ii]=mysql_field_name($qry,$ii) ;
		}
		
		while ($rr=mysql_fetch_array($qry)) {
			 array_push($TResult, $rr);
		}
		
		DisplayMyResults($TResult,$TTitle,$rrQuery,$Message) ;
		
		break;

	default:
		$TList = array ();
		if ($GroupeScope=="\"All\"") {
			 $swhere="" ;
		}
		else {
		  $sList=str_replace("\"","",$GroupeScope) ;
		  $sList=str_replace("'","",$sList) ;
		  $sList=str_replace(";",",",$sList) ;
			$swhere=" where sqlforvolunteers.id in (".$sList.")" ;
		}
		$ss="select * from sqlforvolunteers ".$swhere." order by id" ;
//		echo "\$ss=",$ss,"<br>\n" ; ;
		$qry=sql_query($ss) ;
		while ($rr = mysql_fetch_object($qry)) {
			 array_push($TList, $rr);
		}
		DisplayMyQueryList($TList) ;
		break ;

}


// 			sql_query("update groups set NbChilds=(select count(*) from groupshierarchy where IdGroupParent=groups.id");
?>
