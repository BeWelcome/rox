<?php

namespace App\Utilities;

use Symfony\Component\HttpFoundation\Session\Session;

trait SessionTrait
{
    /** @var Session */
    protected $session;

    /**
     * @SuppressWarnings(PHPMD.StaticAccess)
     */
    protected function setSession()
    {
        $this->session = SessionSingleton::getSession();
    }

    /**
     * @return Session
     */
    protected function getSession()
    {
        return $this->session;
    }
}
