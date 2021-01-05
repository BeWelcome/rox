<?php

namespace App\Doctrine;

use Symfony\Component\Translation\MessageCatalogue;

class DomainType extends EnumType
{
    public const MESSAGES = 'messages';
    public const ICU_MESSAGES = self::MESSAGES . MessageCatalogue::INTL_DOMAIN_SUFFIX;
    public const VALIDATORS = 'validators';

    /** @var string */
    protected $name = 'domain';

    /** @var array */
    protected $values = [
        self::MESSAGES,
        self::ICU_MESSAGES,
        self::VALIDATORS,
    ];
}
