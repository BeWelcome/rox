<?php

namespace AppBundle\Doctrine;

class LanguageLevelType extends EnumType
{
    const MOTHER_TONGUE = 'MotherLanguage';
    const EXPERT = 'Expert';
    const FLUENT = 'Fluent';
    const INTERMEDIATE = 'Intermediate';
    const BEGINNER = 'Beginner';
    const HELLO_ONLY = 'HelloOnly';

    protected $name = 'language_level';

    protected $values = [
        self::MOTHER_TONGUE,
        self::EXPERT,
        self::FLUENT,
        self::INTERMEDIATE,
        self::BEGINNER,
        self::HELLO_ONLY,
    ];
}
