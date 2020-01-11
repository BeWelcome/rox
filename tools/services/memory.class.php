<?php

use App\Utilities\SessionTrait;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class SessionMemory
{
    use SessionTrait;

    private $_store = array();
    private $_session_key = false;

    public function __construct($session_key)
    {
        $this->setSession();
        $this->_session_key = $session_key;
        if ($this->session->has($session_key)) {
            $this->_store = unserialize($this->session->get($session_key));
            if (!is_array($this->_store)) {
                $this->_store = array();
            }
        }
/*
         if ($this->session->has( $session_key ) {
            $this->_store = unserialize($this->session->get($session_key));
            if (!is_array($this->_store)) {
                $this->_store = array();
            }
        }
*/
    }

    public function __get($key)
    {
        if (isset($this->_store[$key])) {
            return $this->_store[$key];
        } else {
            return false;
        }
    }

    public function __set($key, $value)
    {
        $this->_store[$key] = $value;
        $this->_save();
    }

    private function _save()
    {
        $this->session->set($this->_session_key, serialize($this->_store));
    }
}



?>
