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
        $this->addFieldResult('m', 'MessageType', 'messageType');
        $this->addFieldResult('m', 'Message', 'message');
        $this->addFieldResult('m', 'created', 'created');
        $this->addFieldResult('m', 'updated', 'updated');
        $this->addFieldResult('m', 'DateSent', 'dateSent');
        $this->addFieldResult('m', 'WhenFirstRead', 'firstRead');
        $this->addFieldResult('m', 'DeleteRequest', 'deleteRequest');
        $this->addFieldResult('m', 'SpamInfo', 'spamInfo');
        $this->addFieldResult('m', 'Status', 'status');
        $this->addFieldResult('m', 'InFolder', 'folder');
        $this->addMetaResult('m', 'IdParent', 'IdParent');
        $this->addMetaResult('m', 'IdReceiver', 'IdReceiver');
        $this->addMetaResult('m', 'IdSender', 'IdSender');
        $this->addMetaResult('m', 'initiator_id', 'initiator_id');
        $this->addMetaResult('m', 'subject_id', 'subject_id');
        $this->addMetaResult('m', 'request_id', 'request_id');
    }
}
