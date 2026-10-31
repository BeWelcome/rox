<?php

namespace App\Doctrine;

use Override;

class StandardOffersType extends SetType
{
    public const string DINNER = 'dinner';
    public const string GUIDED_TOUR = 'guidedtour';

    #[Override]
    protected string $name = 'standard_offers';

    #[Override]
    protected array $values = [
        self::DINNER,
        self::GUIDED_TOUR,
    ];
}
