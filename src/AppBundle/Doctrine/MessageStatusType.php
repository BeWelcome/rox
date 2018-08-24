<?php

namespace AppBundle\Doctrine;

class MessageStatusType extends EnumType
{
    const DRAFT = 'Draft';
    const CHECK = 'ToCheck';
    const CHECKED = 'Checked';
    const SEND = 'ToSend';
    const SENT = 'Sent';
    const FROZEN = 'Freeze';

    protected $name = 'message_status_old';
    protected $values = [
        self::DRAFT,
        self::CHECK,
        self::CHECKED,
        self::SEND,
        self::SENT,
        self::FROZEN,
    ];
}
