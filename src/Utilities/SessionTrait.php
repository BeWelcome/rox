<?php

namespace App\Utilities;

use Symfony\Component\HttpFoundation\Session\Session;

trait SessionTrait
{
    /** @var Session */
    protected $_session;

    /**
     * @SuppressWarnings(PHPMD.StaticAccess)
     */
    protected function setSession()
    {
        $this->_session = SessionSingleton::getSession();
    }

    /**
     * @return Session
     */
    protected function getSession()
    {
        return $this->_session;
    }
}
