<?php

namespace App\Security;

use App\Entity\Member;
use Symfony\Component\Security\Core\Exception\AccountExpiredException;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class UserChecker implements UserCheckerInterface
{
    /**
     * @throws AccountBannedException
     * @throws AccountDeniedLoginException
     */
    public function checkPreAuth(UserInterface $user)
    {
        if (!$user instanceof Member) {
            return;
        }

        if ($user->isBanned()) {
            throw new AccountBannedException();
        }

        if ($user->isDeniedAccess()) {
            throw new AccountDeniedLoginException();
        }
    }

    /**
     * @throws AccountExpiredException
     * @throws AccountMailNotConfirmedException
     */
    public function checkPostAuth(UserInterface $user)
    {
        if (!$user instanceof Member) {
            return;
        }

        if ($user->isExpired()) {
            throw new AccountExpiredException();
        }

        if ($user->isNotConfirmedYet()) {
            throw new AccountMailNotConfirmedException();
        }
    }
}
