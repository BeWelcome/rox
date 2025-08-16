<?php

namespace App\Security;

use Symfony\Component\Security\Core\Exception\CustomUserMessageAccountStatusException;

class AccountSuspendedException extends CustomUserMessageAccountStatusException
{
    public function getMessageKey(): string
    {
        return 'loginerrorsuspended';
    }
}
