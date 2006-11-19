<?php
include "lib/dbaccess.php" ;
require_once "lib/FunctionsTools.php" ;
require_once "lib/FunctionsLogin.php" ;
require_once "layout/Error.php" ;
require_once "layout/SignupFirstStep.php" ;

  $action="" ;
  if (isset($_GET['action'])) {
    $action=$_GET['action'] ;
  }
  if (isset($_POST['action'])) {
    $action=$_POST['action'] ;
  }
	
  if (isset($_POST['Username'])) { // If return from form
    $Username=$_POST['Username'] ;
    $SecondName=$_POST['SecondName'] ;
    $FirstName=$_POST['FirstName'] ;
    $LastName=$_POST['LastName'] ;
    $StreetName=$_POST['StreetName'] ;
    $Email=$_POST['Email'] ;
    $Zip=$_POST['Zip'] ;
    $EmailCheck=$_POST['EmailCheck'] ;
    $HouseNumber=$_POST['HouseNumber'] ;
    $FeedBack=$_POST['FeedBack'] ;
    $ProfileSummary=$_POST['ProfileSummary'] ;
    $IdCountry=$_POST['IdCountry'] ;
    $IdCity=$_POST['IdCity'] ;
    $IdRegion=$_POST['IdRegion'] ;
    $FeedBack=$_POST['FeedBack'] ;
    $bday=$_POST['bday'] ;
    $bmonth=$_POST['bmonth'] ;
    $byear=$_POST['byear'] ;
    $Gender=$_POST['Gender'] ;
  }
	
  if (IsLogged()) { // Logout the member if one was previously logged on 
		  Logout("") ;
	}
	
// Find parameters
	$IdMember="" ;
  if (isset($_GET['cid'])) {
    $IdMember=$_GET['cid'] ;
  }
  if (isset($_POST['cid'])) {
    $IdMember=$_POST['cid'] ;
  }
	
	$SignupError="" ;
  switch($action) {
	  case "SignupFirstStep" :  // Member has signup then check parameters
		
		  $rr=LoadRow("select Username from members where Username='".$Username."'") ;
			$Username= strtolower($Username);
			
		  if (!ctype_alnum($Username)) $SignupError.=ww("SignupErrorWrongUsername")."<br>" ;
		  if (($s_username{0}>='0') && ($s_username{0}<='9')) $SignupError.=ww("SignupErrorWrongUsername")."<br>" ; // A username can't start with a number
			
			
			if(!isset($_POST['Terms'])) $SignupError.=ww("SignupMustacceptTerms")."<br>";


			if (isset($rr->Username)) {
			  $SignupError.=ww("SignupErrorUsernameAlreadyTaken",$Username)."<br>" ;
				$Username="" ;
			}
			
			if (!CheckEmail($Email)) $SignupError.=ww('SignupErrorInvalidEmail')."<br>" ;
			if ($Email!=$EmailCheck) $SignupError.=ww('SignupErrorEmailCheck')."<br>" ;
			if ((strlen($FirstName)<=1) or (strlen($LastName)<=1)) {
			  $SignupError.=ww('SignupErrorFullNameRequired')."<br>" ;
			}

			if ($IdCountry<=0) {
			  $IdCity=0 ;$IdRegion=0 ;
			  $SignupError.=ww('SignupErrorProvideCountry')."<br>" ;
			}
			if ($IdRegion<=0) {
			  $IdCity=0 ;
			  $SignupError.=ww('SignupErrorProvideRegion')."<br>" ;
			}
			if ($IdCity<=0) {
			  $SignupError.=ww('SignupErrorProvideCity')."<br>" ;
			}
			if (strlen($StreetName)<=1)  {
			  $SignupError.=ww('SignupErrorProvideStreetName')."<br>" ;
			}
			if (strlen($Zip)<1)  {
			  $SignupError.=ww('SignupErrorProvideZip')."<br>" ;
			}
			if (strlen($HouseNumber)<1)  {
			  $SignupError.=ww('SignupErrorProvideHouseNumber')."<br>" ;
			}
			if (strlen($Gender)<1)  {
			  $Gender.=ww('SignupErrorProvideGender',ww('IdontSay'))."<br>" ;
			}

		
//		  DisplaySignupEmailStep() ;
      
      if ($SignupError!="") {
			  DisplaySignupFirstStep($Username,$FirstName,$SecondName,$LastName,$Email,$EmailCheck,$IdCountry,$IdRegion,$IdCity,$HouseNumber,$StreetName,$Zip,$ProfileSummary,$Feedback,$Gender,$bday,$bmonth,$byear,$SignupError) ;
				exit(0) ;
			}
			
			$Password=crc32($Username." ".$LastName) ;
			
			// Create member
			$str="insert into Members(Username,IdCity,Gender,bday,bmonth,byear,created) Values(\"".$Username."\",".$IdCity.",'".$Gender."',".$bday.",".$bmonth.",".$byear.",now())" ;
			mysql_query($str) or die("problem inserting in Members") ;
			$_SESSION['IdMember']=mysql_insert_id() ;
			$str="insert into Addresses(IdMember,IdCity,HouseNumber,StreetName,Zip,created,Explanation) Values(".$_SESSION['IdMember'].",".$IdCity.",".InsertInCrypted($HouseNumber).",".InsertInCrypted($StreetName).",".InsertInCrypted($Zip).",now(),\"Signup addresse\")" ;
			mysql_query($str) or die("problem inserting into addresses<br>".$str) ;
			$str="update Members set FirstName=".InsertInCrypted($FirstName).",SecondName=".InsertInCrypted($SecondName).",LastName=".InsertInCrypted($LastName).",Email=".InsertInCrypted($Email).",ProfileSummary=".InsertInMTrad($ProfileSummary)." where id=".$_SESSION['IdMember'] ;
			mysql_query($str) or die("problem updating in Members<br>".$str) ;

			// todo insert feedback if any
			if ($SignupFeedback!="") {
			}
			
			
			$subj=ww("SignupSubjRegistration",$_SYSHCVOL['SiteName']) ;
			$urltoconfirm=$_SYSHCVOL['SiteName']."/Main.php?action=confirmsignup&username=$Username&key=$password&id=".crc32(time()) ; // compute the link for confimring registration
			$text=ww("SignupTextRegistration",$FirstName,$SecondName,$LastName,$_SYSHCVOL['SiteName'],$urltoconfirm,$urltoconfirm) ;
			hvol_mail($Email,$subj,$text,$hh,$_SYSHCVOL['SignupSenderMail'],$_SESSION['IdLanguage'],"","","") ;

			echo ww('SignupCheckYourMailToConfirm',$Email) ;
			exit(0) ;
	  case ww('SubmitChooseRegion') :
			  DisplaySignupFirstStep($Username,$FirstName,$SecondName,$LastName,$Email,$EmailCheck,$IdCountry,$IdRegion,$IdCity,$HouseNumber,$StreetName,$Zip,$ProfileSummary,$Feedback,$Gender,$bday,$bmonth,$byear,$SignupError) ;
			exit(0) ;
	  case ww('SubmitChooseCity') :
			  DisplaySignupFirstStep($Username,$FirstName,$SecondName,$LastName,$Email,$EmailCheck,$IdCountry,$IdRegion,$IdCity,$HouseNumber,$StreetName,$Zip,$ProfileSummary,$Feedback,$Gender,$bday,$bmonth,$byear,$SignupError) ;
			exit(0) ;
	}
	
  DisplaySignupFirstStep() ;

?>
