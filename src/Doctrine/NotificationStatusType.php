<?php

namespace App\Doctrine;

use Override;

class NotificationStatusType extends EnumType
{
    public const string SCHEDULED = 'ToSend';
    public const string SENT = 'Sent';
    public const string FROZEN = 'Freeze';

    #[Override]
    protected string $name = 'message_status';

    #[Override]
    protected array $values = [
        self::SCHEDULED,
        self::SENT,
        self::FROZEN,
    ];
}
