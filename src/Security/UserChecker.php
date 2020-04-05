<?php

namespace App\Security;

use App\Entity\Member;
use DateTime;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Symfony\Component\Security\Core\Exception\AccountExpiredException;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class UserChecker implements UserCheckerInterface
{
    /**
     * @param UserInterface $user
     *
     * @throws AccountBannedException
     * @throws AccountMailNotConfirmedException
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
     * @param UserInterface $user
     *
     * @throws AccountExpiredException
     * @throws AccountDeniedLoginException
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
