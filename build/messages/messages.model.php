<?php
/**
 * Messages model
 *
 * @package messages
 * @author Andreas (lemon-head)
 * @copyright Copyright (c) 2005-2006, myTravelbook Team
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License (GPL)
 * @version $Id$
 */
class MessagesModel extends RoxModelBase
{
    public $sort_element;

    function __construct()
    {
        parent::__construct();
    }


    public function filteredMailbox($where_filters, $sort_string=false)
    {
        if (!is_array($where_filters)) {
            $where_string = $where_filters;
        } else {
            $where_string = implode(" AND ",$where_filters);
        }
        if (!$sort_string) {
            $sort_string = $this->sortFilters($this->sort_element);
            if (!$sort_string)
                $sort_string = "IF(messages.created > messages.DateSent, messages.created, messages.DateSent) DESC";
        }

        return $this->bulkLookup(
            "
SELECT
    messages.*,
    UNIX_TIMESTAMP(messages.created)        AS  unixtime_created,
    UNIX_TIMESTAMP(messages.DateSent)       AS  unixtime_DateSent,
    UNIX_TIMESTAMP(messages.updated)        AS  unixtime_updated,
    UNIX_TIMESTAMP(messages.WhenFirstRead)  AS  unixtime_WhenFirstRead,
    receivers.Username                      AS  receiverUsername,
    senders.Username                        AS  senderUsername
FROM
    messages
    LEFT JOIN members  AS  receivers  ON  messages.IdReceiver = receivers.id
    LEFT JOIN members  AS  senders    ON  messages.IdSender   = senders.id
WHERE
    $where_string
ORDER BY
    $sort_string
            "
        );
    }

    public function sortFilters($sort_element=false)
    {
        if (!$sort_element) return false;
        if (!$sort_dir = $this->sort_dir) $sort_dir = 'ASC';
        switch ($sort_element) {
            case 'sender':
                $sort_string = 'senderUsername '.$sort_dir.', unixtime_DateSent DESC';
                break;
            case 'receiver':
                $sort_string = 'receiverUsername '.$sort_dir.', unixtime_DateSent DESC';
                break;
            case 'date':
                $sort_string = 'unixtime_DateSent '.$sort_dir.', senderUsername DESC';
                break;
            default:
                $sort_string = false;
        }
        return $sort_string;
    }

    public function receivedMailbox($sort_element=false)
    {
        if (!isset($_SESSION['IdMember'])) {
            // not logged in - no messages
            return array();
        } else {
            if ($sort_element != false) $sort_string = $this->sortFilters($sort_element);
            else $sort_string = false;
            $member_id = $_SESSION['IdMember'];
            return $this->filteredMailbox('messages.IdReceiver = '.$member_id.' AND messages.Status = "Sent" AND messages.InFolder = "Normal" AND DeleteRequest != "receiverdeleted"');
        }
    }

    public function spamMailbox()
    {
        if (!isset($_SESSION['IdMember'])) {
            // not logged in - no messages
            return array();
        } else {
            $member_id = $_SESSION['IdMember'];
            return $this->filteredMailbox('messages.InFolder = "Spam" AND DeleteRequest != "receiverdeleted"');
        }
    }

    public function getMessage($message_id)
    {
        if (!is_numeric($message_id)) {
            return false;
        }
        $message = $this->singleLookup(
            "
SELECT
    messages.*,
    receivers.Username AS receiverUsername,
    senders.Username   AS senderUsername
FROM
    messages
    LEFT JOIN members AS receivers  ON  messages.IdReceiver = receivers.id
    LEFT JOIN members AS senders    ON  messages.IdSender   = senders.id
WHERE
    messages.id = $message_id
            "
        );
        // check if the message exists
        if (!$message) {
            return false;
        }
        $user_id = $_SESSION['IdMember'];
        // look if the member is allowed to see the message
        if ($message->IdSender == $user_id) {
            return $message;
        } else if ($message->IdReceiver != $user_id) {
            return 0;
        } else if ($message->Status == 'Sent') {
            return $message;
        } else {
            return 0;
        }
    }

    public function deleteMessage($message_id)     {
        if (!is_numeric($message_id)) {
            return false;
        }
        $oldmsg = $this->singleLookup("SELECT DeleteRequest, IdSender, IdReceiver FROM messages WHERE id = '$message_id'");
        $DeleteRequest=$oldmsg->DeleteRequest ;

        if ($oldmsg->IdSender==$_SESSION["IdMember"]) {
            if ($DeleteRequest=="receiverdeleted") {
                $DeleteRequest.="senderdeleted,receiverdeleted";
            } else {
                $DeleteRequest="senderdeleted";
            }
        }
        if ($oldmsg->IdReceiver==$_SESSION["IdMember"]) {
            if ($DeleteRequest=="senderdeleted") {
                $DeleteRequest.="senderdeleted,receiverdeleted";
            } else {
                $DeleteRequest="receiverdeleted";
            }
        }

        if ($DeleteRequest==$oldmsg->DeleteRequest) {
            MOD_log::get()->write("Weird: trying todelete message #$message_id in Tab: $DeleteRequest prévious value=[".$oldmsg->DeleteRequest."](MessagesModel::deleteMessage)", "hacking");
        }
        if ($oldmsg->DeleteRequest!=""){
            $DeleteRequest.="," . $oldmsg->DeleteRequest;
        }
        $ss="UPDATE messages SET DeleteRequest='$DeleteRequest' WHERE id='$message_id'";
        $this->dao->query($ss);
        MOD_log::get()->write("Request to delete message #$message_id in Tab: $DeleteRequest del message  (MessagesModel::deleteMessage)", "message");
    } // end of deleteMessage

    // Mark a message as "read" or "unread"
    public function markReadMessage($message_id, $read = true)
    {
        $this->dao->query(
            "
UPDATE messages
SET
    WhenFirstRead = ".($read ? 'NOW()' : '')."
WHERE id = $message_id
            "
        );
        MOD_log::get()->write("Has read message #" . $message_id."  (MessagesModel::markReadMessage)", "readmessage");
    } // end of markReadMessage

    // Mark a message as "read" or "unread"
    public function moveMessage($message_id, $folder)
    {
        $this->dao->query(
            "
UPDATE messages
SET
    InFolder = '$folder'
WHERE id = $message_id
            "
        );
    }

    /**
     * add and remove marks from the SpamInfo of a message
     * 
     * @param integer $message_id
     * @param array/string $marks_to_add
     * @param array/string $marks_to_remove
     * @param string $spam_info current value of the SpamInfo column (optional)
     * @return void
     */
    public function updateSpamInfo($message_id, $marks_to_add, $marks_to_remove, $spam_info=null)
    {
        if (is_string($marks_to_add)) $marks_to_add = array($marks_to_add);
        if (is_string($marks_to_remove)) $marks_to_remove = array($marks_to_remove);

        if (is_null($spam_info)) {
            $old_msg = $this->singleLookup("
SELECT SpamInfo
FROM
    messages
WHERE
    id = '$message_id'");
            if (!$old_msg) return;
            $spam_info = $old_msg->SpamInfo;
        }

        $marks = explode(',', $spam_info);
        $marks = array_diff($marks, array_merge($marks_to_add, $marks_to_remove));
        $marks = array_merge($marks, $marks_to_add);
        $spam_info = implode(',', $marks);

        $this->dao->query("
UPDATE messages
SET
    SpamInfo = '$spam_info'
WHERE
    id = $message_id
             ");
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

    public function getMessagesWith($contact_id)
    {
        $user_id = $_SESSION['IdMember'];
        return $this->filteredMailbox(
            "
((messages.IdSender = $contact_id AND messages.IdReceiver = $user_id AND messages.Status = \"Sent\")
OR (messages.IdSender = $user_id AND messages.IdReceiver = $contact_id))
AND DeleteRequest != 'receiverdeleted'
            "
        );
    }

    /**
     * Tests if a member has exceeded its limit for sending messages
     *
     * @param int $memberId ID of member
     * @return bool|string False if not exceeded, error message if exceeded
     */
    public function hasMessageLimitExceeded($memberId) {
        // Wash ID
        $id = intval($memberId);

        $query = "
            SELECT
                (
                SELECT
                    COUNT(*)
                FROM
                    comments
                WHERE
                    comments.IdToMember = $id
                    AND
                    comments.Quality = 'Good'
                ) AS numberOfComments,
                (
                SELECT
                    COUNT(*)
                FROM
                    messages
                WHERE
                    messages.IdSender = $id
                    AND
                    (
                        Status = 'ToSend'
                        OR
                        Status = 'Sent'
                        AND
                        DateSent > DATE_SUB(NOW(), INTERVAL 1 HOUR)
                    )
                ) AS numberOfMessagesLastHour,
                (
                SELECT
                    COUNT(*)
                FROM
                    messages
                WHERE
                    messages.IdSender = $id
                    AND
                    (
                        Status = 'ToSend'
                        OR
                        Status = 'Sent'
                        AND
                        DateSent > DATE_SUB(NOW(), INTERVAL 1 DAY)
                    )
                ) AS numberOfMessagesLastDay
            ";
        $row = $this->singleLookup($query);
        $comments = $row->numberOfComments;
        $lastHour = $row->numberOfMessagesLastHour;
        $lastDay = $row->numberOfMessagesLastDay;

        $config = PVars::getObj('messages');

        if ($comments < 1 && (
            $lastHour >= $config->new_members_messages_per_hour ||
            $lastDay >= $config->new_members_messages_per_day)) {

            $words = new MOD_words();
            return $words->getFormatted("YouSentToManyMessages");
        } else {
            return false;
        }
    }

    public function getSpamCheckStatus($IdSender,$IdReceiver)
    {
        $Right = new MOD_right();
// Case NeverCheckSendMail
        if ($Right->hasFlag("NeverCheckSendMail","",$IdSender)) {
            $Status = 'ToSend';
            $SpamInfo = "NotSpam";
            $CheckerComment.="Sent by member with NeverCheckSendMail \n";
        }

// Test what the Spam mark should be
        $SpamInfo = "NotSpam"; // By default its not a Spam
        $tt=explode(";",wwinlang("MessageBlackWord",0));
        $max=count($tt);
        for ($ii=0;$ii<$max;$ii++) {
            if ((strstr($Mes->Message,$tt[$ii])!="")and($tt[$ii]!="")) {
                $SpamInfo = "SpamBlkWord";
                $CheckerComment.="Has BlackWord <b>".$tt[$ii]."</b>\n";
            }
        }

        $tt=explode(";",wwinlang("MessageBlackWord",GetDefaultLanguage($Mes->IdSender)));
        $max=count($tt);
        for ($ii=0;$ii<$max;$ii++) {
            if ((strstr($Mes->Message,$tt[$ii])!="")and($tt[$ii]!="")) {
                $SpamInfo = "SpamBlkWord";
                $CheckerComment.="Has BlackWord (in sender language)<b>".$tt[$ii]."</b>\n";
            }
        }
// End of Test what the Spam mark should be


// Case AlwayCheckSendMail
        if ($Right->hasFlag("AlwayCheckSendMail","",$IdSender)) {
              $Status = 'ToCheck';
              $CheckerComment.="Sent by member with AlwayCheckSendMail \n";
              $str = "update messages set Status='".$Status."',CheckerComment='".$CheckerComment."',SpamInfo='" . $SpamInfo . "' where id=" . $Mes->id . " and Status!='Sent'";
              sql_query($str);
              LogStr("AlwayCheckSendMail for message #".$IdMess." from <b>".fUsername($Mes->IdSender)."</b> to <b>".fUsername($Mes->IdReceiver)."</b>","AutoSpamCheck");
              return($Status);
        }

// Case if receiver has preference PreferenceCheckMyMail set to "Yes"  : mail is always set to toCheck
        $rPrefCheckMyMail = LoadRow("select *  from memberspreferences where IdMember=" . $Mes->IdReceiver . " and IdPreference=4"); // PreferenceCheckMyMail --> IdPref=4
        if (isset($rPrefCheckMyMail->Value) and ($rPrefCheckMyMail->Value == 'Yes')) { // if member has choosen CheckMyMail
            $Status = 'ToCheck';
            $CheckerComment.="Member has asked for checking\n";
            $str = "update messages set Status='".$Status."',CheckerComment='".$CheckerComment."',SpamInfo='" . $SpamInfo . "' where id=" . $Mes->id . " and Status!='Sent'";
            sql_query($str);
            LogStr("PreferenceCheckMyMail for message #".$IdMess." from <b>".fUsername($Mes->IdSender)."</b> to <b>".fUsername($Mes->IdReceiver)."</b>","AutoSpamCheck");
            return($Status);
        }
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

        if (!isset($input['receiver_id'])) {
            // receiver is not set:
            if (!isset($input['receiver_username'])) {
                $problems['receiver_username'] = 'receiver username not set.';
                $problems['receiver_id'] = 'receiver id not set.';
            } else if (!$member = $this->getMember($input['receiver_username'])) {
                // receiver does not exist.
                $problems['receiver_username'] = 'receiver with username does not exist';
            } else {
                $input['receiver_id'] = $member->id;
            }
            // $problems['receiver_id'] = 'no receiver was specified.';
        } else if (!$rReceiver=$this->singleLookup(
            "
SELECT id,Username
FROM members
WHERE id = ".$input['receiver_id']."
            "
        )) {
            // receiver does not exist.
            $problems['receiver_id'] = 'receiver does not exist.';
        }

        if (!isset($input['sender_id'])) {
            // sender is not set.
            $input['sender_id'] = $_SESSION['IdMember'];
            // $problems['sender_id'] = 'no sender was specified.';
        } else if ($input['sender_id'] != $_SESSION['IdMember']) {
            // sender is not the person who is logged in.
            $problems['sender_id'] = 'you are not the sender.';
            MOD_log::get()->write("Trying to send a message with IdMember #".$input['sender_id']." (MessagesModel::sendOrComplain)", "hacking");
        }

        if (empty($input['text'])) {
            $problems['text'] = 'text is empty.';
        }

        if (($msg = $this->hasMessageLimitExceeded($input['sender_id']))) {
            $problems['Message Limit Exceeded'] = $msg;
        }

        $input['status'] = 'ToSend';

        if (!empty($problems)) {
            $message_id = false;
        } else if (!isset($input['draft_id'])) {
            // this was a new message
            if ($message_id = $this->_createMessage($input)) {
                MOD_log::get()->write("Has sent message #" . $message_id." to ".$rReceiver->Username." (MessagesModel::sendOrComplain new message)", "contactmember");
            }
            else { // SOmething has failed
                $problems['sender_id'] = MOD_words::getFormatted("MustProvideTheRightCaptcha");
            }
        } else if (!$this->getMessage($draft_id = $input['message_id'] = $input['draft_id'])) {
            // draft id says this is a draft, but it doesn't exist in database.
            // this means, something stinks.
            // Anyway, we insert a new message.
            if ($message_id = $this->_createMessage($input)) {
                MOD_log::get()->write("Has sent message #" . $message_id." to ".$rReceiver->Username." (MessagesModel::sendOrComplain from draft)", "contactmember");
            }
            else { // SOmething has failed
                $problems['sender_id'] = 'Bad Captcha';
            }
            $message_id = $this->_createMessage($input);
        } else {
            // this was a draft, so we only have to change the status in DB
            $this->_updateMessage($draft_id, $input);
            $message_id = $draft_id;
        }

        return array(
            'problems' => $problems,
            'message_id' => $message_id
        );
    }

    /*
    * This function compute a Captcha in bitmap
    * It look a bit weird to have it in the model, but some day it might require additional data from database
    */
    public function DisplayCaptcha($value) {
        $_SESSION['TheCaptcha']=$value ;
        $ss='<img src="bw/captcha.php?PHPSESSID='.session_id().'" alt="copy this captcha"/>';
//      $ss='<img src="http://www.bewelcome.org/bw/captcha.php" alt="copy this captcha"/>';
//      $ss=$_SESSION['TheCaptcha'] ;
        return($ss) ;
    } // end of DisplayCaptcha

    public function CaptchaNeeded($IdMember) {
        // In case this member is submitted to Captcha
        $ss="select count(*) as NbTrust from comments where comments.Quality='Good' and comments.IdToMember=".$IdMember;
        $mSender=$this->singleLookup($ss) ;
        $BW_Rights = new MOD_right();
        $BW_Flags = new MOD_flag();
        return (($mSender->NbTrust<=0)or($BW_Flags->HasFlag("RequireCaptchaForContact"))) ;
    }

    private function CheckForCaptcha($fields) {
        if ($this->CaptchaNeeded($fields['sender_id'])) {
//      if (($m->NbTrust<=0)or(HasFlag("RequireCaptchaForContact"))) {
            if ($fields["c_verification"]!=$_SESSION['ExpectedCaptchaValue']) {
                MOD_log::get()->write("Captcha failed ".$fields["c_verification"]." entered for ".$_SESSION['ExpectedCaptchaValue']." expected (MessagesModel::CheckForCaptcha)", "contactmember") ;
                return(false) ;
            }
        }
        if (!empty($fields["c_verification"])) { // In case the member has filled a captcha with success, log it
            MOD_log::get()->write("Captcha success ".$fields["c_verification"]." entered (MessagesModel::CheckForCaptcha)", "contactmember") ;
        }
        return(true) ;
    } // end of Check for Captcha

    private function _createMessage($fields)    {
        //if (!$this->CheckForCaptcha($fields)) return false ;
        $attach_picture = (isset($fields['attach_picture']) ? ($fields['attach_picture'] ? 'yes' : 'no') : 'no');
        $status = $this->dao->escape($fields['status']);
        $sender = intval($fields['sender_id']);
        $receiver = intval($fields['receiver_id']);
        $text = $this->dao->escape($fields['text']);
        $parent = !empty($fields['reply_to_id']) ? intval($fields['reply_to_id']) : 0;
        $iMes= $this->dao->query(
            <<<SQL
INSERT INTO messages
SET
    created = NOW(),
    Message = '{$text}',
    IdReceiver = {$receiver},
    IdSender = {$sender},
    InFolder = 'Normal',
    Status = '{$status}',
    JoinMemberPict = '{$attach_picture}',
    IdParent = {$parent}
SQL
        )->insertId();

        return ($iMes) ;
    }


    private function _updateMessage($message_id, $fields)  {

        $this->dao->query(
            "
UPDATE messages
SET
    Message = '".mysql_real_escape_string($fields['text'])."',
    IdReceiver = ".$fields['receiver_id'].",
    IdSender = ".$fields['sender_id'].",
    InFolder = 'Normal',
    Status = '".$fields['status']."',
    JoinMemberPict = '".($fields['attach_picture'] ? 'yes' : 'no')."'
WHERE id = $message_id
            "
        );
    }

}
