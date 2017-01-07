<?php

namespace Rox\RoxTraits;

use Rox\Framework\SessionSingleton;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

trait SessionTrait {
    /** @var  Session */
    public $_session;

    protected function setSession() {
        $this->_session = SessionSingleton::getSession();
    }

    /**
     * @return Session
     */
    protected function getSession() {
        return $this->_session;
    }
}