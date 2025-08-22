<?php

namespace App\Security;

use Override;
use Symfony\Component\Security\Core\Exception\AccountStatusException;

class AccountDeniedLoginException extends AccountStatusException
{
    #[Override]
    public function getMessageKey(): string
    {
        return 'This account has been disabled. Please contact the support team.';
    }
}
