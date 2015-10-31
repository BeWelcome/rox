<?php

namespace Rox\Security;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Authentication\AuthenticationFailureHandlerInterface;

class RoxAuthenticationFailureHandler implements AuthenticationFailureHandlerInterface {

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception)
    {
        return true;
    }
}