<?php

namespace App\Doctrine;

use Override;

class MessageStatusType extends EnumType
{
    public const string DRAFT = 'Draft';
    public const string CHECK = 'ToCheck';
    public const string CHECKED = 'Checked';
    public const string SEND = 'ToSend';
    public const string SENT = 'Sent';
    public const string FROZEN = 'Freeze';

    #[Override]
    protected string $name = 'message_status';

    #[Override]
    protected array $values = [
        self::DRAFT,
        self::CHECK,
        self::CHECKED,
        self::SEND,
        self::SENT,
        self::FROZEN,
    ];
}
