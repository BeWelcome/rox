<?php

namespace App\Doctrine;

class AccommodationType extends EnumType
{
    public const YES = 'anytime';
    public const MAYBE = 'dependonrequest';
    public const NO = 'neverask';

    /** @var string */
    protected $name = 'accommodation';

    /** @var array */
    protected $values = [
        self::YES,
        self::MAYBE,
        self::NO,
    ];
}
