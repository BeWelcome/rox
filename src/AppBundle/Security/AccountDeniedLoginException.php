<?php

namespace AppBundle\Security;

use Symfony\Component\Security\Core\Exception\AccountStatusException;

class AccountDeniedLoginException extends AccountStatusException
{
    /**
     * {@inheritdoc}
     */
    public function getMessageKey()
    {
        return 'This account has been disabled. Please contact the support team.';
    }
}
