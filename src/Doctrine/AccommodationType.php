<?php

namespace App\Doctrine;

class AccommodationType extends EnumType
{
    public const string YES = 'anytime';
    public const string MAYBE = 'dependonrequest';
    public const string NO = 'neverask';

    protected string $name = 'accommodation';

    protected array $values = [
        self::YES,
        self::MAYBE,
        self::NO,
        null,
    ];
}
