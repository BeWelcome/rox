<?php
include "lib/dbaccess.php" ;
require_once "lib/FunctionsTools.php" ;
require_once "lib/FunctionsLogin.php" ;
require_once "layout/Error.php" ;

	if (!isset($_SESSION['IdMember'])) {
	  $errcode="ErrorMustBeIndentified" ;
	  DisplayError(ww($errcode)) ;
		exit(0) ;
	}


// Find parameters
	$IdMember=$_SESSION['IdMember'] ;

	if (IsAdmin()) { // admin can alter other profiles
	  $IdMember=GetParam("cid",$_SESSION['IdMember']) ;
	}

// manage picture photorank (swithing from one picture to the other)
  $photorank=GetParam("photorank",0) ;
	
  $TGroups=array() ;
// Try to load groups and caracteristics where the member belong to
  $str="select membersgroups.id as id,membersgroups.Comment as Comment,groups.Name as Name from groups,membersgroups where membersgroups.IdGroup=groups.id and membersgroups.Status='In' and membersgroups.IdMember=".$IdMember ;
	$qry=mysql_query($str) or dier("error=".$str) ;
	$TGroups=array() ;
	while ($rr=mysql_fetch_object($qry)) {
	  array_push($TGroups,$rr) ;
	}
	
	
  switch(GetParam("action")) {
	  case "update" :
		  
		  $m=LoadRow("select * from members where id=".$IdMember) ;
			
		  $str="update members set ProfileSummary=".ReplaceInMTrad(addslashes($_POST['ProfileSummary']),$m->ProfileSummary,$IdMember) ;
		  $str.=",AdditionalAccomodationInfo=".ReplaceInMTrad(addslashes($_POST['AdditionalAccomodationInfo']),$m->AdditionalAccomodationInfo,$IdMember) ;
			$str.=",Accomodation='".$_POST['Accomodation']."'" ;
		  $str.=",Organizations=".ReplaceInMTrad(addslashes($_POST['Organizations']),$m->Organizations,$IdMember) ;
			$str.=" where id=".$IdMember ;
	    sql_query($str) ;
//			echo "str=$str<br>" ;
			
			// updates groups
			$max=count($TGroups) ;
			for ($ii=0;$ii<$max;$ii++) {
			  $ss=addslashes($_POST["Group_".$TGroups[$ii]->Name]) ;
//				 echo "replace $ss<br> for \$TGroups[",$ii,"]->Comment=",$TGroups[$ii]->Comment," \$IdMember=",$IdMember,"<br> " ; continue ;
				
			  $IdTrad=ReplaceInMTrad($ss,$TGroups[$ii]->Comment,$IdMember) ;
//				echo "replace $ss<br> for \$IdTrad=",$IdTrad,"<br>é ; ;
				if ($IdTrad!=$TGroups[$ii]->Comment) {
				  sql_query("update membersgroups set Comment=".$IdTrad." where id=".$TGroups[$ii]->id) ;
				}
			}
			
			
			if ($IdMember==$_SESSION['IdMember']) LogStr("Profil update by member himself","Profil update") ;
			else LogStr("update of another profil","Profil update") ;
			break ;
	  case "logout" :
		  Logout("Main.php") ;
			exit(0) ;
	}
	

	$wherestatus=" and Status='Active'" ;
	if (HasRight("Accepter")) {  // accepter right allow for reading member who are not yet active
	  $wherestatus="" ;
	}
// Try to load the member
	if (is_numeric($IdMember)) {
	  $str="select * from members where id=".$IdMember.$wherestatus ;
	}
	else {
		$str="select * from members where Username='".$IdMember."'".$wherestatus ;
	}

	$m=LoadRow($str) ;

	if (!isset($m->id)) {
	  $errcode="ErrorNoSuchMember" ;
	  DisplayError(ww($errcode,$IdMember)) ;
//		die("ErrorMessage=".$ErrorMessage) ;
		exit(0) ;
	}

	$IdMember=$m->id ; // to be sure to have a numeric ID
	
	$profilewarning="" ;
	if ($m->Status!="Active") {
	  $profilewarning="WARNING the status of ".$m->Username." is set to ".$m->Status ;
	} 

	$photo="" ;
	$phototext="" ;
	$str="select * from membersphotos where IdMember=".$IdMember." and SortOrder=".$photorank ;
	$rr=LoadRow($str) ;
	if (!isset($rr->FilePath)and ($photorank>0)) {
	  $rr=LoadRow("select * from membersphotos where IdMember=".$IdMember." and SortOrder=0") ;
	}
	
	if ($m->IdCity>0) {
	   $rWhere=LoadRow("select cities.Name as cityname,regions.Name as regionname,countries.Name as countryname from cities,countries,regions where cities.IdRegion=regions.id and countries.id=regions.IdCountry and cities.id=".$m->IdCity) ;
	}
	
	
	if (isset($rr->FilePath)) {
	  $photo=$rr->FilePath ;
	  $phototext=FindTrad($rr->Comment) ;
		$photorank=$rr->SortOrder;
	} 

  include "layout/EditMyProfile.php" ;
  DisplayEditMyProfile($m,$photo,$phototext,$photorank,$rWhere->cityname,$rWhere->regionname,$rWhere->countryname,$profilewarning,$TGroups) ;

?>
