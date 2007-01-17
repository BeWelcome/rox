<?php
include "lib/dbaccess.php" ;
require_once "layout/error.php" ;

// Return the crypting criteraia according of IsHidden_* field of a checkbox
function ShallICrypt($ss) {
//  echo "GetParam(IsHidden_$ss)=",GetParam("IsHidden_".$ss),"<br>" ;
  if (GetParam("IsHidden_".$ss)=="on") return ("crypted") ;
	else  return ("not crypted") ;
} // end of ShallICrypt

  // test if is logged, if not logged and forward to the current page
	// exeption for the people at confirm signup state
  if ( ( !IsLogged()) and (GetParam("action")!="confirmsignup") and (GetParam("action")!="update") ) {
    Logout($_SERVER['PHP_SELF']) ;
	  exit(0) ;
  }

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
	
// Try to load groups and caracteristics where the member belong to
  $str="select membersgroups.id as id,membersgroups.Comment as Comment,groups.Name as Name from groups,membersgroups where membersgroups.IdGroup=groups.id and membersgroups.Status='In' and membersgroups.IdMember=".$IdMember ;
	$qry=sql_query($str) ;
	$TGroups=array() ;
	while ($rr=mysql_fetch_object($qry)) {
	  array_push($TGroups,$rr) ;
	}
	
	$profilewarning="" ; // No warning to display


	switch(GetParam("action")) {
	  case ww("TestThisEmail") :
// Send a test mail
      $subj=ww("TestThisEmailSubject",$_SYSHCVOL['SiteName']) ;
			$text=ww("TestThisEmailText",GetParam("Email")) ;
			hvol_mail(GetParam("Email"),$subj,$text,"",$_SYSHCVOL['TestMail'],0,"yes","","") ;
			$profilewarning="Mail sent to ".GetParam("Email") ; 
		  break ;
			
	  case "update" :
		  
		  $m=LoadRow("select * from members where id=".$IdMember) ;
	    MakeRevision($m->id,"members") ; // create revision
			if (GetParam("HideBirthDate")=="on") {
			  $HideBirthDate="Yes" ;
			}
			else {
			  $HideBirthDate="No" ;
			}
			
			if (GetParam("HideGender")=="on") {
			  $HideGender="Yes" ;
			}
			else {
			  $HideGender="No" ;
			}
			
			
// Analyse Restrictions list
	    $TabRestrictions=mysql_get_set("members","Restrictions") ;
		  $max=count($TabRestrictions) ;
			$Restrictions="" ;
	    for ($ii=0;$ii<$max;$ii++) {
	      if (GetParam("check_".$TabRestrictions[$ii])=="on") {
				  if ($Restrictions!="") $Restrictions.="," ;
					$Restrictions.=$TabRestrictions[$ii] ;
				}
			} // end of for $ii


		  $str="update members set HideBirthDate='".$HideBirthDate."'" ;
		  $str.=",HideGender='".$HideGender."'" ;
			$str.=",MotivationForHospitality=".ReplaceInMTrad(addslashes(GetParam(MotivationForHospitality)),$m->MotivationForHospitality,$IdMember) ;
			$str.=",ProfileSummary=".ReplaceInMTrad(addslashes(GetParam(ProfileSummary)),$m->ProfileSummary,$IdMember) ;
			$str.=",WebSite='".addslashes(GetParam("WebSite"))."'";
			$str.=",Accomodation='".GetParam(Accomodation)."'" ;
		  $str.=",Organizations=".ReplaceInMTrad(addslashes(GetParam(Organizations)),$m->Organizations,$IdMember) ;
		  $str.=",ILiveWith=".ReplaceInMTrad(addslashes(GetParam(ILiveWith)),$m->ILiveWith,$IdMember) ;
		  $str.=",MaxGuest=".GetParam(MaxGuest) ;
		  $str.=",MaxLenghtOfStay=".ReplaceInMTrad(addslashes(GetParam(MaxLenghtOfStay)),$m->MaxLenghtOfStay,$IdMember) ;
		  $str.=",AdditionalAccomodationInfo=".ReplaceInMTrad(addslashes(GetParam(AdditionalAccomodationInfo)),$m->AdditionalAccomodationInfo,$IdMember) ;
		  $str.=",Restrictions='".$Restrictions."'" ;
		  $str.=",OtherRestrictions=".ReplaceInMTrad(addslashes(GetParam(OtherRestrictions)),$m->OtherRestrictions,$IdMember) ;
		  $str.=",HomePhoneNumber=".ReplaceInCrypted(addslashes(GetParam(HomePhoneNumber)),$m->HomePhoneNumber,$IdMember,ShallICrypt("HomePhoneNumber")) ;
		  $str.=",CellPhoneNumber=".ReplaceInCrypted(addslashes(GetParam(CellPhoneNumber)),$m->CellPhoneNumber,$IdMember,ShallICrypt("CellPhoneNumber")) ;
		  $str.=",WorkPhoneNumber=".ReplaceInCrypted(addslashes(GetParam(WorkPhoneNumber)),$m->WorkPhoneNumber,$IdMember,ShallICrypt("WorkPhoneNumber")) ;
		  $str.=",chat_SKYPE=".ReplaceInCrypted(addslashes(GetParam(chat_SKYPE)),$m->chat_SKYPE,$IdMember,ShallICrypt("chat_SKYPE")) ;
		  $str.=",chat_MSN=".ReplaceInCrypted(addslashes(GetParam(chat_MSN)),$m->chat_MSN,$IdMember,ShallICrypt("chat_MSN")) ;
		  $str.=",chat_AOL=".ReplaceInCrypted(addslashes(GetParam(chat_AOL)),$m->chat_AOL,$IdMember,ShallICrypt("chat_AOL")) ;
		  $str.=",chat_YAHOO=".ReplaceInCrypted(addslashes(GetParam(chat_YAHOO)),$m->chat_YAHOO,$IdMember,ShallICrypt("chat_YAHOO")) ;
		  $str.=",chat_ICQ=".ReplaceInCrypted(addslashes(GetParam(chat_ICQ)),$m->chat_ICQ,$IdMember,ShallICrypt("chat_ICQ")) ;
		  $str.=",chat_Others=".ReplaceInCrypted(addslashes(GetParam(chat_Others)),$m->chat_Others,$IdMember,ShallICrypt("chat_Others")) ;
			
			$str.=" where id=".$IdMember ;
	    sql_query($str) ;


// Only update hide/unhide for identity fields
      ReplaceInCrypted(addslashes(MemberReadCrypted($m->FirstName)),$m->FirstName,$IdMember,ShallICrypt("FirstName")) ;
      ReplaceInCrypted(addslashes(MemberReadCrypted($m->SecondName)),$m->SecondName,$IdMember,ShallICrypt("SecondName")) ;
      ReplaceInCrypted(addslashes(MemberReadCrypted($m->LasttName)),$m->LastName,$IdMember,ShallICrypt("LastName")) ;
//			echo "str=$str<br>" ;


// if email has changed
      if (GetParam("Email")!=MemberReadCrypted($m->Email)) {
        ReplaceInCrypted(GetParam("Email"),$m->Email,$IdMember,true) ;
				LogStr("Email updated (previous was ".MemberReadCrypted($m->Email).")","Email Update") ;
			}

			
			// updates groups
			$max=count($TGroups) ;
			for ($ii=0;$ii<$max;$ii++) {
			  $ss=addslashes($_POST["Group_".$TGroups[$ii]->Name]) ;
//				 echo "replace $ss<br> for \$TGroups[",$ii,"]->Comment=",$TGroups[$ii]->Comment," \$IdMember=",$IdMember,"<br> " ; continue ;
				
			  $IdTrad=ReplaceInMTrad($ss,$TGroups[$ii]->Comment,$IdMember) ;
//				echo "replace $ss<br> for \$IdTrad=",$IdTrad,"<br>é ; ;
				if ($IdTrad!=$TGroups[$ii]->Comment) {
	        MakeRevision($TGroups[$ii]->id,"membersgroups") ; // create revision
				  sql_query("update membersgroups set Comment=".$IdTrad." where id=".$TGroups[$ii]->id) ;
				}
			}
			
			// Process languages
			// first  the language the member knows
      $str="select memberslanguageslevel.IdLanguage as IdLanguage,memberslanguageslevel.id as id,languages.Name as Name,memberslanguageslevel.Level from memberslanguageslevel,languages where memberslanguageslevel.IdMember=".$IdMember." and memberslanguageslevel.IdLanguage=languages.id" ;
	    $qry=mysql_query($str) ;
	    while ($rr=mysql_fetch_object($qry)) {
			  $str="update memberslanguageslevel set Level='".GetParam("memberslanguageslevel_level_id_".$rr->id)."' where id=".$rr->id ;
				sql_query($str) ;
	    }
			if (GetParam("memberslanguageslevel_newIdLanguage")!="") {
			  $str="insert into memberslanguageslevel (IdLanguage,Level,IdMember) values(".GetParam("memberslanguageslevel_newIdLanguage").",'".GetParam("memberslanguageslevel_newLevel").$rr->id."',".$IdMember.")" ;
				sql_query($str) ;
			}

			
			
			if ($IdMember==$_SESSION['IdMember']) LogStr("Profil update by member himself","Profil update") ;
			else LogStr("update of another profil","Profil update") ;
			break ;
	  case "logout" :
		  Logout("main.php") ;
			exit(0) ;
	}
	

	$wherestatus=" and (Status='Active' or Status='Pending')" ;
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


// Load the language the member knows
  $TLanguages=array() ;
  $str="select memberslanguageslevel.IdLanguage as IdLanguage,memberslanguageslevel.id as id,languages.Name as Name,memberslanguageslevel.Level from memberslanguageslevel,languages where memberslanguageslevel.IdMember=".$IdMember." and memberslanguageslevel.IdLanguage=languages.id" ;
	$qry=mysql_query($str) ;
	while ($rr=mysql_fetch_object($qry)) {
	  array_push($TLanguages,$rr) ;
	}
  $m->TLanguages=$TLanguages ;
	

// Load the language the member does'nt know
	$m->TOtherLanguages=array() ;
  $str="select languages.Name as Name,languages.id as id from languages where id not in (select IdLanguage from memberslanguageslevel where memberslanguageslevel.IdMember=".$IdMember.")" ;
	$qry=mysql_query($str) ;
	while ($rr=mysql_fetch_object($qry)) {
	  array_push($m->TOtherLanguages,$rr) ;
	}
		
 	if (!isset($m->id)) {
	  $errcode="ErrorNoSuchMember" ;
	  DisplayError(ww($errcode,$IdMember)) ;
//		die("ErrorMessage=".$ErrorMessage) ;
		exit(0) ;
	}

	$IdMember=$m->id ; // to be sure to have a numeric ID
	
	if ($m->Status=="Pending") {
	  $profilewarning=ww("YouCanCompleteProfAndWait",$m->Username) ;
	} 
	elseif ($m->Status!="Active") {
	  $profilewarning="WARNING the status of ".$m->Username." is set to ".$m->Status ;
	} 

	$m->photorank=0 ;
	$m->photo="" ;
	$m->phototext="" ;
	$str="select * from membersphotos where IdMember=".$IdMember." and SortOrder=".$photorank ;
	$rr=LoadRow($str) ;
	if (!isset($rr->FilePath)and ($photorank>0)) {
	  $rr=LoadRow("select * from membersphotos where IdMember=".$IdMember." and SortOrder=0") ;
	}
	
	if ($m->IdCity>0) {
	   $rWhere=LoadRow("select cities.Name as cityname,regions.Name as regionname,countries.Name as countryname from cities,countries,regions where cities.IdRegion=regions.id and countries.id=regions.IdCountry and cities.id=".$m->IdCity) ;
     $m->cityname=$rWhere->cityname ;
		 $m->regionname=$rWhere->regionname;
		 $m->countryname=$rWhere->countryname ;
	}
	
	
	if (isset($rr->FilePath)) {
	  $m->photo=$rr->FilePath ;
	  $m->phototext=FindTrad($rr->Comment) ;
		$m->photorank=$rr->SortOrder;
	} 

	$m->MyRestrictions=explode(",",$m->Restrictions) ;
	$m->TabRestrictions=mysql_get_set("members","Restrictions") ;
  include "layout/editmyprofile.php" ;
  DisplayEditMyProfile($m,$profilewarning,$TGroups) ;

?>
