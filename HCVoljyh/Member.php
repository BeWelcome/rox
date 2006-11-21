<?php
include "lib/dbaccess.php" ;
require_once "lib/FunctionsTools.php" ;
require_once "lib/FunctionsLogin.php" ;
require_once "layout/Error.php" ;


// Find parameters
	$IdMember="" ;
  if (isset($_GET['cid'])) {
    $IdMember=$_GET['cid'] ;
  }
  if (isset($_POST['cid'])) {
    $IdMember=$_POST['cid'] ;
  }
	if ($IdMember=="") {
	  $errcode="ErrorWithParameters" ;
	  DisplayError(ww("ErrorWithParameters","\$IdMember is not defined")) ;
		exit(0) ;
	}
	

// manage picture photorank (swithing from one picture to the other)
  $photorank=0 ;
  if (isset($_GET['photorank'])) {
    $photorank=$_GET['photorank'] ;
  }
  if (isset($_POST['action'])) {
    $action=$_POST['action'] ;
  }
	
  switch($action) {
	  case "previouspic" :
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
	

// Try to load the member
	if (is_numeric($IdMember)) {
	  $str="select * from members where id=".$IdMember." and Status='Active'" ;
	}
	else {
		$str="select * from members where Username='".$IdMember."' and Status='Active'" ;
	}

	$m=LoadRow($str) ;

	if (!isset($m->id)) {
	  $errcode="ErrorNoSuchMember" ;
	  DisplayError(ww($errcode,$IdMember)) ;
//		die("ErrorMessage=".$ErrorMessage) ;
		exit(0) ;
	}

	$IdMember=$m->id ; // to be sure to have a numeric ID

	$photo="" ;
	$phototext="" ;
	$str="select * from MembersPhotos where IdMember=".$IdMember." and SortOrder=".$photorank ;
	$rr=LoadRow($str) ;
	if (!isset($rr->FilePath)and ($photorank>0)) {
	  $rr=LoadRow("select * from MembersPhotos where IdMember=".$IdMember." and Sortrder=0") ;
	}
	
	if ($m->IdCity>0) {
	   $rWhere=LoadRow("select cities.Name as cityname,regions.Name as regionname,countries.Name as countryname from cities,countries,regions where cities.IdRegion=regions.id and countries.id=regions.IdCountry and cities.id=".$m->IdCity) ;
	}
	
	
	if (isset($rr->FilePath)) {
	  $photo=$rr->FilePath ;
	  $phototext=FindTrad($rr->Comment) ;
		$photorank=$rr->SortOrder;
	} 
	

  include "layout/Member.php" ;
  DisplayMember($m,$photo,$phototext,$photorank,$rWhere->cityname,$rWhere->regionname,$rWhere->countryname) ;

?>
