<?php

namespace App\Doctrine;

class ActionToWatchType extends EnumType
{
    public const string REPLIES = 'replies';
    public const string UPDATES = 'updates';

    protected string $name = 'action_to_watch';

    protected array $values = [
        self::REPLIES,
        self::UPDATES,
    ];
}
