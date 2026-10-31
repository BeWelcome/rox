<?php

namespace App\Doctrine;

use Override;

class AccommodationType extends EnumType
{
    public const string YES = 'yes';
    public const string NO = 'no';

    #[Override]
    protected string $name = 'accommodation';

    #[Override]
    protected array $values = [
        self::YES,
        self::NO,
        null,
    ];
}
