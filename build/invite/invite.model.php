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
    
    public function getMember($id) {
        return $this->createEntity('Member')->findById($id);
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
        // Maximum 50 emails can be send using the Invitation-Form
        if ($this->_session->has( 'InviteCount' ) && $_SESSION['InviteCount'] > 50) {
            $problems['email'] = 'You already sent more than 50 invitations. Maybe that is enough for now?';
        } elseif (!isset($input['email'])) {
            // $problems['receiver'] = 'no receiver was specified.';
            // receiver does not exist.
            $problems['email'] = 'No receiver set.';
        } else {
            // receiver is set, let's check the email addresses:
            $input['email'] = strtolower($input['email']);
            $input['email'] = str_replace(';',',',$input['email']);
            $input['email'] = str_replace(' ','',$input['email']);
            $email_array = explode(',', $input['email']);
            foreach ($email_array as $email) {
                if (!isset($email) || !$this->isEmailAddress($email)) {
                    $problems['email'] = 'no correct email addresses.';
                }
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
            
            // set the sender
            // FIXME: Read & Uncrypt member's email address from the DB and make it the sender-address
            //$sender_uncrypted = new MOD_member()->getFromMembersTable('email');
            $member = $this->createEntity('Member')->findById($_SESSION['IdMember']);
            $sender = $this->_crypt->MemberReadCrypted($member->Email);
            //$sender = PVars::getObj('syshcvol')->MessageSenderMail;

            $result = MOD_mail::sendEmail($input['subject'],$sender,$email_array, false, $input['text']);
            
        	//Now check if Swift actually sends it
        	if ($result) {
                $status = true;
                $this->getSession->set( 'InviteCount', $this->_session->has( 'InviteCount' ) ? ($_SESSION['InviteCount'] + count($email_array)) : count($email_array) );
        	} else {
        		MOD_log::write("MOD_mail: Failed to send a mail to ".implode(',',$email_array), "MOD_mail");
                $problems['notsend'] = 'InviteNotSent';
                $status = false;
        	}
        }
        
        return array(
            'status' => $status,
            'problems' => $problems,
        );
    }

    public function isEmailAddress ($email) {
        return filter_var($email, FILTER_VALIDATE_EMAIL);
    }
    
    public function style($text,$photo) {
        $html = '<p style="font-family: Arial; font-size: 12px; line-height: 1.5em">';
        if ($photo) {
            $src = MOD_layoutbits::smallUserPic_username($_SESSION['Username']);
            $html .= '<img alt="picture of '.$_SESSION['Username'].'" src="'.PVars::getObj('env')->baseuri.$src.'" style="border: 1px solid #ccc; padding: 6px; margin: 15px; float:left">';
        }
        $html .= $text.'</p>';
        $html .= '<h3 style="font-family: Arial; font-size: 12px; line-height: 1.5em"><a href="http://www.bewelcome.org" style="color: #333">www.bewelcome.org</a></h3>';
        return $html;
    }
    
}


?>
