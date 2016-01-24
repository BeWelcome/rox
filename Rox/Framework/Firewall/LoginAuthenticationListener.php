<?php

namespace Rox\Framework\Firewall;


use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\Security\Http\Firewall\ListenerInterface;

class LoginAuthenticationListener implements ListenerInterface
{

    /**
     * Handles the authentication based on arequest with credentials from the
     * login form
     *
     * @param GetResponseEvent $event
     */
    public function handle(GetResponseEvent $event)
    {
        // get credentials from the request
        $request = $event->getRequest();

        $loginController = new \LoginController();
        $args = new \stdClass();
        $args->post = array(
            'u' => $request->request->get('u'),
            'p' => $request->request->get('p')
        );
        $args->request = $request->getPathInfo();

        // Use old login controller for the login magic for now
        $result = $loginController->loginCallback($args, null, null);
        if ($result) {
            $event->setResponse(new RedirectResponse('/'));
        }
    }
}