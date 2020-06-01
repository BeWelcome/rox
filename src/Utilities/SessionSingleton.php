<?php

namespace App\Utilities;

use InvalidArgumentException;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

/**
 * Class SessionSingleton.
 */
class SessionSingleton
{
    /**
     * @var SessionSingleton The reference to *SessionSingleton* instance of this class
     */
    private static $instance;

    /** @var SessionInterface */
    private $session;

    /**
     * Protected constructor to prevent creating a new instance of the
     * *Singleton* via the `new` operator from outside of this class.
     */
    protected function __construct(SessionInterface $session)
    {
        $this->session = $session;
    }

    /**
     * Returns the *SessionSingleton* instance of this class.
     *
     * @return SessionSingleton the *session* instance
     */
    public static function createInstance(SessionInterface $session)
    {
        if (null === static::$instance) {
            static::$instance = new self($session);
        }

        return static::$instance;
    }

    /**
     * Returns the *SessionSingleton* instance of this class.
     *
     * @throws InvalidArgumentException
     *
     * @return SessionSingleton the *session* instance
     */
    public static function getInstance()
    {
        if (null === static::$instance) {
            throw new InvalidArgumentException('SessionSingleton::getInstance() called without a call to createInstance()');
        }

        return static::$instance;
    }

    /**
     * @return SessionInterface
     *
     * @SuppressWarnings(PHPMD.StaticAccess)
     */
    public static function getSession()
    {
        return self::getInstance()->session;
    }
}
