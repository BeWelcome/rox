<?php
include "lib/dbaccess.php" ;
require_once "lib/FunctionsTools.php" ;
require_once "lib/FunctionsLogin.php" ;
require_once "layout/Error.php" ;

  if (isset($_GET['action'])) {
    $action=$_GET['action'] ;
  }
  if (isset($_POST['action'])) {
    $action=$_POST['action'] ;
  }

	$IdMember=$_SESSION['IdMember'] ;
	
	if (HasRight(Admin)) { // Admin will have access to any member right thru cid
    if (isset($_GET['cid'])) {
      $IdMember=$_GET['cid'] ;
    }
    if (isset($_POST['cid'])) {
      $IdMember=$_POST['cid'] ;
    }
	}
	
  switch($action) {
	  case "logout" :
		  Logout("Main.php") ;
			exit(0) ;
	  case "SelectCountry" :
// Prepare the list of members for this country
      $str="select Members.Username as Username,Countries.Name as CountryName from Countries,Members,Cities,Regions where Members.IdCity=Cities.id and Cities.IdRegion=Regions.id and Countries.id=Regions.IdCountry and Countries.id=".$_POST['IdCountry'] ;
	    $qry=mysql_query($str) ;
	    $TList=array() ;
	    $TitleTable=ww("TheyAreNoMembersThere") ;
	    while ($rWhile=mysql_fetch_object($qry)) {
	      array_push($TList,$rWhile) ;
				$TitleTable=$rWhile->CountryName ;
	    }
	
      require_once "layout/MembersByCountries.php" ;
      DisplayCountry($TitleTable,$TList) ; // call the layout with all countries
			exit(0) ;
	}
	

// prepare the countries list
  $str="select count(*)as Count,Countries.id as id,Countries.Name as Name from Countries,Members,Cities,Regions where Members.IdCity=Cities.id and Cities.IdRegion=Regions.id and Countries.id=Regions.IdCountry group by Countries.id order by Countries.Name" ;
	$qry=mysql_query($str) ;
	$TList=array() ;
	while ($rWhile=mysql_fetch_object($qry)) {
	  array_push($TList,$rWhile) ;
	}
	
  require_once "layout/MembersByCountries.php" ;
  DisplayCountries($TList) ; // call the layout with all countries
	

?>
