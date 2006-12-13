<?php
include "lib/dbaccess.php" ;
require_once "layout/Error.php" ;
require_once "layout/AdminRights.php" ;

  $username=GetParam("username") ;
  $rightname=GetParam("rightname") ;
	

  $RightLevel=HasRight('Rights'); // Check the rights
  if ($RightLevel<1) {  
    echo "This Need the suffcient <b>Accepter</b> rights<br>" ;
	  exit(0) ;
  }
	
  $scope=RightScope('Rights') ;
	
	$lastaction="" ;
  switch(GetParam("action")) {
	  case "logout" :
		  Logout("Main.php") ;
			exit(0) ;
			break ;
	  case "add" :
      if (HasRight("Rights",$rightname)<=0)  {  
        echo "You miss Rights on <b>",$rightname,"</b> for this" ;
	      exit(0) ;
      }
			$str="select id from rights where Name='".$rightname."'" ;
			echo "str=",$str,"<br>" ;
		  $rright=LoadRow($str) ; 
			$str="insert into rightsvolunteers(Comment,Scope,Level,IdMember,created,IdRight) values('".addslashes(GetParam("Comment"))."','".addslashes(GetParam("Scope"))."',".GetParam("Level").",".IdMember($username).",now(),".$rright->id.")" ; 
	    $qry=sql_query($str) ;
			$lastaction="Adding right <i>".$rightname."</i> for <b>".$username."</b>" ;
			LogStr($lastaction,"AdminRights") ;
			break ;
	  case "update" :
      $IdRightVolunteer=GetParam("IdRightVolunteer") ;
			$rbefore=LoadRow("select * from rightsvolunteers where id=".$IdRightVolunteer) ;
		  $rCheck=LoadRow("select rights.Name as Name from rights,rightsvolunteers where rightsvolunteers.IdRight=rights.id and rightsvolunteers.id=".$IdRightVolunteer) ; 
      if ((HasRight("Rights",$rightname)<=0) or ($rCheck->Name!=$rightname))  {  
        echo "You miss Rights on <b>",$rightname,"</b> for this" ;
	      exit(0) ;
      }
			$str="update rightsvolunteers set Comment='".addslashes(GetParam("Comment"))."',Scope='".addslashes(GetParam("Scope"))."',Level=".GetParam("Level")." where id=$IdRightVolunteer" ; 
	    $qry=sql_query($str) ;
			$lastaction="Updating right <i>".$rightname."</i> for <b>".fUsername($rbefore->IdMember)."</b>" ;
			LogStr($lastaction,"AdminRights") ;
			break ;
	}
	
	$TRights=array() ;
	$TRightsVol=array() ;
	
	
	
// Load the right for this member list
	if (($username!="") or ($rightname!="")) { // if at least one parameter is select try to load corresponding rights
    $str="select rightsvolunteers.*,rights.Name as rightname from rightsvolunteers,rights where rights.id=rightsvolunteers.IdRight " ;
	  if ($username!="") {
		  $rwho=LoadRow("select id from members where username='".$username."'") ;
			if (isset($rwho->id)) {
			  $cid=$rwho->id ;
			} 
			else {
			  $cid=0 ;
			}
		  $str.=" and IdMember=".$cid ; 
	  }
	  if ($rightname!="") {
		  $rright=LoadRow("select id from rights where Name='".$rightname."'") ;
			if (isset($rright->id)) {
			  $idright=$rright->id ;
			} 
			else {
			  $idright=0 ;
			}
		  $str.=" and IdRight=".$idright ; 
		}
	  $qry=sql_query($str) ;
	  while ($rr=mysql_fetch_object($qry)) {
	    array_push($TRightsVol,$rr) ;
	  } 
	}
// end of load list

// Load the right list
	$str="select * from rights order by Name asc" ;
	$qry=sql_query($str) ;
	while ($rr=mysql_fetch_object($qry)) {
	  if (!HasRight("Rights",$rr->Name)) continue ; // Skip not allowed rights
		// todo skip already given rights if the user is named
	  array_push($TRights,$rr) ;
	} 
// end of Load the right list
	
	
	
  DisplayAdminRights($username,$rightname,$TRights,$TRightsVol,$rright,$lastaction,$scope) ; // call the layout
	
?>