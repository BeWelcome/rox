<?php

namespace App\Model;

use App\Doctrine\ActionToWatchType;
use App\Doctrine\ForumDeleteStatusType;
use App\Doctrine\ForumVisibilityType;
use App\Doctrine\GroupMembershipStatusType;
use App\Doctrine\NotificationStatusType;
use App\Doctrine\ThreadReplyType;
use App\Entity\ForumPost;
use App\Entity\ForumPostTranslation;
use App\Entity\ForumThread;
use App\Entity\ForumThreadTranslation;
use App\Entity\Group;
use App\Entity\GroupMembership;
use App\Entity\Language;
use App\Entity\Member;
use App\Entity\MemberThreadSubscription;
use App\Entity\PostNotification;
use App\Service\ForumSearchIndex;
use DateTime;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Writing in the forums: new threads, replies, edits and translations of posts, thread subscriptions, and the
 * notifications they lead to (sent by SendAndPushNotificationsCommand).
 */
class ForumModel
{
    public const string NOTIFICATION_NEW_THREAD = 'newthread';
    public const string NOTIFICATION_REPLY = 'reply';
    public const string NOTIFICATION_EDIT = 'useredit';

    /** The notification comes from the member's group membership (its notifications_enabled). */
    public const string SUBSCRIPTION_GROUP = 'membersgroups';
    /** The notification comes from a thread subscription. */
    public const string SUBSCRIPTION_THREAD = 'members_threads_subscribed';

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly ForumSearchIndex $searchIndex,
    ) {
    }

    public function createThread(
        Group $group,
        Member $author,
        string $title,
        string $text,
        string $locale,
        string $visibility,
        bool $subscribe,
    ): ForumThread {
        $language = $this->getLanguage($locale);
        $title = trim(strip_tags($title));

        $post = $this->newPost($author, $text, $language, $visibility);
        $thread = new ForumThread()
            ->setTitle($title)
            ->setIdTitle(0)
            ->setLanguage($language)
            ->setGroup($group)
            ->setVisibility($visibility)
            ->setWhoCanReply(ThreadReplyType::MEMBERS_ONLY)
            ->setDeleted(ForumDeleteStatusType::NOT_DELETED)
            ->setFirstPost($post)
            ->setLastPost($post);
        $thread->addTranslation(new ForumThreadTranslation($language->getShortCode(), 'title', $title)->setTranslator($author));
        $post->setThread($thread);

        $this->entityManager->persist($post);
        $this->entityManager->persist($thread);
        $this->entityManager->flush();

        if ($subscribe) {
            $this->subscribe($thread, $author);
        }
        $this->searchIndex->index($post);
        $this->queueNotifications($post, self::NOTIFICATION_NEW_THREAD);

        return $thread;
    }

    public function reply(ForumThread $thread, Member $author, string $text, string $locale, string $visibility, bool $subscribe): ForumPost
    {
        $post = $this->newPost($author, $text, $this->getLanguage($locale), $visibility);
        $post->setThread($thread);
        $thread->setLastPost($post)->setReplies((int) $thread->getReplies() + 1);

        $this->entityManager->persist($post);
        $this->entityManager->flush();

        if ($subscribe) {
            $this->subscribe($thread, $author);
        } else {
            $this->unsubscribe($thread, $author);
        }
        $this->searchIndex->index($post);
        $this->queueNotifications($post, self::NOTIFICATION_REPLY);

        return $post;
    }

    /**
     * Sets the post's text (and the thread's title, for its first post) in a language: an edit of the language it
     * has, or a translation into a new one. The language the post was written in is its own text too.
     */
    public function savePost(ForumPost $post, Member $editor, string $locale, string $text, ?string $title, ?string $visibility): void
    {
        $changed = $this->setTranslation($post, $locale, $text, $editor);
        if ($post->getLanguage()->getShortCode() === $locale) {
            $post->setMessage($text);
        }
        if (null !== $visibility && $visibility !== $post->getPostvisibility()) {
            $post->setPostvisibility($visibility);
            $changed = true;
        }

        $thread = $post->getThread();
        if (null !== $title && null !== $thread && $thread->getFirstPost() === $post) {
            $title = trim(strip_tags($title));
            $this->setTitleTranslation($thread, $locale, $title, $editor);
            if ($thread->getLanguage()?->getShortCode() === $locale) {
                $thread->setTitle($title);
            }
            if (null !== $visibility) {
                $thread->setVisibility($visibility);
            }
        }

        if ($changed) {
            $post
                ->setLastEdittime(new DateTime())
                ->setLastEditorid($editor->getId())
                ->setEditCount((int) $post->getEditCount() + 1);
        }
        $this->entityManager->flush();

        if ($changed) {
            $this->searchIndex->index($post);
            $this->queueNotifications($post, self::NOTIFICATION_EDIT);
        }
    }

    public function isSubscribed(ForumThread $thread, Member $member): bool
    {
        return null !== $this->findSubscription($thread, $member);
    }

    public function subscribe(ForumThread $thread, Member $member): MemberThreadSubscription
    {
        $subscription = $this->findSubscription($thread, $member);
        if (null === $subscription) {
            $subscription = new MemberThreadSubscription();
            $subscription
                ->setSubscriber($member)
                ->setThread($thread)
                ->setActionToWatch(ActionToWatchType::REPLIES)
                ->setUnsubscribeKey(bin2hex(random_bytes(16)))
                ->setNotificationsEnabled(true);
            $subscription->setSubscribed(new DateTime());
            $this->entityManager->persist($subscription);
        } else {
            $subscription->setNotificationsEnabled(true);
        }
        $this->entityManager->flush();

        return $subscription;
    }

    public function unsubscribe(ForumThread $thread, Member $member): void
    {
        $subscription = $this->findSubscription($thread, $member);
        if (null !== $subscription) {
            $this->entityManager->remove($subscription);
            $this->entityManager->flush();
        }
    }

    /**
     * Pauses or resumes the notifications of a thread subscription (it stays).
     */
    public function setThreadNotifications(ForumThread $thread, Member $member, bool $enabled): bool
    {
        $subscription = $this->findSubscription($thread, $member);
        if (null === $subscription) {
            return false;
        }
        $subscription->setNotificationsEnabled($enabled);
        $this->entityManager->flush();

        return true;
    }

    /**
     * Pauses or resumes all of the member's forum notifications: thread subscriptions and group memberships.
     */
    public function setAllThreadNotifications(Member $member, bool $enabled): void
    {
        $this->entityManager->createQueryBuilder()
            ->update(GroupMembership::class, 'gm')
            ->set('gm.notificationsEnabled', ':enabled')
            ->where('gm.member = :member')
            ->setParameter('enabled', $enabled)
            ->setParameter('member', $member)
            ->getQuery()
            ->execute();
        $this->entityManager->createQueryBuilder()
            ->update(MemberThreadSubscription::class, 's')
            ->set('s.notificationsEnabled', ':enabled')
            ->where('s.subscriber = :member')
            ->setParameter('enabled', $enabled)
            ->setParameter('member', $member)
            ->getQuery()
            ->execute();
    }

    /**
     * Switches the notifications of the member's group membership (the group's new threads and replies).
     */
    public function setGroupNotifications(Group $group, Member $member, bool $enabled): bool
    {
        $membership = $this->entityManager->getRepository(GroupMembership::class)->findOneBy([
            'group' => $group,
            'member' => $member,
            'status' => GroupMembershipStatusType::CURRENT_MEMBER,
        ]);
        if (null === $membership) {
            return false;
        }
        $membership->setNotificationsEnabled($enabled);
        $this->entityManager->flush();

        return true;
    }

    /**
     * @return list<MemberThreadSubscription>
     */
    public function getSubscriptions(Member $member): array
    {
        return $this->entityManager->getRepository(MemberThreadSubscription::class)->findBy(['subscriber' => $member], ['id' => 'DESC']);
    }

    /**
     * Queues a notification about the post for the group's members who want them and for the thread's subscribers,
     * once per member and never for its author. Members who can't read the post (group-only posts, members-only
     * groups) get none.
     */
    public function queueNotifications(ForumPost $post, string $type): void
    {
        $thread = $post->getThread();
        if (
            null === $thread
            || ForumDeleteStatusType::NOT_DELETED !== $post->getDeleted()
            || ForumDeleteStatusType::NOT_DELETED !== $thread->getDeleted()
            || ForumVisibilityType::MODERATOR_ONLY === $post->getPostvisibility()
            || ForumVisibilityType::MODERATOR_ONLY === $thread->getVisibility()
        ) {
            return;
        }

        $group = $thread->getGroup();
        $groupMemberIds = $this->getGroupMemberIds($group);
        $groupOnly = !$group->isBeWelcome() && (
            $group->isMembersOnly()
            || ForumVisibilityType::GROUP_ONLY === $post->getPostvisibility()
            || ForumVisibilityType::GROUP_ONLY === $thread->getVisibility()
        );

        $notified = [$post->getAuthor()->getId() => true];
        foreach ($this->getQueuedReceiverIds($post) as $memberId) {
            $notified[$memberId] = true;
        }

        foreach ($this->getGroupNotificationReceivers($group) as $member) {
            if (!isset($notified[$member->getId()])) {
                $this->entityManager->persist($this->newNotification($member, $post, $type, self::SUBSCRIPTION_GROUP, 0));
                $notified[$member->getId()] = true;
            }
        }

        foreach ($this->entityManager->getRepository(MemberThreadSubscription::class)->findBy(['thread' => $thread]) as $subscription) {
            $member = $subscription->getSubscriber();
            if (isset($notified[$member->getId()]) || !$subscription->getNotificationsEnabled()) {
                continue;
            }
            if ($groupOnly && !isset($groupMemberIds[$member->getId()])) {
                continue;
            }
            $this->entityManager->persist(
                $this->newNotification($member, $post, $type, self::SUBSCRIPTION_THREAD, (int) $subscription->getId())
            );
            $notified[$member->getId()] = true;
        }

        $this->entityManager->flush();
    }

    private function newPost(Member $author, string $text, Language $language, string $visibility): ForumPost
    {
        $post = new ForumPost();
        $post
            ->setAuthor($author)
            ->setMessage($text)
            ->setLanguage($language)
            ->setPostvisibility($visibility)
            ->setDeleted(ForumDeleteStatusType::NOT_DELETED)
            ->setCreated(new DateTime());
        $post->addTranslation(new ForumPostTranslation($language->getShortCode(), 'message', $text)->setTranslator($author));

        return $post;
    }

    private function setTranslation(ForumPost $post, string $locale, string $text, Member $translator): bool
    {
        foreach ($post->getTranslations() as $translation) {
            if ($translation->getLocale() === $locale && 'message' === $translation->getField()) {
                if ($translation->getContent() === $text) {
                    return false;
                }
                $translation->setContent($text);
                $translation->setTranslator($translator)->setUpdatedAt(new DateTimeImmutable());

                return true;
            }
        }
        $post->addTranslation(new ForumPostTranslation($locale, 'message', $text)->setTranslator($translator));

        return true;
    }

    private function setTitleTranslation(ForumThread $thread, string $locale, string $title, Member $translator): void
    {
        foreach ($thread->getTranslations() as $translation) {
            if ($translation->getLocale() === $locale && 'title' === $translation->getField()) {
                if ($translation->getContent() !== $title) {
                    $translation->setContent($title);
                    $translation->setTranslator($translator)->setUpdatedAt(new DateTimeImmutable());
                }

                return;
            }
        }
        $thread->addTranslation(new ForumThreadTranslation($locale, 'title', $title)->setTranslator($translator));
    }

    private function newNotification(Member $member, ForumPost $post, string $type, string $table, int $subscription): PostNotification
    {
        return new PostNotification()
            ->setReceiver($member)
            ->setPost($post)
            ->setStatus(NotificationStatusType::SCHEDULED)
            ->setType($type)
            ->setTableSubscription($table)
            ->setSubscription($subscription);
    }

    /**
     * @return list<Member> the group's current members who want its notifications
     */
    private function getGroupNotificationReceivers(Group $group): array
    {
        return $this->entityManager->createQueryBuilder()
            ->select('m')
            ->from(Member::class, 'm')
            ->join(GroupMembership::class, 'gm', 'WITH', 'gm.member = m')
            ->where('gm.group = :group')
            ->andWhere('gm.status = :in')
            ->andWhere('gm.notificationsEnabled = true')
            ->setParameter('group', $group)
            ->setParameter('in', GroupMembershipStatusType::CURRENT_MEMBER)
            ->getQuery()
            ->getResult();
    }

    /**
     * @return array<int, true> the ids of the group's current members
     */
    private function getGroupMemberIds(Group $group): array
    {
        $ids = $this->entityManager->getConnection()->fetchFirstColumn(
            'SELECT member_id FROM group_membership WHERE group_id = ? AND status = ?',
            [$group->getId(), GroupMembershipStatusType::CURRENT_MEMBER],
        );

        return array_fill_keys(array_map('intval', $ids), true);
    }

    /**
     * @return list<int> the members who already have a notification about the post waiting
     */
    private function getQueuedReceiverIds(ForumPost $post): array
    {
        return array_map('intval', $this->entityManager->getConnection()->fetchFirstColumn(
            'SELECT DISTINCT IdMember FROM posts_notificationqueue WHERE IdPost = ? AND Status = ?',
            [$post->getId(), NotificationStatusType::SCHEDULED],
        ));
    }

    private function findSubscription(ForumThread $thread, Member $member): ?MemberThreadSubscription
    {
        return $this->entityManager->getRepository(MemberThreadSubscription::class)->findOneBy([
            'thread' => $thread,
            'subscriber' => $member,
        ]);
    }

    private function getLanguage(string $locale): Language
    {
        $language = $this->entityManager->getRepository(Language::class)->findOneBy(['shortCode' => $locale])
            ?? $this->entityManager->getRepository(Language::class)->findOneBy(['shortCode' => 'en']);
        \assert($language instanceof Language);

        return $language;
    }
}
