<?php

namespace App\Security;

use Symfony\Component\Security\Core\Exception\CustomUserMessageAccountStatusException;

class AccountSuspendedException extends CustomUserMessageAccountStatusException
{
    /**
     * {@inheritdoc}
     */
    #[\Override]
    public function getMessageKey(): string
    {
        return 'loginerrorsuspended';
    }
}
