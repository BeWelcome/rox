<?php

namespace Rox\Security;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationSuccessHandlerInterface;

class RoxAuthenticationSuccessHandler implements AuthenticationSuccessHandlerInterface {

    public function onAuthenticationSuccess(Request $request, TokenInterface $token)
    {
        return true;
    }

}