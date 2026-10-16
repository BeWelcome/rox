<?php

namespace App\Doctrine;

class TypicalOfferType extends SetType
{
    public const string DINNER = 'dinner';
    public const string GUIDED_TOUR = 'guidedtour';
    public const string WHEELCHAIR_ACCESSIBLE = 'CanHostWeelChair';

    protected string $name = 'typical_offer';

    protected array $values = [
        self::DINNER,
        self::GUIDED_TOUR,
        self::WHEELCHAIR_ACCESSIBLE,
    ];
}
