<?php

namespace App\Utilities;

use InvalidArgumentException;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class TranslatorSingleton
{
    /**
     * @var TranslatorSingleton The reference to *TranslatorSingleton* instance of this class
     */
    private static $_instance;

    /** @var TranslatorInterface  */
    private $_translator;

    /**
     * Returns the *TranslatorSingleton* instance of this class.
     *
     * @param TranslatorInterface $translator
     * @return TranslatorSingleton The *session* instance.
     */
    public static function createInstance(TranslatorInterface $translator)
    {
        if (null === static::$_instance) {
            static::$_instance = new TranslatorSingleton($translator);
        }

        return static::$_instance;
    }

    /**
     * Returns the *TranslatorSingleton* instance of this class.
     *
     * @return TranslatorSingleton The *session* instance.
     * @throws InvalidArgumentException
     */
    public static function getInstance()
    {
        if (null === static::$_instance) {
            return null;
        }

        return static::$_instance;
    }

    /**
     * Protected constructor to prevent creating a new instance of the
     * *Singleton* via the `new` operator from outside of this class.
     * @param SessionInterface $session
     */
    protected function __construct(TranslatorInterface $translator)
    {
        $this->_translator = $translator;
    }

    /**
     * @return TranslatorInterface
     */
    public static function getTranslator() {
        if (null === static::$_instance) {
            return null;
        }

        return self::getInstance()->_translator;
    }

    /**
     * Private clone method to prevent cloning of the instance of the
     * *Singleton* instance.
     *
     * @return void
     */
    private function __clone()
    {
    }

    /**
     * Private unserialize method to prevent unserializing of the *Singleton*
     * instance.
     *
     * @return void
     */
    private function __wakeup()
    {
    }
}
