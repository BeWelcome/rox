<?php
include "lib/dbaccess.php" ;
require_once "lib/FunctionsLogin.php" ;
require_once "layout/error.php" ;
include "layout/myvisitors.php" ;


// test if is logged, if not logged and forward to the current page
if (!IsLogged()) {
  Logout($_SERVER['PHP_SELF']) ;
	exit(0) ;
}




// Find parameters
	$IdMember=$_SESSION['IdMember'] ;
	if (IsAdmin()) { // admin can alter other profiles
	  $IdMember=GetParam("cid",$_SESSION['IdMember']) ;
	}
	
		
  switch(GetParam("action")) {
	  case "del" : // todo
		  break ;
	}
	
	$TData=array() ;
	$str="select recentvisits.created as datevisite,members.Username,members.ProfileSummary,cities.Name as cityname,regions.Name as regionname,countries.Name as countryname,membersphotos.FilePath as photo,membersphotos.Comment" ;
	$str.=" from cities,countries,regions,recentvisits,members left join membersphotos on membersphotos.IdMember=members.id where cities.IdRegion=regions.id and countries.id=regions.IdCountry and cities.id=members.IdCity and status='Active' and members.id=recentvisits.IdVisitor and recentvisits.IdMember=".$IdMember." and members.status='Active' GROUP BY members.id order by recentvisits.created desc" ;
  $qry=sql_query($str) ;
  while ($rr=mysql_fetch_object($qry)) {
	  if ($rr->Comment>0) {
	    $rr->phototext=FindTrad($rr->Comment) ;
	  }
	  else {
	    $rr->phototext="no comment" ;
	  }
		if ($rr->ProfileSummary>0) {
	    $rr->ProfileSummary=FindTrad($rr->ProfileSummary) ;
		}
		else {
		  $rr->ProfileSummary="" ;
		}
    array_push($TData,$rr) ;
  } 
	
  DisplayMyVisitors($TData,fUsername($IdMember)) ;

?>
