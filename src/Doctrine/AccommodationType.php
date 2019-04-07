<?php

namespace App\Doctrine;

class AccommodationType extends EnumType
{
    const YES = 'anytime';
    const MAYBE = 'dependonrequest';
    const NO = 'neverask';

    /** @var string */
    protected $name = 'accommodation';

    /** @var array */
    protected $values = [
        self::YES,
        self::MAYBE,
        self::NO,
    ];
}
