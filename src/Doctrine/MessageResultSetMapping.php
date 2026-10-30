<?php

namespace App\Doctrine;

use App\Entity\Message;
use Doctrine\ORM\Query\ResultSetMapping;

class MessageResultSetMapping extends ResultSetMapping
{
    public function __construct()
    {
        $this->addEntityResult(Message::class, 'm');
        $this->addFieldResult('m', 'id', 'id');
        $this->addFieldResult('m', 'message', 'message');
        $this->addFieldResult('m', 'created', 'created');
        $this->addFieldResult('m', 'first_read', 'firstRead');
        $this->addFieldResult('m', 'delete_request', 'deleteRequest');
        $this->addFieldResult('m', 'spam_info', 'spamInfo');
        $this->addFieldResult('m', 'status', 'status');
        $this->addFieldResult('m', 'folder', 'folder');
        $this->addFieldResult('m', 'checker_comment', 'checkerComment');
        $this->addMetaResult('m', 'parent_id', 'parent_id');
        $this->addMetaResult('m', 'receiver_id', 'receiver_id');
        $this->addMetaResult('m', 'sender_id', 'sender_id');
        $this->addMetaResult('m', 'initiator_id', 'initiator_id');
        $this->addMetaResult('m', 'subject_id', 'subject_id');
        $this->addMetaResult('m', 'request_id', 'request_id');
    }
}
