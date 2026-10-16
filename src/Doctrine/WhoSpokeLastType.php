<?php

namespace App\Doctrine;

class WhoSpokeLastType extends EnumType
{
    public const string MEMBER = 'Member';
    public const string MODERATOR = 'Moderator';

    protected string $name = 'who_spoke_last';

    protected array $values = [
        self::MEMBER,
        self::MODERATOR,
    ];
}
