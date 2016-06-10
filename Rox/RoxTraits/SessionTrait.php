<?php

namespace Rox\RoxTraits;

use Rox\Framework\SessionSingleton;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

trait SessionTrait {
    /** @var  Session */
    private $_session;

    /**
     * @param SessionInterface $session
     */
    public function setSession() {
        $this->_session = SessionSingleton::getSession();
    }

    /**
     * @return Session
     */
    protected function getSession() {
        return $this->_session;
    }
}