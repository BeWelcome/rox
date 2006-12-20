<?php
include "lib/dbaccess.php" ;
require_once "lib/FunctionsLogin.php" ;
require_once "layout/error.php" ;
require_once "layout/signupfirststep.php" ;
?>
<?php
  if (IsLogged()) { // Logout the member if one was previously logged on 
		  Logout("") ;
	}
	
// Find parameters
	
  if (isset($_POST['Username'])) { // If return from form
    $Username=GetParam("Username") ;
    $SecondName=GetParam("SecondName") ;
    $FirstName=GetParam("FirstName") ;
    $LastName=GetParam("LastName") ;
    $StreetName=GetParam("StreetName") ;
    $Email=GetParam("Email") ;
    $Zip=GetParam("Zip") ;
    $EmailCheck=GetParam("EmailCheck") ;
    $HouseNumber=GetParam("HouseNumber") ;
    $FeedBack=GetParam("FeedBack") ;
    $ProfileSummary=GetParam("ProfileSummary") ;
    $IdCountry=GetParam("IdCountry") ;
    $IdCity=GetParam("IdCity") ;
    $IdRegion=GetParam("IdRegion") ;
    $FeedBack=GetParam("FeedBack") ;
    $Gender=GetParam("Gender") ;
		$password=GetParam("password") ;
		$secpassword=GetParam("secpassword") ;
		$BirthDate=GetParam("BirthDate") ;
		if (GetParam("HideBirthDate")=="on") {
		  $HideBirthDate="Yes" ;
		}
		else {
		  $HideBirthDate="No" ;
		}

  }
	
	$IdMember=GetParam("cid","") ;
	
	$SignupError="" ;
  switch(GetParam("action")) {
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
			if (($password!=$secpassword)or($password=="")) $SignupError.=ww('SignupErrorPasswordCheck')."<br>" ;
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
			  $SignupError.=ww('SignupErrorProvideGender',ww('IdontSay'))."<br>" ;
			}

// todo check if BirthDate is valid
      $ttdate=explode("-",$BirthDate) ;
			$DB_BirthDate=$ttdate[2]."-".$ttdate[1]."-".$ttdate[0] ; // resort BirthDate
			if (!checkdate($ttdate[1],$ttdate[0],$ttdate[2]))  {
			  $SignupError.=ww('SignupErrorBirthDate')."<br>" ;
			}
			elseif (fage($DB_BirthDate)<$_SYSHCVOL['AgeMinForApplying'])  {
			  echo "fage(",$DB_BirthDate,")=",fage($DB_BirthDate),"<br>" ;
			  $SignupError.=ww('SignupErrorBirthDateToLow',$_SYSHCVOL['AgeMinForApplying'])."<br>" ;
			}

		
//		  DisplaySignupEmailStep() ;
      
      if ($SignupError!="") {
			  DisplaySignupFirstStep($Username,$FirstName,$SecondName,$LastName,$Email,$EmailCheck,$IdCountry,$IdRegion,$IdCity,$HouseNumber,$StreetName,$Zip,$ProfileSummary,$Feedback,$Gender,$password,$secpassword,$SignupError,$BirthDate,$HideBirthDate) ;
				exit(0) ;
			}
			
			
			// Create member
			$str="insert into members(Username,IdCity,Gender,created,Password,BirthDate,HideBirthDate) Values(\"".$Username."\",".$IdCity.",'".$Gender."',"."now(),password('".$password."','".$DB_BirthDate."','".$HideBirthDate."')" ;
//		echo "str=$str<br>" ;
			sql_query($str) ;
			$_SESSION['IdMember']=mysql_insert_id() ;
			

// todo discuss with Marco the real value to insert there			
// For Travelbook compatibility, also insert in user table
      $str="INSERT INTO `user` ( `id` , `auth_id` , `handle` , `email` , `pw` , `active` , `lastlogin` , `location` )
            VALUES (".$_SESSION['IdMember'].", NULL , '', '".$Email."', '', '1', NULL , ".$IdCity.")"; 		
			sql_query($str) ;

// Now that we have a IdMember, insert the email			
			$str="update members set Email=".InsertInCrypted($Email,$_SESSION['IdMember'],"always")." where id=".$_SESSION['IdMember'] ;
			sql_query($str) ;

			$key=CreateKey($Username,$LastName,$_SESSION['IdMember'],"registration") ; // compute a nearly unique key for cross checking
			$str="insert into addresses(IdMember,IdCity,HouseNumber,StreetName,Zip,created,Explanation) Values(".$_SESSION['IdMember'].",".$IdCity.",".InsertInCrypted(addslashes($HouseNumber)).",".InsertInCrypted(addslashes($StreetName)).",".InsertInCrypted(addslashes($Zip)).",now(),\"Signup addresse\")" ;
//			echo "str=$str<br>" ;
			sql_query($str) ;
			$str="update members set FirstName=".InsertInCrypted($FirstName).",SecondName=".InsertInCrypted(addslashes($SecondName)).",LastName=".InsertInCrypted(addslashes($LastName)).",ProfileSummary=".InsertInMTrad(addslashes($ProfileSummary))." where id=".$_SESSION['IdMember'] ;
//			echo "str=$str<br>" ;
			sql_query($str)  ;

			// todo insert feedback if any
			if ($SignupFeedback!="") {
			  // todo save the feedback if any
			}
			
			
			$subj=ww("SignupSubjRegistration",$_SYSHCVOL['SiteName']) ;
			$urltoconfirm=$_SYSHCVOL['SiteName']."/main.php?action=confirmsignup&username=$Username&key=$key&id=".abs(crc32(time())) ; // compute the link for confimring registration
			$text=ww("SignupTextRegistration",$FirstName,$SecondName,$LastName,$_SYSHCVOL['SiteName'],$urltoconfirm,$urltoconfirm) ;
			hvol_mail($Email,$subj,$text,$hh,$_SYSHCVOL['SignupSenderMail'],$_SESSION['IdLanguage'],"","","") ;
			
			echo ww('SignupCheckYourMailToConfirm',$Email) ;
			echo "There is no mail for now<br>so here is the content :<br>" ;
			echo "<b>",$subj,"</b><br>\n" ;
			echo $text,"<br>" ;
			exit(0) ;
	  case "change_country" :
	  case ww('SubmitChooseRegion') :
			  DisplaySignupFirstStep($Username,$FirstName,$SecondName,$LastName,$Email,$EmailCheck,$IdCountry,$IdRegion,$IdCity,$HouseNumber,$StreetName,$Zip,$ProfileSummary,$Feedback,$Gender,$password,$secpassword,$SignupError,$BirthDate,$HideBirthDate) ;
			exit(0) ;
	  case "change_region" :
	  case ww('SubmitChooseCity') :
			  DisplaySignupFirstStep($Username,$FirstName,$SecondName,$LastName,$Email,$EmailCheck,$IdCountry,$IdRegion,$IdCity,$HouseNumber,$StreetName,$Zip,$ProfileSummary,$Feedback,$Gender,$password,$secpassword,$SignupError,$BirthDate,$HideBirthDate) ;
			exit(0) ;
	}
	
  DisplaySignupFirstStep() ;

?>