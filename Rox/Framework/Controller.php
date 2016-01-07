<?php

namespace Rox\Framework;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\HttpFoundation\RedirectResponse;

class Controller
{
    /** @var \Symfony\Component\Routing\Router */
    private $_routing = null;

    /** @var  \Symfony\Component\Form\FormFactoryInterface */
    private $_formFactory = null;

    /**
     * @RoxModelBase Rox
     */
    private $_model;

    public function __destruct()
    {
        unset($_model);
    }

    /**
     * @param \Symfony\Component\Routing\Router $routing
     *
     * @return Controller
     */
    public function setRouting($routing)
    {
        $this->_routing = $routing;

        return $this;
    }

    /**
     * Gets the associated Router object
     * @return \Symfony\Component\Routing\Router
     */
    protected function getRouting()
    {
        return $this->_routing;
    }

    /**
     * @param \Symfony\Component\Form\FormFactoryInterface $formFactory
     *
     * @return Controller
     */
    public function setFormFactory($formFactory)
    {
        $this->_formFactory = $formFactory;

        return $this;
    }

    /**
     * @return \Symfony\Component\Form\FormFactoryInterface
     */
    public function getFormFactory()
    {
        return $this->_formFactory;
    }

    /**
     * @param \RoxModelBase $model
     *
     * @return Controller
     */
    public function setModel($model)
    {
        $this->_model = $model;

        return $this;
    }

    /**
     * @return \RoxModelBase
     */
    public function getModel()
    {
        return $this->_model;
    }

    public function redirect($url, $status = 302)
    {
        return new RedirectResponse($url, $status);
    }

    /**
     * @param AbstractType $
     */
    protected function createForm($formType)
    {
        $form = $this->_formFactory->create($formType, null, []);
        return $form;
    }
}