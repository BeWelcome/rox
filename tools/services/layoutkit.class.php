<?php


class Layoutkit extends ReadWriteObject
{
    public function showTemplate($rel_path, $args=array())
    {
        $args['words'] = $this->getWords();
        $template = new RoxTemplate($rel_path, $args);
        $template->render();
    }
    
    private $_words;
    public function getWords()
    {
        if (!$this->_words) {
            $this->_words = new MOD_words();
        }
        return $this->_words; 
    }
    
    
    public function registerPosthandlerCallback($classname, $methodname, $extra_args = array())
    {
        if (!method_exists($classname, $methodname)) {
            echo __METHOD__.' - method '.$classname.'::'.$methodname.' does not exist!';
        }
        if ($creg = $this->callbackRegistryService) {
            return $creg->registerCallbackMethod($classname, $methodname, $extra_args);
        } else {
            return '<!-- where is the CallbackRegistryService? -->';
        }
    }
    
    public function createWidget($classname)
    {
        $widget = new $classname();
        if (is_a($widget, 'RoxWidget')) {
            $widget->callbackRegistryService = $this->callbackRegistryService;
        }
        return $widget;
    }
    
}


?>