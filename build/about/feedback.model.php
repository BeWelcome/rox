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
SELECT *, id, name
FROM feedbackcategories
            ",
            array('IdCategory', false)
        );
        return $categories;
    }


    public function sendFeedback($vars)
    {
        $categories = $this->getFeedbackCategories();

		$rCategory = $categories[$vars["IdCategory"]];
        $receiver = explode(',', $rCategory->EmailToNotify);
        
		// feedbackcategory 3 = FeedbackAtSignup
		$IdMember = 0;
		$EmailSender = PVars::getObj('syshcvol')->FeedbackSenderMail;
		if (APP_User::isBWLoggedIn("NeedMore,Pending")) {
		    $IdMember = $_SESSION['IdMember'];
            $model = new MembersModel();
            $member = $model->getMemberWithId($_SESSION['IdMember']);
		    $EmailSender = $member->email;
		    $username = $member->username;
		}
		else {
		    if (isset($vars["Email"]) && $vars["Email"]!="") {
		        $EmailSender = $vars["Email"]; // todo check if this email is a good one !
		    }
		    $username = "unknown user";
		}
		$str = "INSERT INTO feedbacks(created,Discussion,IdFeedbackCategory,IdVolunteer,Status,IdLanguage,IdMember) values(now(),'" . $this->dao->escape($vars["FeedbackQuestion"]) . "'," . $vars["IdCategory"] . "," . $rCategory->IdVolunteer . ",'open'," . $_SESSION['IdLanguage'] . "," . $IdMember.")";
		$this->dao->query($str);
		
		// Notify volunteers that a new feedback come in
		// This also send the message to OTRS
		$subj = "New feedback from " . $username . " - Category: " . $rCategory->Name;
		$text = " Feedback from " . $username . "\r\n";
		$text .= "Category " . $rCategory->Name . "\r\n";
		$text .= "Using Browser " . $_SERVER['HTTP_USER_AGENT']." languages:".$_SERVER["HTTP_ACCEPT_LANGUAGE"]." (".$_SERVER["REMOTE_ADDR"].")\r\n";
		// Feedback must not be slashes striped in case of \r\n so we can't use GetParam
		if (empty($vars["FeedbackQuestion"])) {
			$text .= $vars["FeedbackQuestion"] . "\r\n";
		} else if (empty($vars["FeedbackQuestion"])) {
			$text .= $vars["FeedbackQuestion"] . "\r\n";
		}
		if (isset($vars["answerneeded"]) && $vars["answerneeded"]=="on") {
		    $text .= "member requested an answer (".$EmailSender.")\r\n";
		}
		if (isset($vars["urgent"]) && $vars["urgent"]=="on") {
		    $text .= "member has ticked the urgent checkbox\r\n";
		}

		$this->feedbackMail($receiver, $subj, $text, $EmailSender);
    }
    
    /**
     * Sends a Feedback e-mail
     *
     * @param string $IdMember
     */
    public function feedbackMail($receiver, $message_subject, $message_text, $sender)
    {
        //Load the files we'll need
        require_once "bw/lib/swift/Swift.php";
        require_once "bw/lib/swift/Swift/Connection/SMTP.php";
        require_once "bw/lib/swift/Swift/Message/Encoder.php";
        
        //Start Swift
        $swift =& new Swift(new Swift_Connection_SMTP("localhost"));
                
        //Create a message
        $message =& new Swift_Message($message_subject);
        $message->attach(new Swift_Message_Part($message_text));
        
        //Now check if Swift actually sends it
        if ($swift->send($message, $receiver, $sender)) {
            $status = true;
        } else {
            MOD_log::get()->write(" in feedback model $\swift->send: Failed to send a mail to [".$receiver."]", "feedback");
            $status = false;
        }
    }
    
}


?>

