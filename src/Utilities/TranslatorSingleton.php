<?php

namespace App\Utilities;

use InvalidArgumentException;
use Symfony\Contracts\Translation\TranslatorInterface;

class TranslatorSingleton
{
    /**
     * @var TranslatorSingleton The reference to *TranslatorSingleton* instance of this class
     */
    private static $instance;

    /** @var TranslatorInterface */
    private $translator;

    /**
     * Protected constructor to prevent creating a new instance of the
     * *Singleton* via the `new` operator from outside of this class.
     */
    protected function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    /**
     * Returns the *TranslatorSingleton* instance of this class.
     *
     * @return TranslatorSingleton the *session* instance
     *
     * @SuppressWarnings(PHPMD.StaticAccess)
     */
    public static function createInstance(TranslatorInterface $translator)
    {
        if (null === static::$instance) {
            static::$instance = new self($translator);
        }

        return static::$instance;
    }

    /**
     * Returns the *TranslatorSingleton* instance of this class.
     *
     * @throws InvalidArgumentException
     *
     * @return TranslatorSingleton the *session* instance
     */
    public static function getInstance()
    {
        if (null === static::$instance) {
            return null;
        }

        return static::$instance;
    }

    /**
     * @return TranslatorInterface
     *
     * @SuppressWarnings(PHPMD.StaticAccess)
     */
    public static function getTranslator()
    {
        if (null === static::$instance) {
            return null;
        }

        return self::getInstance()->translator;
    }
}
