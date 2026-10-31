<?php

namespace App\Doctrine;

use Override;

class GenderType extends EnumType
{
    public const string MALE = 'male';
    public const string FEMALE = 'female';
    public const string OTHER = 'other';

    #[Override]
    protected string $name = 'gender_type';

    #[Override]
    protected array $values = [
        self::MALE,
        self::FEMALE,
        self::OTHER,
    ];
}
