<?php

namespace App\Security;

use Override;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAccountStatusException;

class AccountSuspendedException extends CustomUserMessageAccountStatusException
{
    #[Override]
    public function getMessageKey(): string
    {
        return 'loginerrorsuspended';
    }
}
