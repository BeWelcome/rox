<?php

namespace App\Doctrine;

class MessageStatusType extends EnumType
{
    public const DRAFT = 'Draft';
    public const CHECK = 'ToCheck';
    public const CHECKED = 'Checked';
    public const SEND = 'ToSend';
    public const SENT = 'Sent';
    public const FROZEN = 'Freeze';

    /** @var string */
    protected $name = 'message_status';

    /** @var array */
    protected $values = [
        self::DRAFT,
        self::CHECK,
        self::CHECKED,
        self::SEND,
        self::SENT,
        self::FROZEN,
    ];
}
