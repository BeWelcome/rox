<?php
include "lib/dbaccess.php" ;
require_once "lib/FunctionsLogin.php" ;
require_once "layout/error.php" ;


  switch($action) {
	  case "logout" :
		  Logout("main.php") ;
			exit(0) ;

	  case "ask" :
			$rCategory="select * from feedbackcategories where id=".GetParam("IdCategory") ;
			  // feedbackcategory 3 = FeedbackAtSignup
			  $str="insert into feedbacks(created,Discussion,IdFeedbackCategory,IdVolunteer,Status,IdLanguage,IdMember) values(now,'".addslashes(GetParam(FeedbackQuestion))."',".GetParam("IdCategory").",".$rCategory->IdVolunteer.",'open',".$_SESSION['IdLanguage'].",".$_SESSION['IdMember'] ;
			  sql_query($str)  ;
// Notify volunteers that a new feedbac come in
      $username=fUsername($_SESSION['$_SESSION['IdMember']']) ;
			$subj="New feedback from ".$username." Category ".$rCategory->Name ;
			$text=" Feedback from ".$username."\n" ;
			$text.="Category ".$rCategory->Name."\n" ;
			$text.=GetParam(FeedbackQuestion)."\n" ;
			hvol_mail($rCategory->EmailToNotify,$subj,$text,"",$_SYSHCVOL['FeedbackSenderMail'],0,"","","") ;
				
			}
			
			exit(0) ;
	} 
	

	$TFeedBackCategory=array() ;
	$str="select * from feedbackcategories " ;
	$qry=mysql_query($str) ;
	while ($rr=mysql_fetch_object($qry)) {
	  if ($rr->id==3) continue ; // Skip category feedbackatsignup
	  array_push($TFeedBackCategory,$rr) ;
	} 
	
	
  include "layout/feedback.php" ;
  DisplayFeedback($TFeedBackCategory) ;

?>
