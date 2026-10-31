<?php

namespace App\Model;

use App\Entity\Friend;
use App\Entity\Member;
use App\Service\BrowserNotificationPayload;
use App\Service\BrowserNotificationService;
use App\Service\Mailer;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Asking for a friendship and answering it.
 *
 * - the receiver accepts or declines a request;
 * - the requester withdraws it while it is pending;
 * - either side removes a confirmed friendship.
 */
final readonly class FriendshipModel
{
    public const string ACCEPT = 'accept';
    public const string DECLINE = 'decline';
    public const string WITHDRAW = 'withdraw';
    public const string REMOVE = 'remove';

    public function __construct(
        private EntityManagerInterface $entityManager,
        private Mailer $mailer,
        private BrowserNotificationService $browserNotificationService,
        private UrlGeneratorInterface $urlGenerator,
    ) {
    }

    /**
     * $requester asks $receiver to confirm their friendship; the receiver gets a mail and a browser notification.
     */
    public function request(Member $requester, Member $receiver): Friend
    {
        $friend = Friend::request($requester, $receiver);
        $this->entityManager->persist($friend);
        $this->entityManager->flush();

        $this->mailer->sendFriendshipNotification($friend);
        $this->browserNotificationService->queue(
            $receiver,
            BrowserNotificationPayload::friendship(
                $requester,
                $this->urlGenerator->generate('friendships', ['username' => $receiver->getUsername()])
            )
        );

        return $friend;
    }

    /**
     * The actions $member may take on the friendship.
     *
     * @return string[]
     */
    public function getActions(Friend $friend, Member $member): array
    {
        if (!$this->belongsTo($friend, $member)) {
            return [];
        }

        if ($friend->isConfirmed()) {
            return [self::REMOVE];
        }

        return $friend->isRequester($member) ? [self::WITHDRAW] : [self::ACCEPT, self::DECLINE];
    }

    /**
     * Applies the action of $member. False if they may not take it.
     */
    public function answer(Friend $friend, Member $member, string $action): bool
    {
        if (!\in_array($action, $this->getActions($friend, $member), true)) {
            return false;
        }

        if (self::ACCEPT === $action) {
            $friend->confirm();
        } else {
            $this->entityManager->remove($friend);
        }
        $this->entityManager->flush();

        return true;
    }

    private function belongsTo(Friend $friend, Member $member): bool
    {
        return $friend->getLeft()->getId() === $member->getId() || $friend->getRight()->getId() === $member->getId();
    }
}
