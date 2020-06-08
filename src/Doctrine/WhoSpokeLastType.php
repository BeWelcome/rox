<?php

namespace App\Doctrine;

class WhoSpokeLastType extends EnumType
{
    public const MEMBER = 'Member';
    public const MODERATOR = 'Moderator';

    /** @var string */
    protected $name = 'who_spoke_last';

    /** @var array */
    protected $values = [
        self::MEMBER,
        self::MODERATOR,
    ];
}
