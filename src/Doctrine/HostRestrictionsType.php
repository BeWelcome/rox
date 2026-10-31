<?php

namespace App\Doctrine;

use Override;

class HostRestrictionsType extends SetType
{
    public const string NO_ALCOHOL = 'no.alcohol';
    public const string NO_SMOKING = 'no.smoking';
    public const string NO_DRUGS = 'no.drugs';

    #[Override]
    protected string $name = 'host_restrictions';

    #[Override]
    protected array $values = [
        self::NO_ALCOHOL,
        self::NO_DRUGS,
        self::NO_SMOKING,
    ];
}
