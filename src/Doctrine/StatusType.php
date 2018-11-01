<?php

namespace App\Doctrine;

class StatusType extends EnumType
{
    protected $name = 'status';
    protected $values = ['Draft', 'ToCheck', 'ToSend', 'Sent', 'Freeze'];
}
