<?php

namespace App\Doctrine;

class NotificationStatusType extends EnumType
{
    public const SCHEDULED = 'ToSend';
    public const SENT = 'Sent';
    public const FROZEN = 'Freeze';

    /** @var string */
    protected $name = 'message_status';

    /** @var array */
    protected $values = [
        self::SCHEDULED,
        self::SENT,
        self::FROZEN,
    ];
}
