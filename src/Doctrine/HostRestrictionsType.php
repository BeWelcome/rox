<?php

namespace App\Doctrine;

class HostRestrictionsType extends SetType
{
    public const string NO_ALCOHOL = 'no.alcohol';
    public const string NO_SMOKING = 'no.smoking';
    public const string NO_DRUGS = 'no.drugs';

    protected string $name = 'host_restrictions';

    protected array $values = [
        self::NO_ALCOHOL,
        self::NO_DRUGS,
        self::NO_SMOKING,
    ];
}
