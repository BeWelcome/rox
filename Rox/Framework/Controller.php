<?php

namespace Rox\Framework;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class Controller
{
    /** @var \Symfony\Component\Routing\Router */
    private $_router = null;

    /** @var  \Symfony\Component\Form\FormFactoryInterface */
    private $_formFactory = null;

    /**
     * @RoxModelBase Rox
     */
    private $_model;

    /** @var  SessionInterface */
    private $_session;

    public function __destruct()
    {
        unset($_model);
    }

    /**
     * @param \Symfony\Component\Routing\Router $routing
     *
     * @return Controller
     */
    public function setRouter($router)
    {
        $this->_router = $router;

        return $this;
    }

    /**
     * Gets the associated Router object
     * @return \Symfony\Component\Routing\Router
     */
    protected function getRouter()
    {
        return $this->_router;
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

    /**
     * @param SessionInterface $session
     */
    public function setSession(SessionInterface $session) {
        $this->_session = $session;
    }

    /**
     * @return SessionInterface
     */
    protected function getSession() {
        return $this->_session;
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