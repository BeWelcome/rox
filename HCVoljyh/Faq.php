<?php
include "lib/dbaccess.php" ;
require_once "lib/FunctionsTools.php" ;
require_once "lib/FunctionsLogin.php" ;
require_once "layout/Error.php" ;

  switch(GetParam("action")) {
	  case "logout" :
		  Logout("Main.php") ;
			exit(0) ;
	}
	

// prepare the countries list
  $str="select * from Faq order by SortOrder" ;
	$qry=sql_query($str) ;
	$TList=array() ;
	while ($rWhile=mysql_fetch_object($qry)) {
	  array_push($TList,$rWhile) ;
	}
	
  require_once "layout/Faq.php" ;
  DisplayFaq($TList) ; // call the layout with all countries
	

?>
