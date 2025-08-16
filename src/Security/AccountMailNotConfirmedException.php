<?php

/**
 * Created by PhpStorm.
 * User: bla
 * Date: 24.08.2018
 * Time: 11:59.
 */

namespace App\Security;

use Symfony\Component\Security\Core\Exception\CustomUserMessageAccountStatusException;

class AccountMailNotConfirmedException extends CustomUserMessageAccountStatusException
{
    public function getMessageKey(): string
    {
        return 'login.mail.not_confirmed';
    }
}
