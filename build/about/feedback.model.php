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

    const SOFTWARE_ISSUE         = 1;
    const SAFETY_AND_ABUSE       = 2;
    const ACCOUNT                = 3;
    const COMMENT_ISSUE          = 4;
    const MEDIA                  = 5;
    const FORUM_MODERTION        = 6;
    const SUGGESTIONS            = 7;
    const VOLUNTEERING           = 8;
    const OTHER                  = 9;
    const DELETE_PROFILE         = 10;
    const GENERAL_FEEDBACK       = 11;

    public function getFeedbackCategories()
    {
        $categories = $this->bulkLookup(
            "
SELECT *, id, name
FROM feedbackcategories
WHERE visible = 1
ORDER BY sortOrder
            ",
            array('IdCategory', false)
        );
        return $categories;
    }

    public function getFeedbackCategory($category_id)
    {
        return $this->singleLookup(<<<SQL
SELECT *
FROM feedbackcategories
WHERE id = {$this->dao->escape($category_id)}
SQL
        );
    }


    public function sendFeedback($vars)
    {
        if (!($category = $this->getFeedbackCategory($vars["IdCategory"])))
        {
            return false;
        }
        $receiver_str = str_replace(";", ",", $category->EmailToNotify);
        $receiver = explode(',', $receiver_str);

        $IdMember = 0;
        $EmailSender = PVars::getObj('syshcvol')->FeedbackSenderMail;
        if ($member = $this->getLoggedInMember())
        {
            // nasty hack: 'encrypted' values are not accessible unless they're not 'encrypted'
            // or the user is editing it's profile. Hence, get the email address by setting
            // the edit mode. This is nasty and should be removed ... when the 'encryption'
            // system is gone
            $member->setEditMode(true);
            $EmailSender = $member->get_email();
            $username = $member->Username;
            $IdMember = $member->id;
            $member->setEditMode(false);
        }
        else
        {
            if (isset($vars["FeedbackEmail"]) && $vars["FeedbackEmail"]!="")
            {
                $EmailSender = $vars["FeedbackEmail"]; // todo check if this email is a good one !
            }
            $username = "unknown user";
        }
        $feedback = $this->createEntity('Feedback');
        if (!$feedback->createNew($category, $vars["FeedbackQuestion"], $member ? $member : null, 'open'))
        {
            return false;
        }

        // Notify volunteers that a new feedback come in
        // This also send the message to OTRS
        $subj = "Your feedback in the category '" . str_replace("_", " ", $category->Name) . "' ";
        $text = "Feedback from " . $username . "\r\n";
        //$text .= "Category '" . str_replace("_", " ", $category->Name) . "'\r\n\r\n";// information already in subject

        // Unserialise data parameter
        if (isset($vars["data"]) && !empty($vars["data"])) {
            $data = unserialize($vars["data"]);
        } else {
            $data = array();
        }

        // Feedback must not be slashes striped in case of \r\n so we can't use GetParam
        if (!empty($vars["FeedbackQuestion"]))
        {
            $text .= $vars["FeedbackQuestion"] . "\r\n\r\n";
        }
        else if (empty($vars["FeedbackQuestion"]))
        {
            $text .= "Feedback text not filled in.\r\n\r\n";
        }

        // Write extra data to mail if this is a comment issue
        if($category->id == self::SAFETY_AND_ABUSE) {
            foreach($data as $key => $value) {
                $text .= $key . ': ' . $value . "\r\n";
            }
        }

        if (isset($vars["answernotneeded"]) && $vars["answernotneeded"]=="on") {
            $text .= "- no reply needed" . "\r\n";
        }

        $text .= "\r\n";
        $text .= 'BW Rox Version: ' . $this->getVersionInfo() . "\r\n";
        $text .= 'Browser: ' . $_SERVER['HTTP_USER_AGENT'] . "\r\n";
        $text .= 'Languages: ' . $_SERVER["HTTP_ACCEPT_LANGUAGE"] . "\r\n";
        if (isset($vars["RequestURI"]) and ! empty($vars["RequestURI"])) {
            $text .= 'Request URI: ' . $vars["RequestURI"] . "\r\n" ;
        }

        return $this->feedbackMail($receiver, $subj, $text, $EmailSender);
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
        require_once SCRIPT_BASE . 'lib/misc/swift-5.0.1/lib/swift_init.php';

        //Create the Transport
        $transport = Swift_SmtpTransport::newInstance('localhost', 25, false);

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
        try
        {
            $sendResult = $mailer->send($message);
        }
        catch (Exception $e)
        {
            $this->logWrite("Exception when executing Swift_Mailer::send()", "feedback");
            $sendResult = false;
        }

        if ($sendResult)
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

