<?php

namespace Rox\Member\Listener;

use Member;
use RoxModelBase;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

/**
 * Class RestoreRememberListener
 *
 * Replicates RoxFrontRouter::initUser() behaviour of loading user from remember cookie
 */
class RestoreRememberListener
{
    /**
     * @var SessionInterface
     */
    protected $session;

    public function __construct(SessionInterface $session)
    {
        $this->session = $session;
    }

    public function onKernelRequest()
    {
        $roxModelBase = new RoxModelBase();

        $member = $roxModelBase->getLoggedInMember();

        // Do nothing if member is already logged in with a session.
        if ($member) {
            return;
        }

        // Otherwise try to login the member from a remember cookie.
        /** @var Member|boolean $member */
        $member = $roxModelBase->restoreLoggedInMember();

        if (!$member) {
            return;
        }

        $this->session->set('IdMember', $member->id);
    }
}
