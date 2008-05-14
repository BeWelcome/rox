<?php


abstract class AbstractBasePage extends ObjectWithInjection
{
    private $_words = 0;
    private $_model = 0;
    
    /**
     * some view classes need to store a model object.
     */
    public function setModel($model)
    {
        $this->_model = $model;
    }
    
    /**
     * Get the model object that was stored using setModel
     */
    protected function getModel()
    {
        return $this->_model;
    }
    
    protected function getWords()
    {
        if (!$this->_words) {
            $this->_words = new MOD_words();
        }
        return $this->_words; 
    }
    
    
    public function createWidget($classname)
    {
        $widget = new $classname();
        if (is_a($widget, 'RoxWidget')) {
            if ($this->callbackRegistryService) {
                $widget->callbackRegistryService = $this->callbackRegistryService;
            } else if ($this->layoutkit) {
                $widget->callbackRegistryService = $this->layoutkit->callbackRegistryService;
            }
            if ($this->layoutkit) {
                $widget->layoutkit = $this->layoutkit;
            }
        }
        return $widget;
    }
    
    
    protected function showTemplate($rel_path, $args=array())
    {
        $args['words'] = $this->getWords();
        $template = new RoxTemplate($rel_path, $args);
        $template->render();
    }
    
    public abstract function render();
}


?>