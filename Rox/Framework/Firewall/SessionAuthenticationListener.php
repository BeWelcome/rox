<?php

namespace Rox\Framework\Firewall;


use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\Security\Http\Firewall\ListenerInterface;

class SessionAuthenticationListener implements ListenerInterface
{

    /**
     * Checks the session if there is a remember me token and authenticates that
     * member
     *
     * @param GetResponseEvent $event
     */
    public function handle(GetResponseEvent $event)
    {
        $request = $event->getRequest();
        if ($request->getPathInfo() != '/login_symfony') {
            $model = new \RoxModelBase();
            $member = $model->getLoggedInMember();
            if ($member == false) {
                $event->setResponse(new RedirectResponse('/login'));
            }
        }
    }
}