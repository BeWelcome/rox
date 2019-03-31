<?php

namespace App\Doctrine;

class AccommodationType extends EnumType
{
    const ACC_YES = 'anytime';
    const ACC_MAYBE = 'dependonrequest';
    const ACC_NO = 'neverask';

    /** @var string */
    protected $name = 'accommodation';

    /** @var array */
    protected $values = [
        self::ACC_YES,
        self::ACC_MAYBE,
        self::ACC_NO,
    ];
}
