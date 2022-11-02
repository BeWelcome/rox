<?php

namespace App\EventSubscriber;

use App\Entity\Member;
use Carbon\Carbon;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Event\AuthenticationSuccessEvent;
use Symfony\Component\Security\Http\Event\CheckPassportEvent;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Symfony\Component\Security\Http\Event\LoginSuccessEvent;
use Symfony\Component\Security\Http\SecurityEvents;

class AuthenticationEventSubscriber implements EventSubscriberInterface
{
    private EntityManagerInterface $entityManager;
    private AuthorizationCheckerInterface $authorizationChecker;
    private ?TokenStorageInterface $tokenStorage;

    public function __construct(
        EntityManagerInterface $entityManager,
        TokenStorageInterface $tokenStorage = null,
        AuthorizationCheckerInterface $authorizationChecker = null
    ) {
        $this->entityManager = $entityManager;
        $this->authorizationChecker = $authorizationChecker;
        $this->tokenStorage = $tokenStorage;
    }

    public static function getSubscribedEvents()
    {
        return [
            // must be registered before (i.e. with a higher priority than) the default Locale listener
            LoginSuccessEvent::class => 'onLoginSuccess',
            AuthenticationSuccessEvent::class => 'onAuthenticationSuccess',
            CheckPassportEvent::class => 'onCheckPassport',
            KernelEvents::RESPONSE => 'onKernelResponse',
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
            $lastLogin = $member->getLastLogin();
            $diff = (new Carbon())->diffInMinutes($lastLogin);
            if ($diff > 5) {
                $member->setLastLogin(new DateTime());
                $this->entityManager->persist($member);
                $this->entityManager->flush();
            }
        }

    }

    public function onCheckPassport(CheckPassportEvent $event): void
    {
    }

    public function onAuthenticationSuccess(AuthenticationSuccessEvent $event): void
    {
    }

    public function onLoginSuccess(LoginSuccessEvent $event): void
    {
    }
}
