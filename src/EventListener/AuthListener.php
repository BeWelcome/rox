<?php

namespace App\EventListener;

use App\Doctrine\MemberStatusType;
use App\Entity\Member;
use DateTime;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;

/**
 * Listens for interactive login (ie. from the login form, when a password
 * has been typed) to validate that the stored password hash adheres to
 * current encoder rules (using PHP's password_needs_rehash() function.) If
 * the check fails, the password is re-encoded with the new encoder by
 * calling changePassword on MemberService.
 */
class AuthListener
{
    /** @var EntityManager */
    private $em;

    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    public function onAuthenticationSuccess(InteractiveLoginEvent $e)
    {
        /** @var Member $user */
        $user = $e->getAuthenticationToken()->getUser();
        if (MemberStatusType::ACTIVE !== $user->getStatus() && MemberStatusType::CHOICE_INACTIVE !== $user->getStatus()) {
            $user->setStatus(MemberStatusType::ACTIVE);
        }
        $user->setLastlogin(new DateTime());
        $this->em->persist($user);
        $this->em->flush();
    }
}
