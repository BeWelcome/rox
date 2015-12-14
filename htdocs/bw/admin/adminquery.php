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
require_once "lib/FunctionsLogin.php";
require_once "layout/error.php";
require_once "layout/adminquery.php";
$IdMember = GetParam("cid");

$countmatch = 0;

//------------------------------------------------------------------------------

// MustLogIn(); // need to be log

$RightLevel = HasRight('SqlForVolunteers'); // Check the rights

//if ($RightLevel < 1) {
//	echo "This Need the sufficient <b>SqlForVolunteers</b> rights<br>";
//	exit (0);
//}


$IdQueryScope = RightScope('SqlForVolunteers');

$membergrouplist="" ; // receive the list of groups the member belongs to
$qry=sql_query("select IdGroup from membersgroups where Status='In' and IdMember=".$_SESSION["IdMember"]) ;
while ($rr=mysql_fetch_object($qry)) {
	if ($membergrouplist!="") {
		$membergrouplist.="," ;
	}
	$membergrouplist=$membergrouplist.$rr->IdGroup ;

}
$TList = array ();
$table="sqlforvolunteers" ;

if ($IdQueryScope=="\"All\"") {
			 $swhere="" ;
}
else {
	/* Group option Disabled -because this is not a good one (better write real features)
	$table="sqlforvolunteers,sqlforgroupsmembers" ;
	$sList=str_replace("\"","",$IdQueryScope) ;
		  $sList=str_replace("'","",$sList) ;
		  $sList=str_replace(";",",",$sList) ;
	$swhere=" where ( (sqlforvolunteers.id in (".$sList.")) or (sqlforvolunteers.id=sqlforgroupsmembers.IdQuery and sqlforgroupsmembers.IdGroup in (".$membergrouplist.")))" ;
	*/
	$table="sqlforvolunteers" ;
	$sList=str_replace("\"","",$IdQueryScope) ;
	$sList=str_replace("'","",$sList) ;
	$sList=str_replace(";",",",$sList) ;
    $queries = array_filter(explode(',', $sList), 'strlen');
	$swhere=" where  (sqlforvolunteers.id in ('" . implode("','", $queries) . "')) " ;
}



$ss="select sqlforvolunteers.* from ".$table." " ;
$ss=
$ss=$ss.$swhere."  group by sqlforvolunteers.id order by sqlforvolunteers.id" ;
//		echo "\$ss=",$ss,"<br>\n" ; ;
$qry=sql_query($ss) ;
while ($rr = mysql_fetch_object($qry)) {
	 array_push($TList, $rr);
}



$lastaction = "";
switch (GetParam("action")) {
	case "See Users" :
		$IdQuery=(int)GetParam("IdQuery",0) ;
		$rrQuery=LoadRow("select * from sqlforvolunteers where id=".$IdQuery) ;
//		print_r($rrQuery) ;
		$ss="select rightsvolunteers.*,rights.Name as RightName,members.Username,members.Status as MemberStatus from rightsvolunteers,rights,members where rights.id=rightsvolunteers.IdRight and rightsvolunteers.Level>=1 and rights.Name='SqlForVolunteers' and (Scope like '%\"".$IdQuery."\"%' or Scope like '%\"All\"%') and members.id=rightsvolunteers.IdMember" ;
//		echo "ss=",$ss ;
		$TResult=array() ; 

		$qry=sql_query($ss) ;
		while ($rr=mysql_fetch_object($qry)) {
		   array_push($TResult, $rr);
		}
		
		$ss="select groups.Name,groups.id as IdGroup from groups,sqlforgroupsmembers where sqlforgroupsmembers.IdGroup=groups.id and sqlforgroupsmembers.IdQuery=".$IdQuery ;
		$TAllowedGroups=array() ; 

		$qry=sql_query($ss) ;
		while ($rr=mysql_fetch_object($qry)) {
		   array_push($TAllowedGroups, $rr);
		}
		
 	    DisplayUsers($rrQuery,$TResult,$TAllowedGroups) ;
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
				$TheRight=LoadRow("select * from rights where Name='SqlForVolunteers'") ;
		   	 	echo "creating right SqlForVolunteers for ".$Username,"<br>" ;
				$ss="insert into rightsvolunteers(IdMember,IdRight,Level,Comment,created) values(".$rUser->id.",".$TheRight->id.",1,'Granted via adminquery interface',now())" ;
				LogStr("Adding rights <i>SqlForVolunteers</i> for <b>$rUser->Username</b>","Adminrights") ;
				sql_query($ss) ;
				
			 }
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
		$ss="select rightsvolunteers.*,rights.Name as RightName,members.Username,members.Status as MemberStatus from rightsvolunteers,rights,members where rights.id=rightsvolunteers.IdRight and rightsvolunteers.Level>=1 and rights.Name='SqlForVolunteers' and (Scope like '%\"".$IdQuery."\"%' or Scope like '%\"All\"%') and members.id=rightsvolunteers.IdMember" ;
		$TResult=array() ; 

		$qry=sql_query($ss) ;
		while ($rr=mysql_fetch_object($qry)) {
		   array_push($TResult, $rr);
		}
		
 	    DisplayUsers($rrQuery,$TResult,NULL,$Message) ;
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
		
 	    DisplayUsers($rrQuery,$TResult,NULL,$Message) ;
		break ;
	
	case "execute" :
		$IdQuery=(int)GetParam("IdQuery",0) ;
		$rrQuery=LoadRow("select * from sqlforvolunteers where id=".$IdQuery) ;
		
		if (!isset($rrQuery->id)) {
		   DisplayMyResults(array(),array(),array(),$rrQuery,"Sorry your query has failed #IdQuery=<b>".$IdQuery."</b>",$TList) ;
		   break ;
		}
		
		
		$IsQueryAllowedInGroup=LoadRow("select count(*) as cnt  from sqlforgroupsmembers where IdGroup in (".$membergrouplist.") and IdQuery=".$IdQuery) ;
		if ((!HasRight('SqlForVolunteers','"'.$IdQuery.'"')) and ($IsQueryAllowedInGroup->cnt==0) ) {
		   DisplayMyResults(array(),array(),array(),$rrQuery,"Sorry you miss right scope for query <b>".$rrQuery->Name."</b>",$TList) ;
		   LogStr("Trying to use a not allowed query (".$rrQuery->Name.")","adminquery") ;
		   break ;
		}
		
		$_TResult=array() ;
		$_TTitle=array() ;
		$_TTsqry=array() ;
		$_rrQuery=array() ;
		$tQuery=explode(";",$rrQuery->Query) ;
		for ($jj=0;$jj<count($tQuery);$jj++) {
			$sQry=ltrim($tQuery[$jj]) ;
			if (empty($sQry)) continue ;
			$Message="" ;
			$TResult=array() ;
			$TTitle=array() ;
			$Param1=mysql_real_escape_string(stripslashes(GetStrParam("param1",""))) ;
			$Param2=mysql_real_escape_string(stripslashes(GetStrParam("param2",""))) ;
			$Param3=mysql_real_escape_string(stripslashes(GetStrParam("param3",""))) ;
			$Param4=mysql_real_escape_string(stripslashes(GetStrParam("param4",""))) ;
			$Param5=mysql_real_escape_string(stripslashes(GetStrParam("param5",""))) ;

			 $sQuery=$sQry ;

			// echo " \$rrQuery->Query=",$rrQuery->Query," \$Param1=[$Param1]<br>"  ;
			if ((!empty($Param1)) and (!empty($Param2))) {
				if (stripos ($sQry,'%s')!==0) {
					$sQuery=sprintf($sQry,$Param1,$Param2) ;
				}
			}
			else if (!empty($Param1)) {
				if (stripos($sQry,'%s')!==0) {
					$sQuery=sprintf($sQry,$Param1) ;
				} 
			}

			$sQuery=str_ireplace( "\$P1",$Param1,$sQuery) ;
			$sQuery=str_ireplace( "\$P2",$Param2,$sQuery) ;
			$sQuery=str_ireplace( "\$P3",$Param3,$sQuery) ;
			$sQuery=str_ireplace( "\$P4",$Param4,$sQuery) ;
			$sQuery=str_ireplace( "\$P5",$Param5,$sQuery) ;
			$sQuery=str_ireplace( "\$IdMember",$_SESSION["IdMember"],$sQuery) ;
			$sQuery=str_ireplace( "\$Username",$_SESSION["Username"],$sQuery) ;
	
			if ($rrQuery->LogMe=="True") {
				LogStr("Doing query [".$sQuery."]","adminquery") ;
			}
		
			// echo "\$sQuery=",stripslashes($sQuery)," \$Param1=[$Param1]<br>\n"  ;
			$_TTsqry[]=$sQuery ;
		
			$qry=sql_query(stripslashes($sQuery)) ;
			if (!$qry) {
			die ( "Sorry your query [".$sQuery."] has failed #IdQuery=<b>".$IdQuery."</b>") ;
		   DisplayMyResults(array(),array(),array(),null,"Sorry your query [".$sQuery."] has failed #IdQuery=<b>".$IdQuery."</b>",$TList) ;
		   break ;
			}


			if ((stripos ($sQuery,"--")===0) or (stripos ($sQuery,"//")===0)) { // Proceed with comments
			 	$_TResult[]=$sQuery ;
			 	$_TTitle[]=$sQuery ;
			} 
			elseif ((stripos ($sQuery,"delete")===0) or (stripos ($sQuery,"update")===0) or (stripos ($sQuery,"truncate")===0) or (stripos ($sQuery,"replace")===0) or 								(stripos ($sQuery,"insert")===0) ){
				if (!$qry) {
					$Message=$sQuery."<br><b>".mysql_error()."</b>" ;
				}
				else {
		   $AffectedRows=mysql_affected_rows() ;
		   $Message=$AffectedRows." affected rows<br />" ;
		   $iCount=0 ;
		   LogStr($AffectedRows." affected rows by query IdQuery=#".$IdQuery." /#".$jj,"adminquery") ;
			 
			 $TTitle[]="Affected rows" ;
			 $TResult[]=sprintf("%d",$AffectedRows) ;
			 
			 $_TResult[]=$TResult ;
			 $_TTitle[]=$TTitle ;
			}
			}
			else {
		   $AffectedRows=0 ;
		   $iCount=mysql_num_fields($qry) ;
		
		   for ($ii=0;$ii<$iCount;$ii++) {
					$TTitle[$ii]=mysql_field_name($qry,$ii) ;
		   }
		
		   while ($rr=mysql_fetch_array($qry)) {
			 	array_push($TResult, $rr);
		   }
			 $_TResult[]=$TResult ;
			 $_TTitle[]=$TTitle ;
			}
		}
		DisplayMyResults($_TResult,$_TTitle,$_TTsqry,$rrQuery,$Message,$TList) ;
		
		break;

	default:
		DisplayMyQueryList($TList) ;
		break ;

}

?>
