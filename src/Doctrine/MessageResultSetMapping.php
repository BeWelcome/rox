<?php

namespace App\Doctrine;

use Doctrine\ORM\Query\ResultSetMapping;

class MessageResultSetMapping extends ResultSetMapping
{
    public function __construct()
    {
        $this->addEntityResult('App:Message', 'm');
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
        $this->addMetaResult('m', 'idParent', 'idParent');
        $this->addMetaResult('m', 'idReceiver', 'idReceiver');
        $this->addMetaResult('m', 'idSender', 'idSender');
        $this->addMetaResult('m', 'initiator_id', 'initiator_id');
        $this->addMetaResult('m', 'subject_id', 'subject_id');
        $this->addMetaResult('m', 'request_id', 'request_id');
    }
}
