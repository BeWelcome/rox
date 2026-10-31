<?php

namespace App\Doctrine;

use Override;

class ActionToWatchType extends EnumType
{
    public const string REPLIES = 'replies';
    public const string UPDATES = 'updates';

    #[Override]
    protected string $name = 'action_to_watch';

    #[Override]
    protected array $values = [
        self::REPLIES,
        self::UPDATES,
    ];
}
