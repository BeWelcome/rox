<?php

namespace App\Doctrine;

class LanguageLevelType extends EnumType
{
    public const string MOTHER_TONGUE = 'mother.tongue';
    public const string EXPERT = 'expert';
    public const string FLUENT = 'fluent';
    public const string INTERMEDIATE = 'intermediate';
    public const string BEGINNER = 'beginner';
    public const string HELLO_ONLY = 'hello.only';

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
