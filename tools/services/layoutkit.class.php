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
            $this->_words = new MOD_words($this->getSession());
        }
        return $this->_words; 
    }
    
    
    public function createWidget($classname)
    {
        $widget = new $classname();
        if (is_a($widget, 'RoxWidget')) {
            $widget->layoutkit = $this;
        }
        return $widget;
    }
    
}


?>