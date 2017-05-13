<?php

namespace AppBundle\Doctrine;

class DeleteRequestType extends SetType
{
    protected $name = 'deleterequest';
    protected $values = ['SenderDeleted', 'ReceiverDeleted'];
}
