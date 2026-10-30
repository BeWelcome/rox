<?php

namespace App\Controller;

use App\Entity\ForumThread;
use App\Entity\Group;
use App\Entity\GroupMembership;
use App\Entity\Member;
use App\Model\ForumModel;
use App\Utilities\TranslatedFlashTrait;
use App\Utilities\TranslatorTrait;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\UriSigner;
use Symfony\Component\Routing\Attribute\Route;

/**
 * A member's forum notifications: thread subscriptions and the notifications of their group memberships. The URLs are
 * the ones of the legacy forum, including those in the notification mails, so the switches are links.
 */
class ForumSubscriptionController extends AbstractController
{
    use TranslatedFlashTrait;
    use TranslatorTrait;

    public function __construct(
        private readonly ForumModel $forumModel,
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    #[Route(path: '/forums/subscriptions', name: 'subscriptions', methods: ['GET'])]
    public function overview(): Response
    {
        $member = $this->getMember();

        return $this->render('forum/subscriptions.html.twig', [
            'subscriptions' => $this->forumModel->getSubscriptions($member),
            'memberships' => $this->entityManager->getRepository(GroupMembership::class)->findBy(
                ['member' => $member, 'status' => 'In'],
            ),
        ]);
    }

    #[Route(path: '/forums/subscriptions/enable', name: 'subscriptions_enable', methods: ['GET'])]
    public function enableAll(): RedirectResponse
    {
        $this->forumModel->setAllThreadNotifications($this->getMember(), true);

        return $this->redirectToRoute('subscriptions');
    }

    #[Route(path: '/forums/subscriptions/disable', name: 'subscriptions_disable', methods: ['GET'])]
    public function disableAll(): RedirectResponse
    {
        $this->forumModel->setAllThreadNotifications($this->getMember(), false);

        return $this->redirectToRoute('subscriptions');
    }

    #[Route(path: '/forums/subscribe/thread/{threadId}', name: 'forum_thread_subscription', requirements: ['threadId' => '\d+'], methods: ['GET'])]
    #[Route(path: '/forums/subscriptions/subscribe/thread/{threadId}', name: 'thread_subscribe', requirements: ['threadId' => '\d+'], methods: ['GET'])]
    public function subscribe(Request $request, #[MapEntity(id: 'threadId')] ForumThread $thread): RedirectResponse
    {
        $this->denyAccessUnlessGranted('FORUM_THREAD_VIEW', $thread);
        $this->forumModel->subscribe($thread, $this->getMember());
        $this->addTranslatedFlash('notice', 'forum.subscription.subscribed');

        return $this->back($request);
    }

    #[Route(
        path: '/forums/subscriptions/unsubscribe/thread/{threadId}/{subscriptionId}',
        name: 'thread_unsubscribe',
        requirements: ['threadId' => '\d+'],
        defaults: ['subscriptionId' => ''],
        methods: ['GET'],
    )]
    #[Route(path: '/forums/unsubscribe/thread/{threadId}', name: 'forum_thread_unsubscribe', requirements: ['threadId' => '\d+'], methods: ['GET'])]
    public function unsubscribe(Request $request, #[MapEntity(id: 'threadId')] ForumThread $thread): RedirectResponse
    {
        $this->forumModel->unsubscribe($thread, $this->getMember());
        $this->addTranslatedFlash('notice', 'forum.subscription.unsubscribed');

        return $this->back($request);
    }

    #[Route(path: '/forums/subscriptions/enable/thread/{threadId}', name: 'forum_thread_subscription_enable', requirements: ['threadId' => '\d+'], methods: ['GET'])]
    #[Route(path: '/forums/subscriptions/enable/thread/{threadId}/{subscriptionId}', name: 'thread_notifications_enable', requirements: ['threadId' => '\d+'], methods: ['GET'])]
    public function enableThread(Request $request, #[MapEntity(id: 'threadId')] ForumThread $thread): RedirectResponse
    {
        $this->forumModel->setThreadNotifications($thread, $this->getMember(), true);

        return $this->back($request);
    }

    #[Route(path: '/forums/subscriptions/disable/thread/{threadId}', name: 'forum_thread_subscription_disable', requirements: ['threadId' => '\d+'], methods: ['GET'])]
    #[Route(path: '/forums/subscriptions/disable/thread/{threadId}/{subscriptionId}', name: 'thread_notifications_disable', requirements: ['threadId' => '\d+'], methods: ['GET'])]
    public function disableThread(Request $request, #[MapEntity(id: 'threadId')] ForumThread $thread): RedirectResponse
    {
        $this->forumModel->setThreadNotifications($thread, $this->getMember(), false);
        $this->addTranslatedFlash('notice', 'forum.subscription.paused');

        return $this->back($request);
    }

    #[Route(path: '/forums/subscriptions/enable/group/{groupId}', name: 'group_notifications_enable', requirements: ['groupId' => '\d+'], methods: ['GET'])]
    #[Route(path: '/forums/subscriptions/subscribe/group/{groupId}', name: 'group_subscribe', requirements: ['groupId' => '\d+'], methods: ['GET'])]
    public function enableGroup(Request $request, #[MapEntity(id: 'groupId')] Group $group): RedirectResponse
    {
        if (!$this->forumModel->setGroupNotifications($group, $this->getMember(), true)) {
            // not in the group: joining comes first
            return $this->redirectToRoute('join_group', ['groupId' => $group->getId()]);
        }

        return $this->back($request);
    }

    #[Route(path: '/forums/subscriptions/disable/group/{groupId}', name: 'group_notifications_disable', requirements: ['groupId' => '\d+'], methods: ['GET'])]
    #[Route(path: '/forums/subscriptions/unsubscribe/group/{groupId}', name: 'group_unsubscribe', requirements: ['groupId' => '\d+'], methods: ['GET'])]
    public function disableGroup(Request $request, #[MapEntity(id: 'groupId')] Group $group): RedirectResponse
    {
        $this->forumModel->setGroupNotifications($group, $this->getMember(), false);
        $this->addTranslatedFlash('notice', 'forum.subscription.group.off');

        return $this->back($request);
    }

    /**
     * The link in the first agora mail of a new member: stops the BeWelcome group's notifications without logging
     * in. The link is signed, so it only works for the member it was sent to.
     */
    #[Route(path: '/forums/agora/notifications/off/{memberId}', name: 'agora_notifications_off', requirements: ['memberId' => '\d+'], methods: ['GET'])]
    public function agoraNotificationsOff(Request $request, #[MapEntity(id: 'memberId')] Member $member, UriSigner $uriSigner): Response
    {
        if (!$uriSigner->checkRequest($request)) {
            throw $this->createNotFoundException();
        }

        $beWelcome = $this->entityManager->find(Group::class, Group::BEWELCOME);
        if (null !== $beWelcome) {
            $this->forumModel->setGroupNotifications($beWelcome, $member, false);
        }

        return $this->render('forum/agora_notifications_off.html.twig');
    }

    /**
     * Back to the page the link was on (the thread, the subscriptions), else to the subscriptions: the mails' links
     * have no page to go back to.
     */
    private function back(Request $request): RedirectResponse
    {
        $referer = (string) $request->headers->get('referer');
        if ('' !== $referer && str_starts_with($referer, $request->getSchemeAndHttpHost() . '/')) {
            return $this->redirect($referer);
        }

        return $this->redirectToRoute('subscriptions');
    }

    private function getMember(): Member
    {
        /** @var Member $member */
        $member = $this->getUser();

        return $member;
    }
}
