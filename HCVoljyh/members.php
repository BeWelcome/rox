<?php
include "lib/dbaccess.php" ;
require_once "lib/FunctionsLogin.php" ;
require_once "layout/error.php" ;

  switch(GetParam("action")) {
	  case "logout" :
		  Logout("login.php") ;
			exit(0) ;
	}
	


  if (IsLogged()) {
	  $str="select members.*,cities.Name as cityname,regions.Name as regionname,countries.Name as countryname from cities,countries,regions,members where cities.IdRegion=regions.id and countries.id=regions.IdCountry and cities.id=members.IdCity" ;
  }
  else {
	  $str="select members.*,cities.Name as cityname,regions.Name as regionname,countries.Name as countryname from cities,countries,regions,members where cities.IdRegion=regions.id and countries.id=regions.IdCountry and cities.id=members.IdCity" ;
  }

  $TData=array() ;
	$qry=mysql_query($str) ;
	while ($rr=mysql_fetch_object($qry)) {
	  array_push($TData,$rr) ;
	}

  include "layout/members.php" ;
  DisplayMembers($TData) ;

?>
