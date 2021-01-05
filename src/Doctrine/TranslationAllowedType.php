<?php

namespace App\Doctrine;

class TranslationAllowedType extends EnumType
{
    // This is reversed as the database uses a field named do_not_translate!
    public const TRANSLATION_NOT_ALLOWED = 'yes';
    public const TRANSLATION_ALLOWED = 'no';

    /** @var string */
    protected $name = 'translation_allowed';

    /** @var array */
    protected $values = [
        self::TRANSLATION_NOT_ALLOWED,
        self::TRANSLATION_ALLOWED,
    ];
}
