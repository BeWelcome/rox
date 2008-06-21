<?php


abstract class RoxControllerBase extends RoxComponentBase
{
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
}


?>