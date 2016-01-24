<?php

/**
 * represents a single login message
 *
 */
class LoginMessage extends RoxEntityBase
{
    protected $_table_name = 'login_messages';

    /**
     * @param bool $messageId
     */
    public function __construct($messageId = false)
    {
        parent::__construct();
        if ($messageId)
        {
            $this->findById($messageId);
        }
    }


    /**
     * Fetches the latest login message that the member hasn't acknowledged
     *
     * @param Member $member
     * @return array Login messages to be shown to the user
     */
    public function getLatestLoginMessages(Member $member) {
        $q = $this->dao->prepare("
            SELECT
                lm.*
            FROM
                " . $this->_table_name . " lm
            LEFT JOIN `login_messages_acknowledged` lma ON lm.id = lma.messageId AND lma.memberId = ?
            WHERE
                lma.messageId IS NULL
                AND (lm.created > (NOW() - INTERVAL 1 MONTH))
            ORDER BY
                lm.created DESC
            ");
        $memberId = $member->id;
        $q->bindParam(0, $memberId);
        $q->execute();
        $messages = array();
        while ($row = $q->fetch(PDB::FETCH_OBJ)) {
            $messages[$row->id] = $row->text;
        }
        return $messages;
    }

    /**
     * @param integer $id
     * @param Member $member
     */
    public function acknowledgeMessage(Member $member) {
        if (!$this->_has_loaded && $member) {
            return;
        }
        $q = $this->dao->prepare("
            REPLACE INTO
                `login_messages_acknowledged`
                (messageId, memberId, acknowledged)
            VALUES
                (?, ?, 1)");
        $q->bindParam(0, $this->getPKValue());
        $q->bindParam(1, $member->id);
        $q->execute();
    }
}