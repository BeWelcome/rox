<?php

namespace App\Doctrine;

use Override;

class WhoSpokeLastType extends EnumType
{
    public const string MEMBER = 'Member';
    public const string MODERATOR = 'Moderator';

    #[Override]
    protected string $name = 'who_spoke_last';

    #[Override]
    protected array $values = [
        self::MEMBER,
        self::MODERATOR,
    ];
}
