<?php

/**
 * Created by PhpStorm.
 * User: bla
 * Date: 24.08.2018
 * Time: 11:59.
 */

namespace App\Security;

use Symfony\Component\Security\Core\Exception\CustomUserMessageAccountStatusException;

class AccountMailConfirmedException extends CustomUserMessageAccountStatusException
{
    /**
     * {@inheritdoc}
     */
    public function getMessageKey(): string
    {
        return 'login.mail.confirmed';
    }
}
