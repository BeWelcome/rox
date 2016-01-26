<?php

/**
 * Setup the firewall using the symfony security component
 *
 * This is rather complicated but worth the struggle
 *
 */

use Rox\Framework\FormLoginAuthenticator;
use Rox\Framework\Logger;
use Rox\Framework\UserProvider;
use Symfony\Component\Security\Core\Authentication\AuthenticationProviderManager;
use Symfony\Component\Security\Core\User\UserChecker;
use Symfony\Component\Security\Guard\Firewall\GuardAuthenticationListener;
use Symfony\Component\Security\Guard\GuardAuthenticatorHandler;
use Symfony\Component\Security\Guard\Provider\GuardAuthenticationProvider;
use Symfony\Component\Security\Http\FirewallMap;

// anonymous Urls '^/(?!.)' '/login_symfony'
$startMatcher = new \Symfony\Component\HttpFoundation\RequestMatcher('^/(?!.)');
$loginMatcher = new \Symfony\Component\HttpFoundation\RequestMatcher('^/login(?!.)');
$loginCheckMatcher = new \Symfony\Component\HttpFoundation\RequestMatcher('^/login_symfony(?!.)');
$remainingSiteMatcher = new \Symfony\Component\HttpFoundation\RequestMatcher('^/.*');

$anonymousListener = new \Rox\Framework\Firewall\AnonymousAuthenticationListener();
$loginListener = new \Rox\Framework\Firewall\LoginAuthenticationListener();
$sessionListener = new \Rox\Framework\Firewall\SessionAuthenticationListener();

$firewallMap = new FirewallMap();
$firewallMap->add($startMatcher, [$anonymousListener]);
$firewallMap->add($loginMatcher, [$anonymousListener]);
$firewallMap->add($loginCheckMatcher, [$loginListener]);
$firewallMap->add($remainingSiteMatcher, [$sessionListener]);

