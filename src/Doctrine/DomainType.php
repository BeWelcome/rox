<?php

namespace App\Doctrine;

use Symfony\Component\Translation\MessageCatalogue;

class DomainType extends EnumType
{
    const MESSAGES = 'messages';
    const ICU_MESSAGES = self::MESSAGES . MessageCatalogue::INTL_DOMAIN_SUFFIX;
    const VALIDATORS = 'validators';

    /** @var string */
    protected $name = 'domain';

    /** @var array */
    protected $values = [
        self::MESSAGES,
        self::ICU_MESSAGES,
        self::VALIDATORS,
    ];
}
