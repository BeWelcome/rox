<?php

namespace App\Doctrine;

class AccommodationType extends EnumType
{
    public const string YES = 'yes';
    public const string NO = 'no';

    protected string $name = 'accommodation';

    protected array $values = [
        self::YES,
        self::NO,
        null,
    ];
}
