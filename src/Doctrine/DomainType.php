<?php

namespace App\Doctrine;

use Symfony\Component\Translation\MessageCatalogueInterface;

class DomainType extends EnumType
{
    public const string MESSAGES = 'messages';
    public const string ICU_MESSAGES = self::MESSAGES . MessageCatalogueInterface::INTL_DOMAIN_SUFFIX;
    public const string VALIDATORS = 'validators';

    protected string $name = 'domain';

    protected array $values = [
        self::MESSAGES,
        self::ICU_MESSAGES,
        self::VALIDATORS,
    ];
}
