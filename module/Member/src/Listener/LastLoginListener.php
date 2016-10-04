<?php

namespace Rox\Member\Listener;

use Rox\Member\Model\Member;
use Symfony\Component\Security\Core\Event\AuthenticationEvent;

class LastLoginListener
{
    public function onAuthSuccess(AuthenticationEvent $e)
    {
        $user = $e->getAuthenticationToken()->getUser();

        if (!$user instanceof Member) {
            return;
        }

        $user->LastLogin = $user->freshTimestamp();

        $user->save();
    }
}
