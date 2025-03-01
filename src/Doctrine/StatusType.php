<?php

namespace App\Doctrine;

class StatusType extends EnumType
{
    protected string $name = 'status';

    protected array $values = ['Draft', 'ToCheck', 'ToSend', 'Sent', 'Freeze'];
}
