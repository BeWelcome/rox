<?php

namespace App\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\RateLimiter\RateLimiterFactory;
use Twig\Environment;

/**
 * Limits sign-ups per address. The legacy sign-up (build/signup) asks for username, email address and password on
 * its first step: every submission of that step counts, also invalid ones, so bots trying many names from one
 * address are stopped too. Once the limit is reached the step answers 429 instead of being processed.
 */
class SignupRateLimitSubscriber implements EventSubscriberInterface
{
    /** The legacy routes of the first step (src/Routing/LegacyLoader.php). */
    public const FIRST_STEP_ROUTES = ['signup', 'signup_1'];

    public function __construct(
        private readonly RateLimiterFactory $signupIpLimiter,
        private readonly Environment $twig,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        // after the router (priority 32), which sets the route
        return [KernelEvents::REQUEST => ['onKernelRequest', 0]];
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        $request = $event->getRequest();
        if (!$event->isMainRequest() || !$request->isMethod('POST')) {
            return;
        }
        if (!\in_array($request->attributes->get('_route'), self::FIRST_STEP_ROUTES, true)) {
            return;
        }

        if ($this->signupIpLimiter->create($request->getClientIp())->consume()->isAccepted()) {
            return;
        }

        $event->setResponse(new Response(
            $this->twig->render('signup/too_many.html.twig'),
            Response::HTTP_TOO_MANY_REQUESTS
        ));
    }
}
