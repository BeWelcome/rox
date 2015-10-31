<?php

namespace Rox\Security;

use Symfony\Component\Security\Core\Authentication\AuthenticationTrustResolver;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use Symfony\Component\Security\Http\Firewall\ExceptionListener;
use Symfony\Component\Security\Http\HttpUtils;

class RoxExceptionListener extends ExceptionListener
{
    public function  __construct(TokenStorage $tokenStorage, AuthenticationTrustResolver $trustResolver) {
        parent::__construct($tokenStorage, $trustResolver, new HttpUtils(), 'bewelcome' );
    }
}