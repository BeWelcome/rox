<?php

/**
 * Created by PhpStorm.
 * User: bla
 * Date: 24.08.2018
 * Time: 11:59.
 */

namespace App\Security;

use Symfony\Component\Security\Core\Exception\AccountStatusException;

class AccountMailNotConfirmedException extends AccountStatusException
{
    /**
     * {@inheritdoc}
     */
    public function getMessageKey()
    {
        return 'login.mail.not_confirmed';
    }
}
