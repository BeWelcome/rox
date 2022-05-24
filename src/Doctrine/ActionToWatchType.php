<?php

namespace App\Doctrine;

class ActionToWatchType extends EnumType
{
    public const REPLIES = 'replies';
    public const UPDATES = 'updates';
    public const NO = 'neverask';

    /** @var string */
    protected $name = 'action_to_watch';

    /** @var array */
    protected $values = [
        self::REPLIES,
        self::UPDATES,
    ];
}
