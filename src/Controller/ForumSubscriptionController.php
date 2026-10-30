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
 * A member's forum notifications: thread subscriptions and the notifications of their group memberships, at the
 * URLs of the legacy forum. The links in the notification mails (switching off, unsubscribing) are GET requests; on
 * pages every switch is a POST with a CSRF token.
 */
class ForumSubscriptionController extends AbstractController
{
    use TranslatedFlashTrait;
    use TranslatorTrait;

    /** The CSRF token id of the switches on pages. */
    public const string TOKEN = 'forum-subscriptions';

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

    #[Route(path: '/forums/subscriptions/enable', name: 'subscriptions_enable', methods: ['POST'])]
    public function enableAll(Request $request): RedirectResponse
    {
        $this->checkToken($request);
        $this->forumModel->setAllThreadNotifications($this->getMember(), true);

        return $this->redirectToRoute('subscriptions');
    }

    #[Route(path: '/forums/subscriptions/disable', name: 'subscriptions_disable', methods: ['POST'])]
    public function disableAll(Request $request): RedirectResponse
    {
        $this->checkToken($request);
        $this->forumModel->setAllThreadNotifications($this->getMember(), false);

        return $this->redirectToRoute('subscriptions');
    }

    #[Route(path: '/forums/subscribe/thread/{threadId}', name: 'forum_thread_subscription', requirements: ['threadId' => '\d+'], methods: ['POST'])]
    #[Route(path: '/forums/subscriptions/subscribe/thread/{threadId}', name: 'thread_subscribe', requirements: ['threadId' => '\d+'], methods: ['POST'])]
    public function subscribe(Request $request, #[MapEntity(id: 'threadId')] ForumThread $thread): RedirectResponse
    {
        $this->checkToken($request);
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
    #[Route(path: '/forums/unsubscribe/thread/{threadId}', name: 'forum_thread_unsubscribe', requirements: ['threadId' => '\d+'], methods: ['POST'])]
    public function unsubscribe(Request $request, #[MapEntity(id: 'threadId')] ForumThread $thread): RedirectResponse
    {
        $this->checkToken($request);
        $this->forumModel->unsubscribe($thread, $this->getMember());
        $this->addTranslatedFlash('notice', 'forum.subscription.unsubscribed');

        return $this->back($request);
    }

    #[Route(path: '/forums/subscriptions/enable/thread/{threadId}', name: 'forum_thread_subscription_enable', requirements: ['threadId' => '\d+'], methods: ['POST'])]
    #[Route(path: '/forums/subscriptions/enable/thread/{threadId}/{subscriptionId}', name: 'thread_notifications_enable', requirements: ['threadId' => '\d+'], methods: ['GET'])]
    public function enableThread(Request $request, #[MapEntity(id: 'threadId')] ForumThread $thread): RedirectResponse
    {
        $this->checkToken($request);
        $this->forumModel->setThreadNotifications($thread, $this->getMember(), true);

        return $this->back($request);
    }

    #[Route(path: '/forums/subscriptions/disable/thread/{threadId}', name: 'forum_thread_subscription_disable', requirements: ['threadId' => '\d+'], methods: ['GET', 'POST'])]
    #[Route(path: '/forums/subscriptions/disable/thread/{threadId}/{subscriptionId}', name: 'thread_notifications_disable', requirements: ['threadId' => '\d+'], methods: ['GET'])]
    public function disableThread(Request $request, #[MapEntity(id: 'threadId')] ForumThread $thread): RedirectResponse
    {
        $this->checkToken($request);
        $this->forumModel->setThreadNotifications($thread, $this->getMember(), false);
        $this->addTranslatedFlash('notice', 'forum.subscription.paused');

        return $this->back($request);
    }

    #[Route(path: '/forums/subscriptions/enable/group/{groupId}', name: 'group_notifications_enable', requirements: ['groupId' => '\d+'], methods: ['POST'])]
    #[Route(path: '/forums/subscriptions/subscribe/group/{groupId}', name: 'group_subscribe', requirements: ['groupId' => '\d+'], methods: ['POST'])]
    public function enableGroup(Request $request, #[MapEntity(id: 'groupId')] Group $group): RedirectResponse
    {
        $this->checkToken($request);
        if (!$this->forumModel->setGroupNotifications($group, $this->getMember(), true)) {
            // not in the group: joining comes first
            return $this->redirectToRoute('join_group', ['groupId' => $group->getId()]);
        }

        return $this->back($request);
    }

    #[Route(path: '/forums/subscriptions/disable/group/{groupId}', name: 'group_notifications_disable', requirements: ['groupId' => '\d+'], methods: ['GET', 'POST'])]
    #[Route(path: '/forums/subscriptions/unsubscribe/group/{groupId}', name: 'group_unsubscribe', requirements: ['groupId' => '\d+'], methods: ['GET', 'POST'])]
    public function disableGroup(Request $request, #[MapEntity(id: 'groupId')] Group $group): RedirectResponse
    {
        $this->checkToken($request);
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
     * The switches on pages are POST requests with a token; the mails' links are GET requests without one (the
     * routes that serve them accept only what they're for).
     */
    private function checkToken(Request $request): void
    {
        if ($request->isMethod('POST') && !$this->isCsrfTokenValid(self::TOKEN, (string) $request->request->get('_token'))) {
            throw $this->createAccessDeniedException('Invalid CSRF token.');
        }
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
