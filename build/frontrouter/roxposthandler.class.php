<?php


/**
 * The PPostHandler sucks, so we make a new one.
 * Unlike the singleton PPostHandler, this one will be nicest dynamic OOP.
 * 
 * The handler will be called from RoxLauncher.
 */
class RoxPostHandler
{
    private $_registered_callbacks = false;
    
    public function load()
    {
        if (!isset($_SESSION['RoxPostHandler'])) {
            $this->_registered_callbacks = array();  
        } else {
            if (!$look = unserialize($_SESSION['RoxPostHandler'])) {
                $this->_registered_callbacks = array();
            } else if (!is_array($look)) {
                $this->_registered_callbacks = array();
            } else {
                $this->_registered_callbacks = $look;
            }
        }
    }
    
    public function save()
    {
        $_SESSION['RoxPostHandler'] = serialize($this->_registered_callbacks);
    }
    
    public function getCallbackMethod($key_on_page)
    {
        $key_in_table = PFunctions::hex2base64(sha1($key_on_page));
        if (isset($this->_registered_callbacks[$key_in_table])) {
            $result = $this->_registered_callbacks[$key_in_table];
            unset($this->_registered_callbacks[$key_in_table]);
            return $result;
        } else {
            return false;
        }
    }
    
    public function setCallbackMethod($classname, $methodname)
    {
        if (!$this->_registered_callbacks) {
            $this->_registered_callbacks = array();
        }
        
        do {
            $random_string = PFunctions::randomString(42); 
            $key_on_page = PFunctions::hex2base64(sha1($classname.$random_string.$methodname));
            $key_in_table = PFunctions::hex2base64(sha1($key_on_page));
        } while (
            // try to avoid duplicates
            isset($this->_registered_callbacks[$key_in_table])
        );
        
        $this->_registered_callbacks[$key_in_table] = array($classname, $methodname);
        return $key_on_page;
    }
}


?>