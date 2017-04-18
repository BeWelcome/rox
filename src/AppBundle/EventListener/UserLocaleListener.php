<?php

namespace AppBundle\EventListener;

use Rox\Member\Model\Member;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;

/**
 * @see http://symfony.com/doc/current/cookbook/session/locale_sticky_session.html
 */
class UserLocaleListener
{
    /**
     * @var Session
     */
    protected $session;

    public function __construct(Session $session)
    {
        $this->session = $session;
    }

    public function onInteractiveLogin(InteractiveLoginEvent $event)
    {
        /** @var Member $user */
        $user = $event->getAuthenticationToken()->getUser();

        if (null !== $user->getLocale()) {
            $this->session->set('locale', $user->getLocale());
        }
    }
}
