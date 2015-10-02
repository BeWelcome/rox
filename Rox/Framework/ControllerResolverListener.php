<?php

namespace Rox\Framework;
;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\GetResponseForControllerResultEvent;
use Symfony\Component\HttpFoundation\Response;

class ControllerResolverListener implements EventSubscriberInterface
{
    public function onControllerResolved(GetResponseForControllerResultEvent $event)
    {
    }

    public static function getSubscribedEvents()
    {
        return array('kernel.controller' => 'onControllerResolved');
    }
}