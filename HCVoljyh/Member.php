<?php
include "lib/dbaccess.php" ;
require_once "lib/FunctionsTools.php" ;
require_once "lib/FunctionsLogin.php" ;
require_once "layout/Error.php" ;


// Find parameters
	$IdMember=GetParam("cid","") ;
	
	if ($IdMember=="") {
	  $errcode="ErrorWithParameters" ;
	  DisplayError(ww("ErrorWithParameters","\$IdMember is not defined")) ;
		exit(0) ;
	}
	

// manage picture photorank (swithing from one picture to the other)
  $photorank=GetParam("photorank",0) ;
	
	
  switch(GetParam("action")) {
	  case "previouspicture" :
		
	    $photorank-- ;
      if ($photorank<=0) $photorank=0 ;
			break ;
	  case "nextpicture" :
	    $photorank++ ;
			break ;
	  case "logout" :
		  Logout("Main.php") ;
			exit(0) ;
	}
	

	$wherestatus=" and Status='Active'" ;
	if (HasRight("Accepter")) {  // accepter right allow for reading member who are not yet active
	  $wherestatus="" ;
	}
// Try to load the member
	if (is_numeric($IdMember)) {
	  $str="select * from members where id=".$IdMember.$wherestatus ;
	}
	else {
		$str="select * from members where Username='".$IdMember."'".$wherestatus ;
	}

	$m=LoadRow($str) ;

	if (!isset($m->id)) {
	  $errcode="ErrorNoSuchMember" ;
	  DisplayError(ww($errcode,$IdMember)) ;
//		die("ErrorMessage=".$ErrorMessage) ;
		exit(0) ;
	}

	$IdMember=$m->id ; // to be sure to have a numeric ID
	
	$profilewarning="" ;
	if ($m->Status!="Active") {
	  $profilewarning="WARNING the status of ".$m->Username." is set to ".$m->Status ;
	} 

	$photo="" ;
	$phototext="" ;
	$str="select * from membersphotos where IdMember=".$IdMember." and SortOrder=".$photorank ;
	$rr=LoadRow($str) ;
	if (!isset($rr->FilePath)and ($photorank>0)) {
	  $rr=LoadRow("select * from membersphotos where IdMember=".$IdMember." and SortOrder=0") ;
	}
	
	if ($m->IdCity>0) {
	   $rWhere=LoadRow("select cities.Name as cityname,regions.Name as regionname,countries.Name as countryname from cities,countries,regions where cities.IdRegion=regions.id and countries.id=regions.IdCountry and cities.id=".$m->IdCity) ;
	}
	
	
	if (isset($rr->FilePath)) {
	  $photo=$rr->FilePath ;
	  $phototext=FindTrad($rr->Comment) ;
		$photorank=$rr->SortOrder;
	} 
	
  $TGroups=array() ;
// Try to load groups and caracteristics where the member belong to
  $str="select membersgroups.Comment as Comment,groups.Name as Name from groups,membersgroups where membersgroups.IdGroup=groups.id and membersgroups.Status='In' and membersgroups.IdMember=".$m->id ;
	$qry=mysql_query($str) ;
	$TGroups=array() ;
	while ($rr=mysql_fetch_object($qry)) {
	  array_push($TGroups,$rr) ;
	}
	

  include "layout/Member.php" ;
  DisplayMember($m,$photo,$phototext,$photorank,$rWhere->cityname,$rWhere->regionname,$rWhere->countryname,$profilewarning,$TGroups) ;

?>
