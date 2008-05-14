<?php


class Formkit extends ObjectWithInjection
{
    public function getMemFromRedirect()
    {
        return $this->mem_from_redirect;
    }
    
    public function setPostCallback($classname, $methodname, $extra_args = array())
    {
        if (!method_exists($classname, $methodname)) {
            echo '
            '.__METHOD__.'<br>
            callback method '.$classname.'::'.$methodname.'<br>
            does not exist!';
        } else {
            return $this->posthandler->registerCallbackMethod($classname, $methodname, $extra_args);
        }
    }
    
    public function setMemForRecovery($memory = false)
    {
        if (!$memory) $memory = $this->getMemFromRedirect();
        if (!$memory) {
            // no memory stuff
            return '';
        } else {
            if ($memory->prev) {
                $memory_tag_value = htmlspecialchars($memory->prev);
            } else {
                $memory_tag_value = htmlspecialchars(addslashes(serialize($memory)));
            }
            return '
            <input type="hidden" name="formkit_memory_recovery" value="'.$memory_tag_value.'"/>';
        }
    }
    
    public function setRedirect($req)
    {
        return '
        <input type="hidden" name="formkit_redirect_req" value="'.$req.'"/>';
    }
}



?>