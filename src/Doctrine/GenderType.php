<?php

namespace App\Doctrine;

class GenderType extends EnumType
{
    public const string MALE = 'male';
    public const string FEMALE = 'female';
    public const string OTHER = 'other';

    protected string $name = 'gender_type';

    protected array $values = [
        self::MALE,
        self::FEMALE,
        self::OTHER,
    ];
}
