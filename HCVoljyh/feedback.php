<?php
include "lib/dbaccess.php";

$Message="" ;
switch (GetParam("action")) {

	case "ask" :
		$rCategory = LoadRow("select * from feedbackcategories where id=" . GetParam("IdCategory"));
		// feedbackcategory 3 = FeedbackAtSignup
		$IdMember=0 ;
		if (isset( $_SESSION['IdMember'] )) {
		      $IdMember=$_SESSION['IdMember'] ;
		}
		$str = "insert into feedbacks(created,Discussion,IdFeedbackCategory,IdVolunteer,Status,IdLanguage,IdMember) values(now(),'" . addslashes(GetParam(FeedbackQuestion)) . "'," . GetParam("IdCategory") . "," . $rCategory->IdVolunteer . ",'open'," . $_SESSION['IdLanguage'] . "," . $IdMember.")";
		sql_query($str);

		// Notify volunteers that a new feedback come in
		$username = fUsername($_SESSION['IdMember']);
		$subj = "New feedback from " . $username . " Category " . $rCategory->Name;
		$text = " Feedback from " . $username . "\n";
		$text .= "Category " . $rCategory->Name . "\n";
		$text .= GetParam("FeedbackQuestion") . "\n";
		hvol_mail($rCategory->EmailToNotify, $subj, $text, "", $_SYSHCVOL['FeedbackSenderMail'], 0, "", "", "");

		// Todo : make a better display, hide the email
		$Message= "FeedBack Sent to ".$rCategory->EmailToNotify."<br>";

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
