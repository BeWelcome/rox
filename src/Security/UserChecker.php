<?php

namespace App\Security;

use App\Entity\NewMember as Member;
use Symfony\Component\Security\Core\Exception\AccountExpiredException;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class UserChecker implements UserCheckerInterface
{
    /**
     * @throws AccountBannedException
     * @throws AccountDeniedLoginException
     */
    public function checkPreAuth(UserInterface $user): void
    {
        if (!$user instanceof Member) {
            return;
        }

        if ($user->isBanned()) {
            throw new AccountBannedException();
        }

        if ($user->isDeniedAccess() && !$user->isSuspended()) {
            throw new AccountDeniedLoginException();
        }
    }

    /**
     * @throws AccountExpiredException
     * @throws AccountSuspendedException
     * @throws AccountMailConfirmedException
     * @throws AccountMailNotConfirmedException
     */
    public function checkPostAuth(UserInterface $user): void
    {
        if (!$user instanceof Member) {
            return;
        }

        if ($user->isExpired()) {
            throw new AccountExpiredException();
        }

        if ($user->isSuspended()) {
            throw new AccountSuspendedException();
        }
    }
}
