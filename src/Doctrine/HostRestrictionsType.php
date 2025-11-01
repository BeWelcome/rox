<?php

namespace App\Doctrine;

class HostRestrictionsType extends SetType
{
    public const string NO_ALCOHOL = 'NoAlcohol';
    public const string NO_SMOKING = 'NoSmoking';
    public const string NO_DRUGS = 'NoDrugs';

    protected string $name = 'host_restrictions';

    protected array $values = [
        self::NO_ALCOHOL,
        self::NO_DRUGS,
        self::NO_SMOKING,
    ];
}
