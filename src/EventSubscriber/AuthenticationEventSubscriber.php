<?php

namespace App\EventSubscriber;

use App\Entity\Member;
use Carbon\Carbon;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\PasswordHasher\Hasher\PasswordHasherFactoryInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\PasswordHasher\PasswordHasherInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface;
use Symfony\Component\Security\Core\Event\AuthenticationSuccessEvent;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\PasswordUpgradeBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\UserPassportInterface;
use Symfony\Component\Security\Http\Event\CheckPassportEvent;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Symfony\Component\Security\Http\Event\LoginSuccessEvent;
use Symfony\Component\Security\Http\SecurityEvents;

class AuthenticationEventSubscriber implements EventSubscriberInterface
{
    private EntityManagerInterface $entityManager;
    private ?AuthorizationCheckerInterface $authorizationChecker;
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
            $lastLogin = $member->getLastLogin();
            $diff = (new Carbon())->diffInMinutes($lastLogin);
            if (null === $lastLogin || $diff > 5) {
                $member->setLastLogin(new DateTime());
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

        if ($token === null) {
            return;
        }

        $member = $token->getUser();
        if ($member->isBrowsable() === false) {
            $this->tokenStorage->setToken(null); // Force logout
            $event->getRequest()->getSession()->invalidate();
        }
    }
}
