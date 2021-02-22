<?php

declare(strict_types=1);

namespace App\EventListener;

use ApiPlatform\Core\EventListener\EventPriorities;
use App\Entity\Member;
use App\Entity\Trip;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\KernelEvent;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Core\Security;

/**
 * @author Vincent Chalamon <vincentchalamon@gmail.com>
 */
final class TripEventListener implements EventSubscriberInterface
{
    private Security $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::VIEW => [
                ['setTripCreator', EventPriorities::PRE_VALIDATE],
            ],
        ];
    }

    /**
     * @param KernelEvent|ViewEvent $event
     */
    public function setTripCreator(KernelEvent $event): void
    {
        $object = $event->getControllerResult();
        /** @var null|Member $user */
        $user = $this->security->getUser();
        if (!$object instanceof Trip || !$user || !\in_array($event->getRequest()->getMethod(), [
                Request::METHOD_POST,
                Request::METHOD_PUT,
            ])
        ) {
            return;
        }

        $object->setCreator($user);
    }
}
