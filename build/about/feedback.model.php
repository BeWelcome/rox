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
		$rCategory = $categories[$vars["IdCategory"]-1];
		$receiver_str = str_replace(";", ",", $rCategory->EmailToNotify);
        $receiver = explode(',', $receiver_str);
        
		$IdMember = 0;
		$EmailSender = PVars::getObj('syshcvol')->FeedbackSenderMail;
		if ($member = $this->getLoggedInMember())
        {
		    $EmailSender = $member->get_email();
		    $username = $member->Username;
            $IdMember = $member->id;
		}
		else
        {
		    if (isset($vars["Email"]) && $vars["Email"]!="")
            {
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
		if (!empty($vars["FeedbackQuestion"]))
        {
			$text .= $vars["FeedbackQuestion"] . "\r\n";
		}
        else if (empty($vars["FeedbackQuestion"]))
        {
			$text .= "Feedback text not filled in.\r\n";
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
        // check if the receiver email address is good
        if (!$this->validateEmail($receiver))
        {
            $this->logWrite("In feedback model swift::send: bad email [{$receiver}]", "feedback");
            return false;
        }

        // check if the sender email address is good
        if (!$this->validateEmail($sender))
        {
            $sender = 'dummy_address@bewelcome.org';
        }

        //Load the files we'll need
        require_once SCRIPT_BASE.'lib/misc/swift-mailer/lib/swift_required.php';
        
        //Create the Transport
        $transport = Swift_SmtpTransport::newInstance('localhost', 25);

        //Create the Mailer using your created Transport
        $mailer = Swift_Mailer::newInstance($transport);
        
        try
        {
            //Create the message
            $message = Swift_Message::newInstance()

              //Give the message a subject
              ->setSubject($message_subject)

              //Set the From address with an associative array
              ->setFrom($sender)

              //Set the To addresses with an associative array
              ->setTo($receiver)

              //Give it a body
              ->setBody($message_text)
              ;
        }
        catch (Exception $e)
        {
            $this->logWrite("In feedback model swift::send: caught exception try to send email to [{$receiver}]", "feedback");
            return false;
        }
        
        //Now check if Swift actually sends it
        if ($mailer->send($message))
        {
            return true;
        }
        else
        {
            $this->logWrite("In feedback model swift::send: Failed to send a mail to [{$receiver}]", "feedback");
            return false;
        }
    }
    
    /**
     * checks whether the supplied email address(es) are valid
     *
     * @param string|array $email - email address or array of addresses
     *
     * @access private
     * @return bool
     */
    private function validateEmail($email)
    {
        $return = true;
        if (is_array($email))
        {
            foreach ($email as $e)
            {
                if (!filter_var($e, FILTER_VALIDATE_EMAIL))
                {
                    $return = false;
                }
                
            }
        }
        else
        {
            if (!filter_var($email, FILTER_VALIDATE_EMAIL))
            {
                $return = false;
            }

        }
        return $return;
    }
}

