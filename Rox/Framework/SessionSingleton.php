<?php

namespace Rox\Framework;


use Symfony\Component\HttpFoundation\Session\Session;

class SessionSingleton
{
    /**
     * @var SessionSingleton The reference to *SessionSingleton* instance of this class
     */
    private static $_instance;

    /** @var Session  */
    private $_session;

    /**
     * Returns the *SessionSingleton* instance of this class.
     *
     * @return SessionSingleton The *session* instance.
     */
    public static function getInstance()
    {
        if (null === static::$_instance) {
            static::$_instance = new SessionSingleton();
        }

        return static::$_instance;
    }

    /**
     * Protected constructor to prevent creating a new instance of the
     * *Singleton* via the `new` operator from outside of this class.
     */
    protected function __construct()
    {
        $this->_session = new Session();
    }

    public static function getSession() {
        return self::getInstance()->_session;
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