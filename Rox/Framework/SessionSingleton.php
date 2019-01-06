<?php

namespace Rox\Framework;


use InvalidArgumentException;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class SessionSingleton
{
    /**
     * @var SessionSingleton The reference to *SessionSingleton* instance of this class
     */
    private static $_instance;

    /** @var SessionInterface  */
    private $_session;

    /**
     * Returns the *SessionSingleton* instance of this class.
     *
     * @param SessionInterface $session
     * @return SessionSingleton The *session* instance.
     */
    public static function createInstance(SessionInterface $session)
    {
        if (null === static::$_instance) {
            static::$_instance = new SessionSingleton($session);
        }

        return static::$_instance;
    }

    /**
     * Returns the *SessionSingleton* instance of this class.
     *
     * @return SessionSingleton The *session* instance.
     * @throws InvalidArgumentException
     */
    public static function getInstance()
    {
        if (null === static::$_instance) {
            throw new \InvalidArgumentException('SessionSingleton::getInstance() called without a call to createInstance()');
        }

        return static::$_instance;
    }

    /**
     * Protected constructor to prevent creating a new instance of the
     * *Singleton* via the `new` operator from outside of this class.
     * @param SessionInterface $session
     */
    protected function __construct(SessionInterface $session)
    {
        $this->_session = $session;
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
