<?php
include "lib/dbaccess.php" ;
require_once "layout/error.php" ;
require_once "layout/adminaccepter.php" ;


function loaddata($Status) {

  $TData=array() ;
	
	$str="select countries.Name as countryname,regions.Name as regionname,cities.Name as cityname,members.* from members,countries,regions,cities where members.id=cities.id and regions.id=cities.IdRegion and countries.id=regions.IdCountry and Status='".$Status."'" ;
	$qry=sql_query($str) ;
	while ($rr=mysql_fetch_object($qry)) {

		$StreetName="" ;
		$Zip="" ;
		$HouseNumber="" ;
		$rAddress=LoadRow("select StreetName,Zip,HouseNumber,countries.id as IdCountry,cities.id as IdCity,regions.Name as regionname,cities.Name as cityname,regions.id as IdRegion from addresses,countries,regions,cities where IdMember=".$rr->id." and addresses.IdCity=cities.id and regions.id=cities.IdRegion and countries.id=regions.IdCountry") ;
		if (isset($rAddress->IdCity)) {
      $rr->StreetName=AdminReadCrypted($rAddress->StreetName) ;
      $rr->Zip=AdminReadCrypted($rAddress->Zip) ;
      $rr->HouseNumber=AdminReadCrypted($rAddress->HouseNumber) ;
		}

		$rr->ProfileSummary=FindTrad($rr->ProfileSummary);
	  array_push($TData,$rr) ;
	} 
	
	return($TData) ;
	
} // end of load data

//------------------------------------------------------------------------------


  $IdMember=GetParam("cid") ;

	$countmatch=0 ;

  $RightLevel=HasRight('Accepter'); // Check the rights
  if ($RightLevel<1) {  
    echo "This Need the sufficient <b>Accepter</b> rights<br>" ;
	  exit(0) ;
  }
	
  $scope=RightScope('Accepter') ;
	
	$lastaction="" ;
  switch(GetParam("action")) {
	  case "logout" :
		  Logout("main.php") ;
			exit(0) ;
			break ;
	  case "accept" :
		  $rr=LoadRow("select * from members where id=".$IdMember) ;
			$lastaction="accepting ".$rr->Username ;
	    $str="update members set Status='Active' where Status='Pending' and id=".$IdMember ;
	    $qry=sql_query($str) ;


			$Email=AdminReadCrypted($rr->Email) ;
			// todo change what need to be change to answer in member default language
			$subj=ww("SignupSubjAccepted",$_SYSHCVOL['SiteName']) ;
			$loginurl=$_SYSHCVOL['SiteName']."/login.php?&Username=".$rr->Username ;
			$text=ww("SignupYouHaveBeenAccepted",$rr->Username,$_SYSHCVOL['SiteName'],$loginurl) ;
			hvol_mail($Email,$subj,$text,$hh,$_SYSHCVOL['AccepterSenderMail'],$_SESSION['IdLanguage'],"","","") ;
			

			break ;
	  case "tocomplete" :
		  $rr=LoadRow("select * from members where id=".$IdMember) ;
			$lastaction="setting to profile of  ".$rr->Username. " to NeedMore"  ;
			
	    $str="update members set Status='NeedMore' where Status='Pending' and id=".$IdMember ;
	    $qry=sql_query($str) ;
			// to do manage the need more
			break ;
	}
	
	$Taccepted=loaddata("Active") ;
	$Tmailchecking=loaddata("MailToConfirm") ; 
	$Tpending=loaddata("Pending") ; 
	$TNeedMore=loaddata("Needmore") ;
	
	
  DisplayAdminAccepter($Taccepted,$Tmailchecking,$Tpending,$TNeedMore,$lastaction) ; // call the layout
	
?>