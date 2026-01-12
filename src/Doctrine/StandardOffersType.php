<?php

namespace App\Doctrine;

class StandardOffersType extends SetType
{
    public const string DINNER = 'dinner';
    public const string GUIDED_TOUR = 'guidedtour';

    protected string $name = 'standard_offers';

    protected array $values = [
        self::DINNER,
        self::GUIDED_TOUR,
    ];
}
