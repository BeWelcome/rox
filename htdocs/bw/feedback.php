<?php
/*

Copyright (c) 2007 BeVolunteer

This file is part of BW Rox.

BW Rox is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

BW Rox is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, see <http://www.gnu.org/licenses/> or 
write to the Free Software Foundation, Inc., 59 Temple Place - Suite 330, 
Boston, MA  02111-1307, USA.   

*/
require_once "lib/init.php";
require_once "layout/feedback.php";

$Message="";
switch (GetParam("action")) {

	case "ask" :
		$rCategory = LoadRow("SELECT * FROM feedbackcategories WHERE id=" . GetParam("IdCategory"));
		// feedbackcategory 3 = FeedbackAtSignup
		$IdMember=0;
		if (isset( $_SESSION['IdMember'] )) {
		      $IdMember=$_SESSION['IdMember'];
		}
		$str = "INSERT INTO feedbacks(created,Discussion,IdFeedbackCategory,IdVolunteer,Status,IdLanguage,IdMember) values(now(),'" . GetStrParam("FeedbackQuestion") . "'," . GetParam("IdCategory") . "," . $rCategory->IdVolunteer . ",'open'," . $_SESSION['IdLanguage'] . "," . $IdMember.")";
		sql_query($str);
		
		$EmailSender=$_SYSHCVOL['FeedbackSenderMail'];
		if (IsLoggedIn()) {
		    $EmailSender=GetEmail($IdMember); // The mail address of the sender can be used for the reply
		    $username = fUsername($_SESSION['IdMember']);
		}
		else {
		    if (GetStrParam("Email")!="") {
		        $EmailSender=GetStrParam("Email"); // todo check if this email is a good one !
		    }
		    $username="unknown user ";
		}
		
		// Notify volunteers that a new feedback come in
		// This also send the message to OTRS
		$subj = "New feedback from " . $username . " - Category: " . $rCategory->Name;
		$text = " Feedback from " . $username . "\r\n";
		$text .= "Category " . $rCategory->Name . "\r\n";
		$text .= "Using Browser " . $_SERVER['HTTP_USER_AGENT']." languages:".$_SERVER["HTTP_ACCEPT_LANGUAGE"]." (".$_SERVER["REMOTE_ADDR"].")\r\n";
		// Feedback must not be slashes striped in case of \r\n so we can't use GetParam
		if (empty($_POST["FeedbackQuestion"])) {
			$text .= $_GET["FeedbackQuestion"] . "\r\n";
		} else if (empty($_GET["FeedbackQuestion"])) {
			$text .= $_POST["FeedbackQuestion"] . "\r\n";
		}
		if (GetStrParam("answerneeded")=="on") {
		    $text .= "member requested for an answer (".$EmailSender.")\r\n";
		}
		if (GetStrParam("urgent")=="on") {
		    $text .= "member has ticked the urgent checkbox\r\n";
		}

		if (IsLoggedIn()) {
			LogStr($subj."<br />".$text."<br />".$rCategory->EmailToNotify,"feedback") ;
		}
		else {
			LogStr($subj."<br />".$text."<br />".$rCategory->EmailToNotify." to:".$EmailSender,"feedback") ;
		}

		bw_mail($rCategory->EmailToNotify, $subj, $text, "", $EmailSender, 0, "nohtml", "", "");
//		echo "feedback email sent to ",$rCategory->EmailToNotify;

		DisplayResults( ww("FeedBackSent"));
		exit(0);

}

$TFeedBackCategory = array ();
$str = "select * from feedbackcategories ";
$qry = mysql_query($str);
while ($rr = mysql_fetch_object($qry)) {
	if ($rr->id == 3)
		continue; // Skip category feedbackatsignup
	array_push($TFeedBackCategory, $rr);
}

DisplayFeedback($TFeedBackCategory,$Message,GetParam("IdCategory",0));
?>
