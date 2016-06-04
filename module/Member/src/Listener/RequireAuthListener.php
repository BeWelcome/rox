<?php

namespace Rox\Member\Listener;

use Rox\Member\Model\Member;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Class RequireAuthListener
 */
class RequireAuthListener
{
    const ROUTE_LOGIN = 'auth/login';

    /**
     * @var UrlGeneratorInterface
     */
    protected $urlGenerator;

    public function __construct(UrlGeneratorInterface $urlGenerator)
    {
        $this->urlGenerator = $urlGenerator;
    }

    public function onKernelRequest(GetResponseEvent $e)
    {
        $request = $e->getRequest();

        $authState = $request->attributes->get('auth_state');
        $route = $request->attributes->get('auth_state_redirect', self::ROUTE_LOGIN);
        $member = $request->attributes->get('member');

        if (!$authState) {
            // Both guest and auth are allowed to view the page.
            return;
        }

        if ($authState === 'auth' && $member instanceof Member) {
            return;
        }

        if ($authState === 'guest' && !$member instanceof Member) {
            return;
        }

        $url = $this->urlGenerator->generate($route);

        $response = new RedirectResponse($url);

        // The request will never make it to the controller as long as we set a
        // response here, but we also want to stop propagation in case another
        // listener replaces the redirect response.
        $e->setResponse($response);

        $e->stopPropagation();
    }
}
