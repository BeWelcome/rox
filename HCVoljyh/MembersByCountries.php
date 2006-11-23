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

	
  switch($action) {
	  case "logout" :
		  Logout("Main.php") ;
			exit(0) ;
	}
	

// prepare the countries list
  $str="select members.Username as Username,countries.id as id,countries.Name as CountryName,regions.Name as RegionName,cities.Name as CityName  from countries,members,cities,regions where members.IdCity=cities.id and members.Status='Active' and cities.IdRegion=regions.id and countries.id=regions.IdCountry order by countries.id,regions.id,cities.id " ;
	$qry=mysql_query($str) ;
	$TList=array() ;
	while ($rWhile=mysql_fetch_object($qry)) {
	  array_push($TList,$rWhile) ;
	}
	
  require_once "layout/MembersByCountries.php" ;
  DisplayCountries($TList) ; // call the layout with all countries
	

?>
