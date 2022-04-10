<?php

namespace App\Doctrine;

class LanguageLevelType extends EnumType
{
    public const MOTHER_TONGUE = 'MotherLanguage';
    public const EXPERT = 'Expert';
    public const FLUENT = 'Fluent';
    public const INTERMEDIATE = 'Intermediate';
    public const BEGINNER = 'Beginner';
    public const HELLO_ONLY = 'HelloOnly';

    /** @var string */
    protected $name = 'language_level';

    /** @var array */
    protected $values = [
        self::MOTHER_TONGUE,
        self::EXPERT,
        self::FLUENT,
        self::INTERMEDIATE,
        self::BEGINNER,
        self::HELLO_ONLY,
    ];
}
