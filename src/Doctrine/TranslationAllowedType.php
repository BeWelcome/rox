<?php

namespace App\Doctrine;

use Override;

class TranslationAllowedType extends EnumType
{
    // This is reversed as the database uses a field named do_not_translate!
    public const string TRANSLATION_NOT_ALLOWED = 'yes';
    public const string TRANSLATION_ALLOWED = 'no';

    #[Override]
    protected string $name = 'translation_allowed';

    #[Override]
    protected array $values = [
        self::TRANSLATION_NOT_ALLOWED,
        self::TRANSLATION_ALLOWED,
    ];
}
