<?php

namespace App\Doctrine;

use Override;

class TypicalOfferType extends SetType
{
    public const DINNER = 'dinner';
    public const GUIDED_TOUR = 'guidedtour';
    public const WHEELCHAIR_ACCESSIBLE = 'CanHostWeelChair';

    #[Override]
    protected string $name = 'typical_offer';

    #[Override]
    protected array $values = [
        self::DINNER,
        self::GUIDED_TOUR,
        self::WHEELCHAIR_ACCESSIBLE,
    ];
}
