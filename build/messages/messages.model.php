<?php
/**
 * Gallery model
 * 
 * @package about
 * @author The myTravelbook Team <http://www.sourceforge.net/projects/mytravelbook>
 * @copyright Copyright (c) 2005-2006, myTravelbook Team
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License (GPL)
 * @version $Id$
 */
class MessagesModel extends PAppModel
{
    function __construct()
    {
        parent::__construct();
    }
    
    public function bulkLookup($query_string)
    {
        $rows = array();
        if (!$sql_result = $this->dao->query($query_string)) {
            // sql problem
        } else while ($row = $sql_result->fetch(PDB::FETCH_OBJ)) {
            $rows[] = $row;
        }
        return $rows;
    }
    
    public function singleLookup($query_string)
    {
        if (!$sql_result = $this->dao->query($query_string)) {
            // sql problem
            return false;
        } else if (!$row = $sql_result->fetch(PDB::FETCH_OBJ)) {
            // nothing found
            return false;
        } else {
            return $row;
        }
    }
    
    public function filteredMailbox($where_filters)
    {
        if (!is_array($where_filters)) {
            $where_string = $where_filters;
        } else {
            $where_string = implode(" AND ",$where_filters);
        }
        return $this->bulkLookup(
            "
SELECT
    messages.*,
    receivers.Username AS receiverUsername,
    senders.Username AS senderUsername  
FROM messages
LEFT JOIN members AS receivers
ON messages.IdReceiver = receivers.id
LEFT JOIN members AS senders
ON messages.IdSender = senders.id 
WHERE $where_string
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
            return $this->filteredMailbox('messages.IdReceiver = '.$member_id.' AND messages.Status = "Sent"');
        }
    }
    
    public function getMessage($message_id) {
        $message = $this->singleLookup(
            "
SELECT
    messages.*,
    receivers.Username AS receiverUsername,
    senders.Username AS senderUsername  
FROM messages
LEFT JOIN members AS receivers
ON messages.IdReceiver = receivers.id
LEFT JOIN members AS senders
ON messages.IdSender = senders.id 
WHERE messages.id = $message_id
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
    
    public function getMember($username) {
        return $this->singleLookup(
            "
SELECT *
FROM members
WHERE Username = \"$username\"
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
}




?>
