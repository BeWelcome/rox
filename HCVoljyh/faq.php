<?php
include "lib/dbaccess.php" ;
require_once "lib/FunctionsLogin.php" ;
require_once "layout/error.php" ;

  switch(GetParam("action")) {
	  case "logout" :
		  Logout("main.php") ;
			exit(0) ;
	}
	

// prepare the countries list
  $str="select * from faq order by SortOrder" ;
	$qry=sql_query($str) ;
	$TList=array() ;
	while ($rWhile=mysql_fetch_object($qry)) {
	  array_push($TList,$rWhile) ;
	}
	
  require_once "layout/faq.php" ;
  DisplayFaq($TList) ; // call the layout with all countries
	

?>
