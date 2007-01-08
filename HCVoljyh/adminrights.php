<?php
include "lib/dbaccess.php" ;
require_once "layout/error.php" ;
require_once "layout/adminrights.php" ;

if (!isset($title)) $title="Admin Rights" ;
if (!isset($thetable)) $thetable="rights" ;
if (!isset($rightneeded)) $rightneeded="Rights" ;
if (!isset($IdItem)) $IdItem="IdRight" ;

  $username=GetParam("username") ;
  $Name=GetParam("Name") ;
	

  $RightLevel=HasRight($rightneeded); // Check the rights
  if ($RightLevel<1) {  
    echo "This Need the sufficient <b>$rightneeded</b> rights<br>" ;
	  exit(0) ;
  }
	
  $scope=RightScope($rightneeded) ;
	
	$lastaction="" ;
  switch(GetParam("action")) {
	  case "logout" :
		  Logout("main.php") ;
			exit(0) ;
			break ;
	  case "add" :
      if (HasRight($rightneeded,$Name)<=0)  {  
        echo "You miss $rightneeded on <b>",$Name,"</b> for this" ;
	      exit(0) ;
      }
			$str="select id from ".$thetable." where Name='".$Name."'" ;
			echo "str=",$str,"<br>" ;
		  $rprevious=LoadRow($str) ; 
			$str="insert into ".$thetable."svolunteers(Comment,Scope,Level,IdMember,created,".$IdItem.") values('".addslashes(GetParam("Comment"))."','".addslashes(GetParam("Scope"))."',".GetParam("Level").",".IdMember($username).",now(),".$rprevious->id.")" ; 
	    $qry=sql_query($str) ;
			$lastaction="Adding ".$thetable." <i>".$Name."</i> for <b>".$username."</b>" ;
			LogStr($lastaction,"Admin".$thetable."") ;
			break ;
	  case "update" :
      $IdItemVolunteer=GetParam("IdItemVolunteer") ;
			$rbefore=LoadRow("select * from ".$thetable."volunteers where id=".$IdItemVolunteer) ;
		  $rCheck=LoadRow("select ".$thetable.".Name as Name from ".$thetable.",".$thetable."volunteers where ".$thetable."volunteers.".$IdItem."=".$thetable.".id and ".$thetable."volunteers.id=".$IdItemVolunteer) ; 
      if ((HasRight($rightneeded,$Name)<=0) or ($rCheck->Name!=$Name))  {  
        echo "You miss Rights on <b>",$Name,"</b> for this" ;
	      exit(0) ;
      }
			$str="update ".$thetable."volunteers set Comment='".addslashes(GetParam("Comment"))."',Scope='".addslashes(GetParam("Scope"))."',Level=".GetParam("Level")." where id=$IdItemVolunteer" ; 
	    $qry=sql_query($str) ;
			$lastaction="Updating ".$thetable." <i>".$Name."</i> for <b>".fUsername($rbefore->IdMember)."</b>" ;
			LogStr($lastaction,"Admin".$thetable."") ;
			break ;
	  case "del" :
      $IdItemVolunteer=GetParam("IdItemVolunteer") ;
			$rbefore=LoadRow("select * from ".$thetable."volunteers where id=".$IdItemVolunteer) ;
		  $rCheck=LoadRow("select ".$thetable.".Name as Name from ".$thetable.",".$thetable."volunteers where ".$thetable."volunteers.".$IdItem."=".$thetable.".id and ".$thetable."volunteers.id=".$IdItemVolunteer) ; 
      if ((HasRight($rightneeded,$Name)<10) or ($rCheck->Name!=$Name))  {  
        echo "You miss Rights on <b>",$Name,"</b> for this" ;
	      exit(0) ;
      }
			$str="delete from  ".$thetable."volunteers  where id=$IdItemVolunteer" ; 
	    $qry=sql_query($str) ;
			$lastaction="Deleting ".$thetable." <i>".$Name."</i> for <b>".fUsername($rbefore->IdMember)."</b>" ;
			LogStr($lastaction,"Admin".$thetable."") ;
			break ;
	}
	
	$TDatas=array() ;
	$TDatasVol=array() ;
	
	
	
// Load the values for this member list

  $str="select 0" ;
	if (($username!="") or ($Name!="")) { // if at least one parameter is select try to load corresponding rights
    $str="select ".$thetable."volunteers.*,".$thetable.".Name as Name,Username from ".$thetable."volunteers,".$thetable.",members where members.id=".$thetable."volunteers.IdMember and ".$thetable.".id=".$thetable."volunteers.".$IdItem."" ;

// add username filter if any
	  if ($username!="") {
		  $rwho=LoadRow("select id from members where username='".$username."'") ;
			if (isset($rwho->id)) {
			  $cid=$rwho->id ;
			} 
			else {
			  $cid=0 ;
			}
		  $str.=" and ".$thetable."volunteers.IdMember=".$cid ; 
	  }

// Add Name filter if any
	  if ($Name!="") {
		  $rprevious=LoadRow("select id,Description from ".$thetable." where Name='".$Name."'") ;
			if (isset($rprevious->id)) {
			  $iid=$rprevious->id ;
			} 
			else {
			  $iid=0 ;
			}
		  $str.=" and ".$IdItem."=".$iid ; 
		}
	  $qry=sql_query($str." group by members.id") ;
//		echo "str=$str","<br>" ;
	  while ($rr=mysql_fetch_object($qry)) {
	    array_push($TDatasVol,$rr) ;
	  } 
	}
// end of load list

// Load the right list
	$str="select * from ".$thetable." order by Name asc" ;
	$qry=sql_query($str) ;
	while ($rr=mysql_fetch_object($qry)) {
	  if (!HasRight($rightneeded,$rr->Name)) continue ; // Skip not allowed rights in scope of $rightneeded
		if ($username!="") {
	    if (HasRight($rr->Name,"",$rwho->id)) continue ; // Skip already given rights if the user is named
		}
	  array_push($TDatas,$rr) ;
	} 
// end of Load the right list
	
	
	
  DisplayAdminView($username,$Name,$rprevious->Description,$TDatas,$TDatasVol,$rprevious,$lastaction,$scope) ; // call the layout
	
?>