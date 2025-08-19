<?php

/**
 * Created by PhpStorm.
 * User: bla
 * Date: 24.08.2018
 * Time: 11:59.
 */

namespace App\Security;

use Override;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAccountStatusException;

class AccountMailNotConfirmedException extends CustomUserMessageAccountStatusException
{
    #[Override]
    public function getMessageKey(): string
    {
        return 'login.mail.not_confirmed';
    }
}
