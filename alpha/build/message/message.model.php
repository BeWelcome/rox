<?php
/**
 * Message model
 *
 * @package message
 * @author The myTravelbook Team <http://www.sourceforge.net/projects/mytravelbook>
 * @copyright Copyright (c) 2005-2006, myTravelbook Team
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License (GPL)
 */
class Message extends PAppModel {
    private $_dao;
    
    public function __construct() {
        parent::__construct();
    }


//    insertMessage($msgId, $User->getId(), implode(',', array_keys($recipients)), $vars['s'], $vars['txt'], count($recipients)+1);
    public function writeEntry($senderId, $recipients, $subject, $text, $refcount)
    {
        $msgId = $this->dao->nextId('message');
        $s = $this->dao->prepare('
INSERT INTO `message`
(`message_id`, `sender_id_foreign`, `recipients`, `subject`, `text`, `created`, `refcount`)
VALUES
(?, ?, ?, ?, ?, NOW(), ?)');
        $s->execute(array(
            0=>$msgId,
            1=>$senderId, 2=>$recipients,
            3=>$subject, 4=>$text, 5=>$refcount)
        );
        /*
        if ($msgId !== $s->insertId()) {
            $vars['errors'][] = 'not_created';
            return false;
        }
         */
        return $s->insertId();
    }

    public function insertOutbox($userId, $msgId)
    {
        $query = '
INSERT INTO `user_outbox`
(`user_id_foreign`, `message_id_foreign`)
VALUES
(
    '.(int)$userId.',
    '.$msgId.'
)';
        return $this->dao->query($query);
    }

    public function insertInbox($userId, $msgId)
    {
        $query = '
INSERT INTO `user_inbox`
(`user_id_foreign`, `message_id_foreign`)
VALUES
(
    '.(int)$userId.',
    '.$msgId.'
)';
        return $this->dao->query($query);
    }

    /**
     * Returns true if the user has that message in his inbox.
     */
    public function hasInboxMessage($userId, $msgId)
    {
        $s = $this->dao->query('
SELECT `user_id_foreign`
FROM `user_inbox`
WHERE `user_id_foreign`='.(int)$userId.' AND `message_id_foreign`='.(int)$msgId);
        return ($s->numRows() > 0);
    }

    /**
     * Returns true if the user has that message in his outbox.
     */
    public function hasOutboxMessage($userId, $msgId)
    {
        $s = $this->dao->query('
SELECT `user_id_foreign`
FROM `user_outbox`
WHERE `user_id_foreign`='.(int)$userId.' AND `message_id_foreign`='.(int)$msgId);
        return ($s->numRows() > 0);
    }

    /**
     * Removes the entry from a message box.
     * @arg boolean $inbox  If true we remove from INBOX otherwise from OUTBOX.
     */
    private function _deleteFromBox($userId, $msgId, $inbox)
    {
            $query = '
DELETE FROM `'.($inbox?'user_inbox':'user_outbox').'` 
WHERE `user_id_foreign`='.(int)$userId.' AND `message_id_foreign`='.(int)$msgId;

        // does this really return 1 if deletion was done and otherwise 0?
        if (0 == $this->dao->exec($query))
            return false;
            
        // update refcount
        $s = $this->dao->query('SELECT `refcount` FROM `message` WHERE `message_id`='.(int)$msgId);
        $obj = $s->fetch(PDB::FETCH_OBJ);
        if ($obj->refcount <= 1) { // delete message
            $this->dao->query('DELETE FROM `message` WHERE `message_id` = '.(int)$msgId);
        } else { // update refcount
            $this->dao->query('UPDATE `message` SET `refcount`=`refcount` - 1 WHERE `message_id`='.(int)$msgId);
        }
        return true;
    }

    public function deleteInboxMessage($userId, $msgId)
    {
        return $this->_deleteFromBox($userId, $msgId, true);
    }

    public function deleteOutboxMessage($userId, $msgId)
    {
        return $this->_deleteFromBox($userId, $msgId, false);
    }

    public function getInbox($userId)
    {
        if (!APP_User::login())
            return false;
        $query = '
SELECT 
    m.`message_id`, m.`subject`, m.`text`, m.`sender_id_foreign`,
    b.`seen`,
    UNIX_TIMESTAMP(m.`created`) AS `unix_created`,
    u.`handle` AS user_handle
FROM `user_inbox` AS b
LEFT JOIN `message` AS m 
    ON m.`message_id`=b.`message_id_foreign`
LEFT JOIN `user` AS u
    ON m.`sender_id_foreign`=u.`id`
WHERE b.`user_id_foreign`=\''.(int)$userId.'\'
ORDER BY m.`created` DESC';
        $s = $this->dao->query($query);
        if ($s->numRows() == 0)
            return false;

        return $s;
    }

    public function getOutbox($userId)
    {
        if (!APP_User::login())
            return false;
        $query = '
SELECT 
    m.`message_id`, m.`subject`, m.`text`, m.`recipients`,
    UNIX_TIMESTAMP(m.`created`) AS `unix_created`
FROM `user_outbox` AS b
LEFT JOIN `message` AS m ON 
    m.`message_id`=b.`message_id_foreign`
WHERE b.`user_id_foreign`=\''.(int)$userId.'\'
ORDER BY m.`created` DESC';
        $s = $this->dao->query($query);
        if ($s->numRows() == 0)
            return false;

        return $s;
    }

    public function isOwnedMessageId($msgId, $userId=null) {
        $query = 'SELECT `sender_id_foreign`, `recipients` FROM `message` WHERE `message_id` = '.(int)$msgId;
        $s = $this->dao->query($query);
        if (null == $userId) {
            return ($s->numRows() !== 0);
        }
        $m = $s->fetch();
        return ($m['sender_id_foreign'] == $userId || 
            in_array($userId, explode(',', $m['recipients'])));
    }

    public function getMessage($id)
    {
        if (!APP_User::login())
            return false;
        $query = '
SELECT
    `subject`,
    `text`,
    `sender_id_foreign`,
    `recipients`,
    UNIX_TIMESTAMP(`created`) AS `unix_created`
FROM `message`
WHERE `message_id`=\''.(int)$id.'\'
    ';
        $s = $this->dao->query($query);
        if ($s->numRows() == 0)
            return false;
        
        return $s->fetch(PDB::FETCH_OBJ);
    }

    public function setMessageSeen($userId, $msgId)
    {
        $this->dao->query('
UPDATE `user_inbox`
SET `seen`=1
WHERE `message_id_foreign`='.(int)$msgId.' AND `user_id_foreign`='.(int)$userId);
    }





}
?>
