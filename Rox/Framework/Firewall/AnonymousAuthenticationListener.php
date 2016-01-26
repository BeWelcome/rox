<?php

namespace Rox\Framework\Firewall;


use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\Security\Http\Firewall\ListenerInterface;

class AnonymousAuthenticationListener implements ListenerInterface
{

    /**
     * Just return as authentication is granted
     *
     * @param GetResponseEvent $event
     */
    public function handle(GetResponseEvent $event)
    {
        return ;
    }
}