<?php
/**
 * Invite model
 * 
 * @package InviteAFriend
 * @author Micha (lupochen)
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License (GPL)
 * @version $Id$
 */
class InviteModel extends RoxModelBase
{
    function __construct()
    {
        parent::__construct();
    }
    
    public function getMember($username) {
        return $this->singleLookup(
            "
SELECT *
FROM members
WHERE Username = '$username'
            "
        );
    }
    
    
    /**
     * Look if the information in $input is ok to send.
     * If yes, send and return a confirmation.
     * Otherwise, return an array that tells what is missing.
     * 
     * required information in $input:
     * sender_id, receiver_id, text
     * 
     * optional fields in $input:
     * reply_to_id, draft_id
     *
     * @param unknown_type $input
     */
    public function sendOrComplain($input)
    {
        // check fields
        
        $problems = array();
        
        if (!isset($input['email'])) {
            // $problems['receiver'] = 'no receiver was specified.';
            // receiver does not exist.
            $problems['email'] = 'No receiver set.';
        } else {
            // receiver is set, let's check the email addresses:
            $input['email'] = strtolower($input['email']);
            if (!isset($input['email']) || !$this->isEmailAddress($input['email'])) {
            // $problems['receiver'] = 'no correct email addresses.';
            // receiver addresses not correct.
                $problems['email'] = 'no correct email addresses.';
            }
        }
        
        if (!isset($input['sender_id'])) {
            // sender is not set.
            $input['sender_id'] = $_SESSION['IdMember'];
            // $problems['sender_id'] = 'no sender was specified.';
        } else if (!$input['sender_id'] != $_SESSION['IdMember']) {
            // sender is not the person who is logged in.
            $problems['sender_id'] = 'you are not the sender.';
        }
        
        if (empty($input['text'])) {
            $problems['text'] = 'text is empty.';
        }
        
        $input['status'] = 'ToSend';
        
        if (!empty($problems)) {
            $status = false;
        } else {
            // partly copied from htdocs/bw/lib/mailer.php
            //Load the files we'll need
            require_once "bw/lib/swift/Swift.php";
            require_once "bw/lib/swift/Swift/Connection/SMTP.php";
            require_once "bw/lib/swift/Swift/Message/Encoder.php";
        
            //Start Swift
            $swift =& new Swift(new Swift_Connection_SMTP("localhost"));
            
            // FOR TESTING ONLY (using Gmail SMTP Connection for example):
            //$smtp =& new Swift_Connection_SMTP("smtp.gmail.com", Swift_Connection_SMTP::PORT_SECURE, Swift_Connection_SMTP::ENC_TLS);
            //$smtp->setUsername("YOURUSERNAME");
            //$smtp->setpassword("YOURPASSWORD");
        	//$swift =& new Swift($smtp);
        	 
            //Create a message
        	$message =& new Swift_Message($input['subject']);
            
        	//Add some "parts"
        	$message->attach(new Swift_Message_Part($input['text']));
        	$message->attach(new Swift_Message_Part($this->style(stripslashes(str_replace("\n","<br \>",$input['text'])),$input['attach_picture']), "text/html"));
            
            // set the sender
            // FIXME: Read & Uncrypt member's email address from the DB and make it the sender-address
            //$sender_uncrypted = new MOD_member->getFromMembersTable('email');
            //$sender = ???
            $sender = PVars::getObj('syshcvol')->MessageSenderMail;
            
        	//Now check if Swift actually sends it
        	if ($swift->send($message, $input['email'], $sender)) {
                $status = true;
        	} else {
        		LogStr("bw_sendmail_swift: Failed to send a mail to ".$to, "hcvol_mail");
                $status = false;
        	}
        }
        
        return array(
            'status' => $status,
            'problems' => $problems,
        );
    }

	public function isEmailAddress ($email) {
		return preg_match ('#^([a-zA-Z0-9_\-])+(\.([a-zA-Z0-9_\-])+)*@((\[(((([0-1])?([0-9])?[0-9])|(2[0-4][0-9])|(2[0-5][0-5])))\.(((([0-1])?([0-9])?[0-9])|(2[0-4][0-9])|(2[0-5][0-5])))\.(((([0-1])?([0-9])?[0-9])|(2[0-4][0-9])|(2[0-5][0-5])))\.(((([0-1])?([0-9])?[0-9])|(2[0-4][0-9])|(2[0-5][0-5]))\]))|((([a-zA-Z0-9])+(([\-])+([a-zA-Z0-9])+)*\.)+([a-zA-Z])+(([\-])+([a-zA-Z0-9])+)*))$#', $email);
	}
    
	public function style($text,$photo) {
        $html = '<p style="font-family: Arial; font-size: 12px; line-height: 1.5em">';
        if ($photo) {
            $src = MOD_layoutbits::smallUserPic_username($_SESSION['Username']);
            $html .= '<img alt="picture of '.$_SESSION['Username'].'" src="'.$src.'" style="border: 1px solid #ccc; padding: 6px; margin: 15px; float:left">';
        }
        $html .= $text.'</p>';
        $html .= '<h3 style="font-family: Arial; font-size: 12px; line-height: 1.5em"><a href="http://www.bewelcome.org" style="color: #333">www.bewelcome.org</a></h3>';
        return $html;
    }
    
}


?>
