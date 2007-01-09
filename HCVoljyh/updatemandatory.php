<?php
include "lib/dbaccess.php" ;
require_once "lib/FunctionsLogin.php" ;
require_once "layout/error.php" ;
require_once "layout/updatemandatory.php" ;
?>
<?php

// Find parameters
	$IdMember=$_SESSION['IdMember'] ;
	if (HasRight("Accepter")) { // Accepter can alter these data
	  $IdMember=GetParam("cid",$_SESSION['IdMember']) ;
		$ReadCrypted="AdminReadCrypted" ;
	}
	else {
		$ReadCrypted="MemberReadCrypted" ;
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
    $ProfileSummary=GetParam("ProfileSummary") ;
    $IdCountry=GetParam("IdCountry") ;
    $IdCity=GetParam("IdCity") ;
    $IdRegion=GetParam("IdRegion") ;
    $Gender=GetParam("Gender") ;
		$BirthDate=GetParam("BirthDate") ;
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
    $FirstName=$ReadCrypted($m->FirstName) ;
    $SecondName=$ReadCrypted($m->SecondName) ;
    $LastName=$ReadCrypted($m->LastName) ;

    $Email=$ReadCrypted($m->Email) ;
		$EmailCheck="" ;
		
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


    $ProfileSummary=FindTrad($m->ProfileSummary) ;
    $Gender=$m->Gender ;
		
		$BirthDate=$m->BirthDate ;
		$HideBirthDate=$m->HideBirthDate ;
		$HideGender=$m->HideGender ;
	}
	
	
	$MessageError="" ;
  switch(GetParam("action")) {
	  case "SignupFirstStep" :  // Member has signup then check parameters
		
		  $rr=LoadRow("select Username from members where Username='".$Username."'") ;
			$Username= strtolower($Username);
			
		  if (!ctype_alnum($Username)) $MessageError.=ww("SignupErrorWrongUsername")."<br>" ;
		  if (($s_username{0}>='0') && ($s_username{0}<='9')) $MessageError.=ww("SignupErrorWrongUsername")."<br>" ; // A username can't start with a number
			
			
			if(!isset($_POST['Terms'])) $MessageError.=ww("SignupMustacceptTerms")."<br>";


			if (isset($rr->Username)) {
			  $MessageError.=ww("SignupErrorUsernameAlreadyTaken",$Username)."<br>" ;
				$Username="" ;
			}
			

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

// todo check if BirthDate is valid
      $ttdate=explode("-",$BirthDate) ;
			$DB_BirthDate=$ttdate[2]."-".$ttdate[1]."-".$ttdate[0] ; // resort BirthDate
			if (!checkdate($ttdate[1],$ttdate[0],$ttdate[2]))  {
			  $MessageError.=ww('SignupErrorBirthDate')."<br>" ;
			}
			elseif (fage_value($DB_BirthDate)<$_SYSHCVOL['AgeMinForApplying'])  {
			  echo "fage_value(",$DB_BirthDate,")=",fage_value($DB_BirthDate),"<br>" ;
			  $MessageError.=ww('SignupErrorBirthDateToLow',$_SYSHCVOL['AgeMinForApplying'])."<br>" ;
			}

		
      
      if ($MessageError!="") {
			  DisplayUpdateMandatory($Username,$FirstName,$SecondName,$LastName,$IdCountry,$IdRegion,$IdCity,$HouseNumber,$StreetName,$Zip,$ProfileSummary,$Gender,$MessageError,$BirthDate,$HideBirthDate) ;
				exit(0) ;
			}
			
			
			// Create member
			$str="insert into members(Username,IdCity,Gender,created,Password,BirthDate,HideBirthDate) Values(\"".$Username."\",".$IdCity.",'".$Gender."',"."now(),password('".$password."'),'".$DB_BirthDate."','".$HideBirthDate."')" ;
//		echo "str=$str<br>" ;
			sql_query($str) ;
			$_SESSION['IdMember']=mysql_insert_id() ;
			
			$key=CreateKey($Username,$LastName,$_SESSION['IdMember'],"registration") ; // compute a nearly unique key for cross checking
			$str="insert into addresses(IdMember,IdCity,HouseNumber,StreetName,Zip,created,Explanation) Values(".$_SESSION['IdMember'].",".$IdCity.",".InsertInCrypted(addslashes($HouseNumber)).",".InsertInCrypted(addslashes($StreetName)).",".InsertInCrypted(addslashes($Zip)).",now(),\"Signup addresse\")" ;
			sql_query($str) ;
			$str="update members set FirstName=".InsertInCrypted($FirstName).",SecondName=".InsertInCrypted(addslashes($SecondName)).",LastName=".InsertInCrypted(addslashes($LastName)).",ProfileSummary=".InsertInMTrad(addslashes($ProfileSummary))." where id=".$_SESSION['IdMember'] ;
			sql_query($str)  ;

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
	  case "change_country" :
	  case ww('SubmitChooseRegion') :
			  DisplayUpdateMandatory($Username,$FirstName,$SecondName,$LastName,$IdCountry,$IdRegion,$IdCity,$HouseNumber,$StreetName,$Zip,$ProfileSummary,$Gender,$MessageError,$BirthDate,$HideBirthDate) ;
			exit(0) ;
	  case "change_region" :
	  case ww('SubmitChooseCity') :
			  DisplayUpdateMandatory($Username,$FirstName,$SecondName,$LastName,$IdCountry,$IdRegion,$IdCity,$HouseNumber,$StreetName,$Zip,$ProfileSummary,$Gender,$MessageError,$BirthDate,$HideBirthDate) ;
			exit(0) ;
	}
	
  DisplayUpdateMandatory($Username,$FirstName,$SecondName,$LastName,$IdCountry,$IdRegion,$IdCity,$HouseNumber,$StreetName,$Zip,$ProfileSummary,$Gender,$MessageError,$BirthDate,$HideBirthDate,$HideGender) ;

?>