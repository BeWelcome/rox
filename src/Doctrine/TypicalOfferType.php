<?php

namespace App\Doctrine;

class TypicalOfferType extends SetType
{
    public const DINNER = 'dinner';
    public const GUIDED_TOUR = 'guidedtour';
    public const WHEELCHAIR_ACCESSIBLE = 'CanHostWeelChair';

    /** @var string */
    protected $name = 'typical_offer';

    /** @var array */
    protected $values = [
        self::DINNER,
        self::GUIDED_TOUR,
        self::WHEELCHAIR_ACCESSIBLE,
    ];
}
