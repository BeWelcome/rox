<?php

namespace Rox\Member\Listener;

use Rox\Member\Repository\MemberRepositoryInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;

/**
 * Adds the logged in user to the request attributes.
 */
class UserListener
{
    /**
     * @var SessionInterface
     */
    protected $session;

    /**
     * @var MemberRepositoryInterface
     */
    protected $memberRepository;

    public function __construct(
        SessionInterface $session,
        MemberRepositoryInterface $memberRepository
    ) {
        $this->session = $session;
        $this->memberRepository = $memberRepository;
    }

    public function onKernelRequest(GetResponseEvent $e)
    {
        // The session object is also available through the request object, but
        // injecting with the constructor makes unit testing easier.
        $memberId = $this->session->get('IdMember');

        if (!$memberId) {
            return;
        }

        $member = $this->memberRepository->getById($memberId);

        $e->getRequest()->attributes->set('member', $member);
    }
}
