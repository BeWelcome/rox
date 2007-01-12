<?php
include "lib/dbaccess.php" ;
require_once "lib/FunctionsLogin.php" ;
require_once "layout/error.php" ;


// Find parameters
	$IdMember=IdMember(GetParam("cid","")) ;
	
	if ($IdMember==0) {
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
  $str="select * from members where id=".$IdMember.$wherestatus ;

	$m=LoadRow($str) ;

	if (!isset($m->id)) {
	  $errcode="ErrorNoSuchMember" ;
	  DisplayError(ww($errcode,$IdMember)) ;
//		die("ErrorMessage=".$ErrorMessage) ;
		exit(0) ;
	}

	$profilewarning="" ;
	if ($m->Status!="Active") {
	  $profilewarning="WARNING the status of ".$m->Username." is set to ".$m->Status ;
	} 

// Load photo data
	$photo="" ;
	$phototext="" ;
	$str="select * from membersphotos where IdMember=".$IdMember." and SortOrder=".$photorank ;
	$rr=LoadRow($str) ;
	if (!isset($rr->FilePath)and ($photorank>0)) {
	  $rr=LoadRow("select * from membersphotos where IdMember=".$IdMember." and SortOrder=0") ;
	}
	if (isset($rr->FilePath)) {
	  $photo=$rr->FilePath ;
	  $phototext=FindTrad($rr->Comment) ;
		$photorank=$rr->SortOrder;
	} 
	$m->photo=$photo ;
	$m->photorank=$photorank ;
	$m->phototext=$phototext ;
	
	
// Load geography
	if ($m->IdCity>0) {
	   $rWhere=LoadRow("select cities.Name as cityname,regions.Name as regionname,countries.Name as countryname from cities,countries,regions where cities.IdRegion=regions.id and countries.id=regions.IdCountry and cities.id=".$m->IdCity) ;
     $m->cityname=$rWhere->cityname ;
		 $m->regionname=$rWhere->regionname;
		 $m->countryname=$rWhere->countryname ;
	}
	
	
// Load nbcomments nbtrust
	$m->NbTrust=0 ;
	$m->NbComment=0 ;
  $rr=LoadRow("select count(*) as cnt from comments where IdToMember=".$m->id." and Quality='Good'") ;
	if (isset($rr->cnt)) $m->NbTrust=$rr->cnt ;
  $rr=LoadRow("select count(*) as cnt from comments where IdToMember=".$m->id) ;
	if (isset($rr->cnt)) $m->NbComment=$rr->cnt ;
	
	if ($m->LastLogin=="11/30/99 00:00:00") $m->LastLogin= ww("NeverLog");
	else $m->LastLogin=localdate($m->LastLogin) ;
	
// Load Age
  $m->age=fage($m->BirthDate,$m->HideBirthDate) ;
	
// Load full name
	$m->FullName=fFullName($m)  ;

// Load Address data
	$rr=LoadRow("select * from addresses where IdMember=".$m->id," and Rank=0 limit 1") ;
	if (isset($rr->id)) {
	  $m->Address=PublicReadCrypted($rr->HouseNumber,"*")." ".PublicReadCrypted($rr->StreetName,ww("MemberDontShowStreetName")) ;
	  $m->Zip=PublicReadCrypted($rr->Zip,ww("ZipIsCrypted")) ;
		$m->IdGettingThere=$rr->IdGettingThere ;
	}
	
// Try to load groups and caracteristics where the member belong to
  $TGroups=array() ;
  $str="select membersgroups.Comment as Comment,groups.Name as Name from groups,membersgroups where membersgroups.IdGroup=groups.id and membersgroups.Status='In' and membersgroups.IdMember=".$m->id ;
	$qry=mysql_query($str) ;
	$TGroups=array() ;
	while ($rr=mysql_fetch_object($qry)) {
	  array_push($TGroups,$rr) ;
	}
	

// Load phone
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


// Load the language the members nows
  $TLanguages=array() ;
  $str="select memberslanguageslevel.IdLanguage as IdLanguage,languages.Name as Name,memberslanguageslevel.Level from memberslanguageslevel,languages where memberslanguageslevel.IdMember=".$m->id." and memberslanguageslevel.IdLanguage=languages.id" ;
	$qry=mysql_query($str) ;
	while ($rr=mysql_fetch_object($qry)) {
	  array_push($TLanguages,$rr) ;
	}
	$m->TLanguages=$TLanguages ;
	

// see if the visit of the profile need to be logged
  if (($IdMember!=$_SESSION["IdMember"]) and ($_SESSION["IdMember"]!=1) and (IsLogged())) { // don't log admin visits or visit on self profile
	  $str="insert into recentvisits(IdMember,IdVisitor) values(".$m->id.",".$_SESSION["IdMember"].")" ;
		sql_query($str) ;
	}

	
  include "layout/member.php" ;
  DisplayMember($m,$profilewarning,$TGroups) ;

?>
