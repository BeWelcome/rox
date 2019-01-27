<?php

namespace App\Doctrine;

class StatusType extends EnumType
{
    /** @var string */
    protected $name = 'status';

    /** @var array */
    protected $values = ['Draft', 'ToCheck', 'ToSend', 'Sent', 'Freeze'];
}
