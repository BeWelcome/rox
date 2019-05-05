<?php

namespace App\Doctrine;

class MessageStatusType extends EnumType
{
    const DRAFT = 'Draft';
    const CHECK = 'ToCheck';
    const CHECKED = 'Checked';
    const SEND = 'ToSend';
    const SENT = 'Sent';
    const FROZEN = 'Freeze';

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
