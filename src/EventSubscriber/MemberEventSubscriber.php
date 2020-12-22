<?php

declare(strict_types=1);

namespace App\EventSubscriber;

use ApiPlatform\Core\EventListener\EventPriorities;
use App\Entity\Member;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Core\User\UserProviderInterface;

final class MemberEventSubscriber implements EventSubscriberInterface
{
    private $userProvider;

    public function __construct(UserProviderInterface $userProvider)
    {
        $this->userProvider = $userProvider;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => [
                ['findOneByUsername', EventPriorities::PRE_READ],
            ],
        ];
    }

    public function findOneByUsername(RequestEvent $event): void
    {
        $request = $event->getRequest();
        $attributes = $request->attributes;
        if ('api_members_get_item' !== $attributes->get('_route')) {
            return;
        }

        /** @var Member|null $member */
        $member = $this->userProvider->loadUserByUsername($attributes->get('username'));
        if ($member) {
            $attributes->set('id', $member->getId());
        }
    }
}
