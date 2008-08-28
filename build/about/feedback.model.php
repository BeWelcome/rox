<?php


/**
 * Aboutus Model
 *
 * @package about
 * @author Andreas (lemon-head), based on work by Michael Dettbarn (bw: lupochen)
 * @copyright hmm
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License (GPL)
 * @version $Id$
 */
class FeedbackModel extends RoxModelBase
{
    public function getFeedbackCategories()
    {
        $categories = $this->bulkLookup(
            "
SELECT id, name
FROM feedbackcategories
            ",
            array('IdCategory', false)
        );
        return $categories;
    }


    public function sendFeedback($get) {

        require_once "bwlegacy.php";
        var_dump($_POST);

		$rCategory = LoadRow("SELECT * FROM feedbackcategories WHERE id=" . GetParam("IdCategory"));
		// feedbackcategory 3 = FeedbackAtSignup
		$IdMember=0;
		if (isset( $_SESSION['IdMember'] )) {
		      $IdMember=$_SESSION['IdMember'];
		}
		$str = "INSERT INTO feedbacks(created,Discussion,IdFeedbackCategory,IdVolunteer,Status,IdLanguage,IdMember) values(now(),'" . GetParam("FeedbackQuestion") . "'," . GetParam("IdCategory") . "," . $rCategory->IdVolunteer . ",'open'," . $_SESSION['IdLanguage'] . "," . $IdMember.")";
		sql_query($str);
		
		$EmailSender=$_SYSHCVOL['FeedbackSenderMail'];
		if (IsLoggedIn()) {
		    $EmailSender=GetEmail($IdMember); // The mail address of the sender can be used for the reply
		    $username = fUsername($_SESSION['IdMember']);
		}
		else {
		    if (GetParam("Email")!="") {
		        $EmailSender=GetParam("Email"); // todo check if this email is a good one !
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
		if (GetParam("answerneededt")=="on") {
		    $text .= "member requested for an answer (".$EmailSender.")\r\n";
		}
		if (GetParam("urgent")=="on") {
		    $text .= "member has ticked the urgent checkbox\r\n";
		}

		bw_mail($rCategory->EmailToNotify, $subj, $text, "", $EmailSender, 0, "nohtml", "", "");
    }
    
}


?>

