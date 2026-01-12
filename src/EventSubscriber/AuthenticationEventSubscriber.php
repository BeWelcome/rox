<?php

namespace App\EventSubscriber;

use App\Doctrine\MemberStatusType;
use App\Entity\Member;
use Carbon\Carbon;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class AuthenticationEventSubscriber implements EventSubscriberInterface
{
    public function __construct(private readonly EntityManagerInterface $entityManager, private readonly ?TokenStorageInterface $tokenStorage = null, private readonly ?AuthorizationCheckerInterface $authorizationChecker = null)
    {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::RESPONSE => 'onKernelResponse',
            KernelEvents::REQUEST => 'onKernelRequest',
        ];
    }

    public function onKernelResponse(ResponseEvent $event)
    {
        if (!$event->isMainRequest()) {
            return;
        }

        if (null === $this->authorizationChecker) {
            return;
        }

        if (null === $this->tokenStorage) {
            return;
        }

        if ($this->authorizationChecker->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
            /** @var Member $member */
            $member = $this->tokenStorage->getToken()->getUser();

            // Update the last login if last login and current time differ for more than 5 minutes
            $lastLogin = $member->getLastActive();
            $diff = new Carbon()->diffInMinutes($lastLogin, true);
            if (null === $lastLogin || $diff > 5) {
                $member->setLastActive(new DateTime());

                $status = $member->getStatus();
                if (MemberStatusType::OUT_OF_REMIND === $status) {
                    $member->setStatus(MemberStatusType::ACTIVE);
                }

                $member->setRemindersWithOutLogin(0);

                $this->entityManager->persist($member);
                $this->entityManager->flush();
            }
        }
    }

    public function onKernelRequest(RequestEvent $event)
    {
        if (!$event->isMainRequest()) {
            return;
        }

        if (null === $this->authorizationChecker) {
            return;
        }

        if (null === $this->tokenStorage) {
            return;
        }

        /** @var Member $member */
        $token = $this->tokenStorage->getToken();

        if (null === $token) {
            return;
        }

        $member = $token->getUser();
        if (false === $member->isBrowsable()) {
            $this->tokenStorage->setToken(null); // Force logout
            $event->getRequest()->getSession()->invalidate();
        }
    }
}
