<?php


abstract class RoxControllerBase extends PAppController
{
    private $_injected_parameters = array();
    
    public function inject($key, $value) {
        $this->_injected_parameters[$key] = $value;
    }
    
    protected function get($key) {
        if (isset($this->_injected_parameters[$key])) {
            return $this->_injected_parameters[$key];
        } else {
            return false;
        }
    }
    
    protected function redirect($new_request_string) {
        $this->redirectAbsolute(PVars::getObj('env')->baseuri . $new_request_string);
    }
    
    protected function redirectHome($extra_args = false) {
        $this->redirect('index', $extra_args);
    }
    
    protected function redirectRefresh() {
        $this->redirect(implode('/',$this->get('request')));
    }
    
    protected function redirectAbsolute($url) {
        header('Location: ' . $url);
        PVars::getObj('page')->output_done = true;
    }

    public function __toString()
    {
        return serialize($this);
    }
}


?>