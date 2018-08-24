<?php
/**
 * Created by PhpStorm.
 * User: bla
 * Date: 24.08.2018
 * Time: 11:59.
 */

namespace AppBundle\Security;

use Symfony\Component\Security\Core\Exception\AccountStatusException;

class AccountBannedException extends AccountStatusException
{
    /**
     * {@inheritdoc}
     */
    public function getMessageKey()
    {
        return 'Account has been banned.';
    }
}
