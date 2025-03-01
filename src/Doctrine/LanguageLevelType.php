<?php

namespace App\Doctrine;

class LanguageLevelType extends EnumType
{
    public const string MOTHER_TONGUE = 'MotherLanguage';
    public const string EXPERT = 'Expert';
    public const string FLUENT = 'Fluent';
    public const string INTERMEDIATE = 'Intermediate';
    public const string BEGINNER = 'Beginner';
    public const string HELLO_ONLY = 'HelloOnly';

    protected string $name = 'language_level';

    protected array $values = [
        self::MOTHER_TONGUE,
        self::EXPERT,
        self::FLUENT,
        self::INTERMEDIATE,
        self::BEGINNER,
        self::HELLO_ONLY,
    ];
}
