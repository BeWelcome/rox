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

use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpKernel\HttpKernel;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\Router;
use Symfony\Component\Security\Http\Firewall;

/**
 * RoxFirewall uses a RoxFirewallMap to register security listeners for the given
 * request.
 */
class RoxFirewall extends Firewall
{
    /**
     * Constructor.
     *
     * @param HttpKernel               $httpKernel
     * @param Router                   $router
     * @param RequestContext           $context
     * @param EventDispatcherInterface $dispatcher An EventDispatcherInterface instance
     */
    public function __construct(HttpKernel $httpKernel, Router $router, RequestContext $context, EventDispatcherInterface $dispatcher)
    {
        parent::__construct(new RoxFirewallMap($httpKernel, $router, $context), $dispatcher);
    }
}

/*
 * $httpUtils = new HttpUtils();
$tokenStorage = new TokenStorage();
$trustResolver = new AuthenticationTrustResolver(
    'Rox\Security\Anonymous',
    'Rox\Security\RememberMe'
);
$exceptionListener = new ExceptionListener(
    $tokenStorage,
    $trustResolver,
    $httpUtils,
    'bewelcome'
);

$userProvider = new RoxUserProvider();
$userChecker = new UserChecker();
$defaultEncoder = new MessageDigestPasswordEncoder('sha512', true, 5000);
$encoders = [
    $defaultEncoder,
];
$encoderFactory = new EncoderFactory(
    $encoders
);

$formEntryPoint = new FormAuthenticationEntryPoint(
    $framework,
    $httpUtils,
    '/login'
);
$authenticationProviders = [
    new AnonymousAuthenticationProvider('bewelcome'),
    new DaoAuthenticationProvider(
        $userProvider,
        $userChecker,
        'bewelcome',
        $encoderFactory
    ),
];
$authenticationManager = new AuthenticationProviderManager(
    $authenticationProviders
);

$firewallMap = new FirewallMap();
$adminMatcher = new RequestMatcher('^/admin');
$firewallMap->add(
    $adminMatcher,
    [
        new AnonymousAuthenticationListener($tokenStorage, 'bewelcome'),
    ],
    $exceptionListener
);
$defaultSuccessHandler = new DefaultAuthenticationSuccessHandler($httpUtils);
$defaultFailureHandler = new DefaultAuthenticationFailureHandler(
    $framework,
    $httpUtils
);
 $simpleFormAuthenticationListener = new SimpleFormAuthenticationListener(
    $tokenStorage,
    $authenticationManager,
    new SessionAuthenticationStrategy(SessionAuthenticationStrategy::INVALIDATE),
    $httpUtils,
    'bewelcome',
    $defaultSuccessHandler,
    $defaultFailureHandler,
    null,
    null,
    $dispatcher,
    null,
    new \Symfony\Component\Security\Http\Authentication\SimpleAuthenticationHandler()

);

 $siteMatcher = new RequestMatcher('^/.*');
$firewallMap->add($siteMatcher, [
    $simpleFormAuthenticationListener
], $exceptionListener);
$startMatcher = new RequestMatcher('^/$');
$firewallMap->add($startMatcher, [
    new SimpleFormAuthenticationListener($tokenStorage, $authenticationManager, SessionAuthenticationStrategy::INVALIDATE, $httpUtils, 'bewelcome',
        new DefaultAuthenticationSuccessHandler($httpUtils),
        new DefaultAuthenticationFailureHandler($framework, $httpUtils))
], $exceptionListener);

$firewall = new Firewall($firewallMap, $dispatcher);
$dispatcher->addSubscriber($firewall);
*/

