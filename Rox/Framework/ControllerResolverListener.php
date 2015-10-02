<?php

namespace Rox\Framework;
;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
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

    public function __construct(Router $router)
    {
        $this->_router = $router;
    }

    public function onControllerResolved(FilterControllerEvent $event)
    {
        $controller= $event->getController();

        if (is_array($controller)) {
            if (!is_object($controller[0])) {
                return;
            }

            $controller[0]->router = $this->_router;
            $event->setController($controller);
        }
    }

    public static function getSubscribedEvents()
    {
        return array('kernel.controller' => 'onControllerResolved');
    }
}