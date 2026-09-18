<?php

namespace App\Utilities;

use InvalidArgumentException;
use Symfony\Contracts\Translation\TranslatorInterface;

final class TranslatorSingleton
{
    /**
     * @var TranslatorSingleton The reference to *TranslatorSingleton* instance of this class
     */
    private static $instance;

    /**
     * Protected constructor to prevent creating a new instance of the
     * *Singleton* via the `new` operator from outside of this class.
     */
    private function __construct(
        private readonly TranslatorInterface $translator,
    ) {
    }

    /**
     * Returns the *TranslatorSingleton* instance of this class.
     *
     * @SuppressWarnings("PHPMD.StaticAccess")
     */
    public static function createInstance(TranslatorInterface $translator): self
    {
        if (null === self::$instance) {
            self::$instance = new self($translator);
        }

        return self::$instance;
    }

    /**
     * Returns the *TranslatorSingleton* instance of this class.
     *
     * @throws InvalidArgumentException
     */
    public static function getInstance(): ?self
    {
        if (null === self::$instance) {
            return null;
        }

        return self::$instance;
    }

    /**
     * @SuppressWarnings("PHPMD.StaticAccess")
     */
    public static function getTranslator(): ?TranslatorInterface
    {
        if (null === self::$instance) {
            return null;
        }

        return self::getInstance()->translator;
    }
}
