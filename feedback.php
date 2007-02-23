<?php
require_once "lib/init.php";

$Message="" ;
switch (GetParam("action")) {

	case "ask" :
		$rCategory = LoadRow("select * from feedbackcategories where id=" . GetParam("IdCategory"));
		// feedbackcategory 3 = FeedbackAtSignup
		$IdMember=0 ;
		if (isset( $_SESSION['IdMember'] )) {
		      $IdMember=$_SESSION['IdMember'] ;
		}
		$str = "insert into feedbacks(created,Discussion,IdFeedbackCategory,IdVolunteer,Status,IdLanguage,IdMember) values(now(),'" . GetParam("FeedbackQuestion") . "'," . GetParam("IdCategory") . "," . $rCategory->IdVolunteer . ",'open'," . $_SESSION['IdLanguage'] . "," . $IdMember.")";
		sql_query($str);
		
		$EmailSender=$_SYSHCVOL['FeedbackSenderMail'] ;
		if (IsLoggedIn()) {
		    $EmailSender=GetEmail($IdMember) ; // The mail address of the sender can be used for the reply
			$username = fUsername($_SESSION['IdMember']);
		}
		else {
		   if (GetParam("Email")!="") {
		   	   $EmailSender=GetParam("Email") ;
		   }
		   $username="unknown user " ;
		}

		// Notify volunteers that a new feedback come in
		// This also send the message to OTRS
		$subj = "New feedback from " . $username . " Category " . $rCategory->Name;
		$text = " Feedback from " . $username . "\n";
		$text .= "Category " . $rCategory->Name . "\n";
		$text .= $_POST["FeedbackQuestion"].$_GET["FeedbackQuestion"] . "\n"; // Feedback must not be slashes striped in case of \r\n so we can't use GetParam
		if (GetParam("answerneededt")=="on") {
		    $text .= "member requested for an answer (".$EmailSender.")\n";
		}
		if (GetParam("urgent")=="on") {
		    $text .= "member has ticked the urgent checkbox\n";
		}

		bw_mail($rCategory->EmailToNotify, $subj, $text, "", $EmailSender, 0, "nohtml", "", "");
//		echo "feedback email sent to ",$rCategory->EmailToNotify ;

		$Message= ww("FeedBackSent") ;;

}

$TFeedBackCategory = array ();
$str = "select * from feedbackcategories ";
$qry = mysql_query($str);
while ($rr = mysql_fetch_object($qry)) {
	if ($rr->id == 3)
		continue; // Skip category feedbackatsignup
	array_push($TFeedBackCategory, $rr);
}

include "layout/feedback.php";
DisplayFeedback($TFeedBackCategory,$Message,GetParam("IdCategory",0));
?>
