<?php
include "lib/dbaccess.php" ;
require_once "lib/FunctionsLogin.php" ;
require_once "layout/error.php" ;
require_once "layout/updatemandatory.php" ;
?>
<?php

// Find parameters
	$IdMember=$_SESSION['IdMember'] ;
	
	if ((HasRight("Accepter"))and(GetParam("cid")!="")) { // Accepter can alter these data
	  $IdMember=IdMember(GetParam("cid",$_SESSION['IdMember'])) ;
		$ReadCrypted="AdminReadCrypted" ;  // In this case the AdminReadCrypted will be used
	  $IsVolunteerAtWork=true ;
	}
	else {
	  $IsVolunteerAtWork=false ;
		$ReadCrypted="MemberReadCrypted" ;  // In this case the AdminReadCrypted will be used (only owner can decrypt)
	}
	$m=LoadRow("select * from members where id=".$IdMember) ;
	
  if (isset($_POST['FirstName'])) { // If return from form
    $Username=GetParam("Username") ;
    $SecondName=GetParam("SecondName") ;
    $FirstName=GetParam("FirstName") ;
    $LastName=GetParam("LastName") ;
    $Email=GetParam("Email") ;
    $EmailCheck=GetParam("EmailCheck") ;
    $StreetName=GetParam("StreetName") ;
    $Zip=GetParam("Zip") ;
    $HouseNumber=GetParam("HouseNumber") ;
    $IdCountry=GetParam("IdCountry") ;
    $IdCity=GetParam("IdCity") ;
    $IdRegion=GetParam("IdRegion") ;
    $Gender=GetParam("Gender") ;
		$BirthDate=GetParam("BirthDate") ;
		$MemberStatus=GetParam("Status") ;
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
  } // end if return from form
	else {
    $Username=$m->Username ;
		$MemberStatus=$m->Status ;
    $FirstName=$ReadCrypted($m->FirstName) ;
    $SecondName=$ReadCrypted($m->SecondName) ;
    $LastName=$ReadCrypted($m->LastName) ;


    $Email=$ReadCrypted($m->Email) ;
		$EmailCheck=$Email ;
		
		$StreetName="" ;
		$Zip="" ;
		$HouseNumber="" ;
		$IdCountry=0 ;
		$IdCity=0 ;
		$IdRegion=0 ;
		$rAdresse=LoadRow("select StreetName,Zip,HouseNumber,countries.id as IdCountry,cities.id as IdCity,regions.id as IdRegion from addresses,countries,regions,cities where IdMember=".$IdMember." and addresses.IdCity=cities.id and regions.id=cities.IdRegion and countries.id=regions.IdCountry") ;
		if (isset($rAdresse->IdCity)) {
      $IdCountry=$rAdresse->IdCountry ;
      $IdCity=$rAdresse->IdCity ;
      $IdRegion=$rAdresse->IdRegion ;

      $StreetName=$ReadCrypted($rAdresse->StreetName) ;
      $Zip=$ReadCrypted($rAdresse->Zip) ;
      $HouseNumber=$ReadCrypted($rAdresse->HouseNumber) ;
		}


    $Gender=$m->Gender ;
		$HideGender=$m->HideGender ;
		
    $ttdate=explode("-",$m->BirthDate) ;
		$BirthDate=$ttdate[2]."-".$ttdate[1]."-".$ttdate[0] ; // resort BirthDate
		
		$HideBirthDate=$m->HideBirthDate ;
	}
	
	
	$MessageError="" ;
  switch(GetParam("action")) {
	  case "updatemandatory" :  // Member has signup then check parameters
		
			$Username= strtolower($Username);
		  $m=LoadRow("select * from members where Username='".$Username."'") ;
			
			if ($IdCountry<=0) {
			  $IdCity=0 ;$IdRegion=0 ;
			  $MessageError.=ww('SignupErrorProvideCountry')."<br>" ;
			}
			if ($IdRegion<=0) {
			  $IdCity=0 ;
			  $MessageError.=ww('SignupErrorProvideRegion')."<br>" ;
			}
			if ($IdCity<=0) {
			  $MessageError.=ww('SignupErrorProvideCity')."<br>" ;
			}
			if (strlen($StreetName)<=1)  {
			  $MessageError.=ww('SignupErrorProvideStreetName')."<br>" ;
			}
			if (strlen($Zip)<1)  {
			  $MessageError.=ww('SignupErrorProvideZip')."<br>" ;
			}
			if (strlen($HouseNumber)<1)  {
			  $MessageError.=ww('SignupErrorProvideHouseNumber')."<br>" ;
			}
			if (strlen($Gender)<1)  {
			  $MessageError.=ww('SignupErrorProvideGender',ww('IdontSay'))."<br>" ;
			}

			if (!CheckEmail($Email)) $SignupError.=ww('SignupErrorInvalidEmail')."<br>" ;
			if ($Email!=$EmailCheck) $SignupError.=ww('SignupErrorEmailCheck')."<br>" ;

// todo check if BirthDate is valid
      $ttdate=explode("-",$BirthDate) ;
			$DB_BirthDate=$ttdate[2]."-".$ttdate[1]."-".$ttdate[0] ; // resort BirthDate
			if (!checkdate($ttdate[1],$ttdate[0],$ttdate[2]))  {
			  $MessageError.=ww('SignupErrorBirthDate')."<br>" ;
			}
			elseif (fage_value($DB_BirthDate)<$_SYSHCVOL['AgeMinForApplying'])  {
//			  echo "fage_value(",$DB_BirthDate,")=",fage_value($DB_BirthDate),"<br>" ;
			  $MessageError.=ww('SignupErrorBirthDateToLow',$_SYSHCVOL['AgeMinForApplying'])."<br>" ;
			}

      if ($MessageError!="") {
			  DisplayUpdateMandatory($Username,$FirstName,$SecondName,$LastName,$IdCountry,$IdRegion,$IdCity,$HouseNumber,$StreetName,$Zip,$Gender,$MessageError,$BirthDate,$HideBirthDate,$Email,$EmailCheck,$MemberStatus) ;
				exit(0) ;
			}
			
// in case the update is made by a volunteer
      if ($IsVolunteerAtWork) {
// todo store previous values
			
			  $rr=LoadRow("select * from addresses where IdMember=".$m->id) ;
				if (isset($rr->id)) {  // if the member already has an address
				  $str="update addresses set IdCity=".$IdCity.",HouseNumber=".ReplaceInCrypted(addslashes($HouseNumber),$rr->HouseNumber,$m->id).",StreetName=".ReplaceInCrypted(addslashes($StreetName),$rr->StreetName,$m->id).",Zip=".ReplaceInCrypted(addslashes($Zip),$rr->Zip,$m->id)." where id=".$rr->id ;
			    sql_query($str) ;
				}
				else {
			    $str="insert into addresses(IdMember,IdCity,HouseNumber,StreetName,Zip,created,Explanation) Values(".$_SESSION['IdMember'].",".$IdCity.",".InsertInCrypted(addslashes($HouseNumber)).",".InsertInCrypted(addslashes($StreetName)).",".InsertInCrypted(addslashes($Zip)).",now(),\"Address created by volunteer\")" ;
			    sql_query($str) ;
				  LogStr("Doing a mandatoryupdate on <b>".$Username."</b> creating address","updatemandatory") ;
				}
				$m->FirstName=ReplaceInCrypted(addslashes($FirstName),$m->FirstName,$m->id);
				$m->SecondName=ReplaceInCrypted(addslashes($SecondName),$m->SecondName,$m->id) ;
				$m->LastName=ReplaceInCrypted(addslashes($LastName),$m->LastName,$m->id) ;

				$m->Email=ReplaceInCrypted(addslashes($Email),$m->LastName,$m->id) ;
				
			  $str="update members set FirstName=".$m->FirstName.",SecondName=".$m->SecondName.",LastName=".$m->LastName.",Gender='".$Gender."',HideGender='".$HideGender."',BirthDate='".$DB_BirthDate."',HideBirthDate='".$HideBirthDate."',IdCity=".$IdCity." where id=".$m->id ; 
			  sql_query($str) ;
				if (($IsVolunteerAtWork)and($MemberStatus!=$m->Status)) {
			    $str="update members set Status='".$MemberStatus."' where id=".$m->id ; 
			    sql_query($str) ;
				  LogStr("Changing Status from ".$m->Status." to ".$MemberStatus." for member <b>".$Username."</b>","updatemandatory") ;
				}

				LogStr("Doing a mandatoryupdate on <b>".$Username."</b>","updatemandatory") ;
			}
			else { // not volunteer action
			
			echo "not ready" ;
			exit(0) ;

			  $subj=ww("UpdateMantatorySubj",$_SYSHCVOL['SiteName']) ;
			  $text=ww("UpdateMantatoryMailConfirm",$FirstName,$SecondName,$LastName,$_SYSHCVOL['SiteName']) ;
			  $defLanguage=$_SESSION['IdLanguage'] ;
			  hvol_mail($Email,$subj,$text,"",$_SYSHCVOL['SignupSenderMail'],$defLanguage,"","","") ;
				
			
			
			  echo ww('UpdateMantatoryConfirm',$Email) ;


// Notify volunteers that an updater has updated
			  $subj="Update mandatory ".$Username." from ".getcountryname($IdCountry)." has signup" ;
			  $text=" updater is ".$FirstName." ".strtoupper($LastName)."\n" ;
			  $text.="using language ".$_SESSION['IdLanguage']."\n" ;
			  $text.=GetParam("ProfileSummary") ;
			  hvol_mail($_SYSHCVOL['MailToNotifyWhenNewMemberSignup'],$subj,$text,"",$_SYSHCVOL['SignupSenderMail'],0,"","","") ;
			  exit(0) ;
			}
			
	  case "change_country" :
	  case ww('SubmitChooseRegion') :
			  DisplayUpdateMandatory($Username,$FirstName,$SecondName,$LastName,$IdCountry,$IdRegion,$IdCity,$HouseNumber,$StreetName,$Zip,$Gender,$MessageError,$BirthDate,$HideBirthDate,$Email,$EmailCheck,$MemberStatus) ;
			exit(0) ;
	  case "change_region" :
	  case ww('SubmitChooseCity') :
			  DisplayUpdateMandatory($Username,$FirstName,$SecondName,$LastName,$IdCountry,$IdRegion,$IdCity,$HouseNumber,$StreetName,$Zip,$Gender,$MessageError,$BirthDate,$HideBirthDate,$Email,$EmailCheck,$MemberStatus) ;
			exit(0) ;
	}
  DisplayUpdateMandatory($Username,$FirstName,$SecondName,$LastName,$IdCountry,$IdRegion,$IdCity,$HouseNumber,$StreetName,$Zip,$Gender,$MessageError,$BirthDate,$HideBirthDate,$HideGender,$Email,$EmailCheck,$MemberStatus) ;

?>