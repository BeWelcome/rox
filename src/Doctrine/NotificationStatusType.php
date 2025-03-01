<?php

namespace App\Doctrine;

class NotificationStatusType extends EnumType
{
    public const string SCHEDULED = 'ToSend';
    public const string SENT = 'Sent';
    public const string FROZEN = 'Freeze';

    protected string $name = 'message_status';

    protected array $values = [
        self::SCHEDULED,
        self::SENT,
        self::FROZEN,
    ];
}
