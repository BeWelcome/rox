<?php

namespace Rox\Framework\Firewall;

use Rox\Framework\UserProvider;
use Rox\Security\RoxUserProvider;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\RequestMatcher;
use Symfony\Component\Routing\Router;
use Symfony\Component\Security\Core\Authentication\AuthenticationProviderManager;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use Symfony\Component\Security\Core\User\UserChecker;
use Symfony\Component\Security\Guard\Firewall\GuardAuthenticationListener;
use Symfony\Component\Security\Guard\GuardAuthenticatorHandler;
use Symfony\Component\Security\Guard\Provider\GuardAuthenticationProvider;
use Symfony\Component\Security\Http\FirewallMap;

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
     * @param Router $router
     * @param EventDispatcherInterface $dispatcher
     * @param TokenStorage $tokenStorage
     */
    public function __construct(Router $router, EventDispatcherInterface $dispatcher, TokenStorage $tokenStorage)
    {
        // anonymous Urls '^/(?!.)' '/login_symfony'
        $anonymousMatcher = new RequestMatcher('^/(?!.)');
        $loginMatcher = new RequestMatcher('^/login');
        $sessionMatcher = new RequestMatcher('^/.*');

        $guardHandler = new GuardAuthenticatorHandler($tokenStorage, $dispatcher);

        $anonymousAuthenticator = [new AnonymousAuthenticator($router)];
        $anonymousProviders = [new GuardAuthenticationProvider($anonymousAuthenticator, new RoxUserProvider(), 'AnonymousBewelcome', new UserChecker())];
        $anonymousListener = new GuardAuthenticationListener($guardHandler, new AuthenticationProviderManager($anonymousProviders), 'AnonymousBewelcome', $anonymousAuthenticator);

        $formAuthenticator = [new FormLoginAuthenticator( $router )];
        $providers = [new GuardAuthenticationProvider($formAuthenticator, new RoxUserProvider(), 'LoginBewelcome', new UserChecker())];
        $loginListener = new GuardAuthenticationListener($guardHandler, new AuthenticationProviderManager($providers), 'LoginBewelcome', $formAuthenticator);

        $sessionAuthenticator = [new SessionAuthenticator( $router )];
        $providers = [new GuardAuthenticationProvider($sessionAuthenticator, new RoxUserProvider(), 'SessionBewelcome', new UserChecker())];
        $sessionListener = new GuardAuthenticationListener($guardHandler, new AuthenticationProviderManager($providers), 'SessionBewelcome', $sessionAuthenticator);

        $this->add($anonymousMatcher, [$anonymousListener]);
        $this->add($loginMatcher, [$loginListener]);
        $this->add($sessionMatcher, [$sessionListener]);
    }
}
