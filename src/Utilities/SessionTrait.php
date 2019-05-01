<?php

namespace App\Utilities;

use Symfony\Component\HttpFoundation\Session\Session;

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