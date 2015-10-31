<?php

namespace Rox\Security;

use Symfony\Component\Security\Core\Authentication\Token\AbstractToken;

class RoxUserToken extends AbstractToken
{
    public function __construct(array $roles = array())
    {
        parent::__construct($roles);
    }

    public function getCredentials()
    {
        return '';
    }
}