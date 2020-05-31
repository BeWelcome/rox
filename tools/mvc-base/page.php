<?php

use Symfony\Component\Templating\EngineInterface;
use Twig\Environment;

abstract class AbstractBasePage extends VisualComponent
{
    private $_words = 0;
    private $_model = 0;

    /**
     * @var Environment
     */
    protected $environment;

    public function setEnvironment(Environment $environment)
    {
        $this->environment = $environment;

        return $this;
    }

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

