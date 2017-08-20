<?php

namespace AppBundle\Doctrine;

class DeleteRequestType extends SetType
{
    protected $name = 'delete_request';
    protected $values = ['SenderDeleted', 'ReceiverDeleted'];
}
