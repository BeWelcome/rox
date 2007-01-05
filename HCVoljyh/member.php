<?php
include "lib/dbaccess.php" ;
require_once "lib/FunctionsLogin.php" ;
require_once "layout/error.php" ;


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
		  Logout("main.php") ;
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
	
	$NbTrust=0 ;
	$NbComment=0 ;
  $rr=LoadRow("select count(*) as cnt from comments where IdToMember=".$m->id." and Quality='Good'") ;
	if (isset($rr->cnt)) $NbTrust=$rr->cnt ;
  $rr=LoadRow("select count(*) as cnt from comments where IdToMember=".$m->id) ;
	if (isset($rr->cnt)) $NbComment=$rr->cnt ;
	
	if ($m->LastLogin=="11/30/99 00:00:00") $LastLogin= "never";
	else $LastLogin=localdate($m->LastLogin) ;
	
// 	$age  22 years old, // todo compute age and add a description
  $age=fage($m->BirthDate,$m->HideBirthDate) ;
	
	$m->FullName=fFullName($m)  ;

// Load the language the members nows
  $TLanguages=array() ;
  $str="select memberslanguageslevel.IdLanguage as IdLanguage,languages.Name as Name,memberslanguageslevel.Level from memberslanguageslevel,languages where memberslanguageslevel.IdMember=".$m->id." and memberslanguageslevel.IdLanguage=languages.id" ;
	$qry=mysql_query($str) ;
	while ($rr=mysql_fetch_object($qry)) {
	  array_push($TLanguages,$rr) ;
	}
	$m->TLanguages=$TLanguages ;
	
// Load Address data
	$rr=LoadRow("select * from addresses where IdMember=".$m->id," and Rank=0 limit 1") ;
	if (isset($rr->id)) {
	  $m->Address=PublicReadCrypted($rr->HouseNumber,"*")." ".PublicReadCrypted($rr->StreetName,ww("MemberDontShowStreetName")) ;
	  $m->Zip=PublicReadCrypted($rr->Zip,ww("ZipIsCrypted")) ;
		$m->IdGettingThere=$rr->IdGettingThere ;
	}
	
	if ($m->HomePhoneNumber>0) {
	  $m->DisplayHomePhoneNumber=PublicReadCrypted($m->HomePhoneNumber,ww("Hidden")) ;
	}
	if ($m->CellPhoneNumber>0) {
	  $m->DisplayCellPhoneNumber=PublicReadCrypted($m->CellPhoneNumber,ww("Hidden")) ;
	}
	if ($m->WorkPhoneNumber>0) {
	  $m->DisplayWorkPhoneNumber=PublicReadCrypted($m->WorkPhoneNumber,ww("Hidden")) ;
	}
	
  if ($m->Restrictions=="") {
	  $m->TabRestrictions=array() ;
	}
	else {	
	  $m->TabRestrictions=explode(",",$m->Restrictions) ;
	}
	
	if ($m->OtherRestrictions>0) $m->OtherRestrictions=FindTrad($m->OtherRestrictions) ;
	else $m->OtherRestrictions="" ;

// see if the visit of the profile need to be logged
  if (($IdMember!=$_SESSION["IdMember"]) and ($_SESSION["IdMember"]!=1) and (IsLogged())) { // don't log admin visits or visit on self profile
	  $str="insert into recentvisits(IdMember,IdVisitor) values(".$m->id.",".$_SESSION["IdMember"].")" ;
		sql_query($str) ;
	}

	
  include "layout/member.php" ;
  DisplayMember($m,$photo,$phototext,$photorank,$rWhere->cityname,$rWhere->regionname,$rWhere->countryname,$profilewarning,$TGroups,$LastLogin,$NbComment,$NbTrust,$age) ;

?>
