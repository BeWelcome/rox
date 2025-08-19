<?php

namespace App\Security;

use Symfony\Component\Security\Core\Exception\AccountStatusException;

class AccountDeniedLoginException extends AccountStatusException
{
    /**
     * {@inheritdoc}
     */
    #[\Override]
    public function getMessageKey(): string
    {
        return 'This account has been disabled. Please contact the support team.';
    }
}
