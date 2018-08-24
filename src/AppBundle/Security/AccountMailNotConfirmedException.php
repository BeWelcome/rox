<?php
/**
 * Created by PhpStorm.
 * User: bla
 * Date: 24.08.2018
 * Time: 11:59.
 */

namespace AppBundle\Security;

use Symfony\Component\Security\Core\Exception\AccountStatusException;

class AccountMailNotConfirmedException extends AccountStatusException
{
    /**
     * {@inheritdoc}
     */
    public function getMessageKey()
    {
        return "Please check your spam folder as your email address hasn't been confirmed yet.";
    }
}
