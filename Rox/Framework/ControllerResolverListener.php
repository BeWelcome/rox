<?php

namespace Rox\Framework;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\HttpKernel\Event\GetResponseForControllerResultEvent;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Router;

class ControllerResolverListener implements EventSubscriberInterface
{
    /**
     * @var Router
     */
    private $_router;

    /**
     * @var FormFactoryInterface
     */
    private $_formFactory;

    /**
     * ControllerResolverListener constructor.
     * @param Router $router
     * @param FormFactoryInterface $formFactory
     */
    public function __construct(Router $router, FormFactoryInterface $formFactory)
    {
        $this->_router = $router;
        $this->_formFactory = $formFactory;
    }

    public function onControllerResolved(FilterControllerEvent $event)
    {
        $controller= $event->getController();

        if (is_array($controller)) {
            if (!is_object($controller[0])) {
                return;
            }

            if (($controller[0] instanceof \RoxControllerBase) ||
            ($controller[0] instanceof Controller)) {
                $controller[0]->setRouting($this->_router);
                $controller[0]->setFormFactory($this->_formFactory);
            }
            $event->setController($controller);
        }
    }

    public static function getSubscribedEvents()
    {
        return array('kernel.controller' => 'onControllerResolved');
    }
}