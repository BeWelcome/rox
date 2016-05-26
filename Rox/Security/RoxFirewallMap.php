<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Rox\Security;
use Symfony\Component\HttpFoundation\RequestMatcher;
use Symfony\Component\HttpKernel\HttpKernel;
use Symfony\Component\Routing\Matcher\RequestMatcherInterface;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\Router;
use Symfony\Component\Security\Core\Authentication\AuthenticationProviderManager;
use Symfony\Component\Security\Core\Authentication\AuthenticationTrustResolver;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use Symfony\Component\Security\Http\FirewallMap;
use Symfony\Component\Security\Http\HttpUtils;
use Symfony\Component\Security\Http\Session\SessionAuthenticationStrategy;

/**
 * FirewallMap allows configuration of different firewalls for specific parts
 * of the website.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
class RoxFirewallMap extends FirewallMap
{
    /**
     * RoxFirewallMap constructor.
     *
     * Sets up the firewall for the Rox framework.
     *
     * Basically there are three areas to protect
     * - The start page / which can be accessed anonymously
     * - Everything below /admin needs special roles
     * - All other pages can be accessed with a logged in user
     *
     * @param HttpKernel     $httpKernel
     * @param Router         $router
     * @param RequestContext $context
     */
    public function __construct(HttpKernel $httpKernel, Router $router, RequestContext $context)
    {
        $tokenStorage = new TokenStorage();
        $trustResolver = new AuthenticationTrustResolver(
            'Rox\Security\Token\AnonymousToken',
            'Rox\Security\Token\RememberMeToken'
        );

        $sessionStrategy = new SessionAuthenticationStrategy(SessionAuthenticationStrategy::NONE);

        $authenticationManager = new AuthenticationProviderManager([
            new RoxAuthenticationProvider(new RoxUserProvider(), '')
        ]);

        $matcher = new UrlMatcher($router->getRouteCollection(), $context);
        $adminHttpUtils = new HttpUtils($router->getGenerator(), $matcher);
        $adminAuthenticationListener = new AdminAuthenticationListener(
            $tokenStorage,
            $authenticationManager,
            $sessionStrategy,
            $adminHttpUtils,
            'bewelcome',
            new RoxAuthenticationSuccessHandler(), new RoxAuthenticationFailureHandler($httpKernel, $adminHttpUtils),
            [
                'check_path' => '^/admin'
            ]
        );
        $adminExceptionListener = new AdminExceptionListener($tokenStorage, $trustResolver);

        $siteHttpUtils = new HttpUtils($router->getGenerator(), $matcher);
        $siteAuthenticationListener = new AdminAuthenticationListener(
            $tokenStorage,
            $authenticationManager,
            $sessionStrategy,
            $siteHttpUtils,
            'bewelcome',
            new RoxAuthenticationSuccessHandler(),
            new RoxAuthenticationFailureHandler(),
            [
                'check_path' => '^/.+'
            ]
        );
        $siteExceptionListener = new SiteExceptionListener($tokenStorage, $trustResolver);

        $startMatcher = new RequestMatcher('^/$');
        $startHttpUtils = new HttpUtils($router->getGenerator(), $matcher);
        $startAuthenticationListener = new AdminAuthenticationListener(
            $tokenStorage,
            $authenticationManager,
            $sessionStrategy,
            $startHttpUtils,
            'bewelcome',
            new RoxAuthenticationSuccessHandler(),
            new RoxAuthenticationFailureHandler(), [
            'check_path' => '^/$'
        ]);
        $startExceptionListener = new StartExceptionListener($tokenStorage, $trustResolver);
        $this->add($matcher, [$adminAuthenticationListener, $siteAuthenticationListener], $adminExceptionListener);
        $this->add($matcher, [$siteAuthenticationListener], $siteExceptionListener);
        $this->add($matcher, [$startAuthenticationListener], $startExceptionListener);
    }
}
