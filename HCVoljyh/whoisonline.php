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
	  $str="select members.*,cities.Name as cityname,regions.Name as regionname,countries.Name as countryname,membersphotos.FilePath as photo,membersphotos.Comment,online.updated as lastdateaction,lastactivity from cities,countries,regions,online,members left join membersphotos on membersphotos.IdMember=members.id where cities.IdRegion=regions.id and countries.id=regions.IdCountry and cities.id=members.IdCity and status='Active' and online.IdMember=members.id and online.updated>DATE_SUB(now(),interval ".$_SYSHCVOL['WhoIsOnlineDelayInMinutes']." minute) GROUP BY members.id order by members.LastLogin desc" ;
  }
  else {
	  // Todo there only select profile publics
	  $str="select members.*,cities.Name as cityname,regions.Name as regionname,countries.Name as countryname,membersphotos.FilePath as photo,membersphotos.Comment,online.updated as lastdateaction,lastactivity from cities,countries,regions,online,memberspublicprofiles,members left join membersphotos on membersphotos.IdMember=members.id where cities.IdRegion=regions.id and countries.id=regions.IdCountry and cities.id=members.IdCity and status='Active' and online.IdMember=members.id and online.updated>DATE_SUB(now(),interval ".$_SYSHCVOL['WhoIsOnlineDelayInMinutes']." minute) and online.IdMember=members.id and memberspublicprofiles.IdMember=members.id GROUP BY members.id order by members.LastLogin desc" ;
  }

	
  $TData=array() ;
	$qry=mysql_query($str) ;
//	echo "str=$str<br>" ;
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

  include "layout/whoisonline.php" ;
  DisplayWhoIsOnline($TData) ;

?>
