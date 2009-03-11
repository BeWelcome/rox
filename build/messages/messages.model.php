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
    
    public function receivedMailbox()
    {
        if (!isset($_SESSION['IdMember'])) {
            // not logged in - no messages
            return array();
        } else {
            $member_id = $_SESSION['IdMember'];
            return $this->filteredMailbox('messages.IdReceiver = '.$member_id.' AND messages.Status = "Sent" AND messages.InFolder = "Normal"');
        }
    }
    
    public function spamMailbox()
    {
        if (!isset($_SESSION['IdMember'])) {
            // not logged in - no messages
            return array();
        } else {
            $member_id = $_SESSION['IdMember'];
            return $this->filteredMailbox('messages.InFolder = "Spam"');
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
    
    public function deleteMessage($message_id)
    {
        if (!is_numeric($message_id)) {
            return false;
        }
        $this->dao->query(
            "
DELETE FROM messages
WHERE id = $message_id
            "
        );
    }
    
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
    }
    
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
(messages.IdSender = $contact_id AND messages.IdReceiver = $user_id AND messages.Status = \"Sent\")
OR (messages.IdSender = $user_id AND messages.IdReceiver = $contact_id)
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
        
        if (!isset($input['agree_spam_policy'])) {
            $problems['agree_spam_policy'] = 'you must agree with spam policy.';
        }
        
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
        } else if (!$this->singleLookup(
            "
SELECT id
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
        } else if (!$input['sender_id'] != $_SESSION['IdMember']) {
            // sender is not the person who is logged in.
            $problems['sender_id'] = 'you are not the sender.';
        }
        
        if (empty($input['text'])) {
            $problems['text'] = 'text is empty.';
        }
        
        $input['status'] = 'ToSend';
        
        if (!empty($problems)) {
            $message_id = false;
        } else if (!isset($input['draft_id'])) {
            // this was a new message
            $message_id = $this->_createMessage($input);
        } else if (!$this->getMessage($draft_id = $input['message_id'] = $input['draft_id'])) {
            // draft id says this is a draft, but it doesn't exist in database.
            // this means, something stinks.
            // Anyway, we insert a new message.
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
    
    
    private function _createMessage($fields)
    {
        return $this->dao->query(
            "
INSERT INTO messages
SET
    created = NOW(),
    Message = '".mysql_real_escape_string($fields['text'])."',
    IdReceiver = ".$fields['receiver_id'].",
    IdSender = ".$fields['sender_id'].",
    InFolder = 'Normal',
    Status = '".$fields['status']."',
    JoinMemberPict = '".(isset($fields['attach_picture']) ? ($fields['attach_picture'] ? 'yes' : 'no') : 'no')."'
            "
        )->insertId();
    }
    
    
    private function _updateMessage($message_id, $fields)
    {
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



?>
