<?php

namespace App\Doctrine;

use Override;

class StatusType extends EnumType
{
    #[Override]
    protected string $name = 'status';

    #[Override]
    protected array $values = ['Draft', 'ToCheck', 'ToSend', 'Sent', 'Freeze'];
}
