<?php
include "lib/dbaccess.php" ;
require_once "lib/FunctionsTools.php" ;
require_once "lib/FunctionsLogin.php" ;
require_once "layout/Error.php" ;
  require_once "layout/Groups.php" ;

	$IdMember=$_SESSION['IdMember'] ;
	
	if (HasRight(Admin)) { // Admin will have access to any member right thru cid
	  $IdMember=GetParam("cid",$_SESSION['IdMember']) ;
	}


	
  switch(GetParam("action")) {
	  case "logout" :
		  Logout("Main.php") ;
			exit(0) ;
	  case "Add" :
		  Logout("Main.php") ;
			exit(0) ;
	  case "ShowMembers" :
		  $TGroup=LoadRow("select * from groups where id=".GetParam("IdGroup")) ;
			$Tlist=array() ;
			$str="select Username,membersgroups.Comment as GroupComment from members,membersgroups where members.id=membersgroups.IdMember and membersgroups.Status='In' and membersgroups.IdGroup=".GetParam("IdGroup") ;
//			echo "str=$str<br>";
	    $qry=sql_query($str) ;
	    while ($rr=mysql_fetch_object($qry)) {
	      array_push($Tlist,$rr) ;
			}
      DisplayGroupMembers($TGroup,$Tlist) ; // call the layout
			exit(0) ;
	  case "ListAll" :
// Try to load the Preferences, prepare the layout data
      $str="select * from groups" ;
	    $qry=sql_query($str) ;
	    $TGroup=array() ;
	    while ($rr=mysql_fetch_object($qry)) {
	      array_push($TGroup,$rr) ;
	    }
	
      DisplayGroupList($TGroup) ; // call the layout
			exit(0) ;
	}
	
// update groups set NbChilds=(select count(*) from groupshierarchy where IdGroupParent=groups.id)

  $TGroup=array() ; // Will receive the results
	AddGroups(1) ; // Add groups starting with first group
  DisplayGroupHierarchyList($TGroup) ; // call the layout
	
	
	function AddGroups($IdGroup,$depht=0) {
	  global $TGroup ;
    // Try to load the available groups according to group hierarchy
    $str="select groups.id as IdGroup,NbChilds,groups.Name as Name,".$depht." as Depht,0 as NbMembers from groups,groupshierarchy where groups.id=groupshierarchy.IdGroupChild and IdGroupParent=".$IdGroup ;
//		echo "str=$str<br>" ;
	  $qry=sql_query($str) ;
	  while ($rr=mysql_fetch_object($qry)) {
		  $rnb=LoadRow("select count(*) as cnt from membersgroups where IdGroup=".$rr->IdGroup) ;
		  $rr->NbMembers=$rnb->cnt ;
	    array_push($TGroup,$rr) ;
		  if ($rr->NbChilds>0) AddGroups($rr->IdGroup,$depht+1) ;
	  }
		return ;
	}

?>
