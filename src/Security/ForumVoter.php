<?php

namespace App\Security;

use App\Doctrine\ForumDeleteStatusType;
use App\Doctrine\ForumVisibilityType;
use App\Doctrine\GroupMembershipStatusType;
use App\Doctrine\PostCanStillEditType;
use App\Entity\Flag;
use App\Entity\FlagMember;
use App\Entity\ForumPost;
use App\Entity\ForumThread;
use App\Entity\Group;
use App\Entity\Member;
use App\Repository\FlagMemberRepository;
use App\Repository\GroupRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Vote;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

/**
 * Who may read a thread or a post, and reply to a thread.
 *
 * The group decides first: its forum can be read by whoever may read it (GroupVoter::GROUP_VIEW_FORUM), and its
 * members reply (everybody in the BeWelcome group's agora). Within that a thread or post can be visible to fewer
 * members: the group's members only, or moderators only. Deleted threads and posts are for moderators; forum
 * moderators and admins see everything.
 *
 * Writing is closed to members with the NotAllowedToPostInForum flag.
 *
 * @extends Voter<string, ForumThread|ForumPost|Group>
 */
class ForumVoter extends Voter
{
    public const string THREAD_VIEW = 'FORUM_THREAD_VIEW';
    public const string THREAD_REPLY = 'FORUM_THREAD_REPLY';
    public const string POST_VIEW = 'FORUM_POST_VIEW';
    /** Edit or translate a post: its author while it can still be edited, and moderators. */
    public const string POST_EDIT = 'FORUM_POST_EDIT';
    /** Start a thread in a group's forum. */
    public const string GROUP_POST = 'FORUM_GROUP_POST';

    private const string NOT_ALLOWED_TO_POST = 'NotAllowedToPostInForum';

    public function __construct(
        private readonly AccessDecisionManagerInterface $accessDecisionManager,
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    protected function supports(string $attribute, mixed $subject): bool
    {
        return match ($attribute) {
            self::THREAD_VIEW, self::THREAD_REPLY => $subject instanceof ForumThread,
            self::POST_VIEW, self::POST_EDIT => $subject instanceof ForumPost,
            self::GROUP_POST => $subject instanceof Group,
            default => false,
        };
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token, ?Vote $vote = null): bool
    {
        $member = $token->getUser();
        if (!$member instanceof Member) {
            return false;
        }
        $writes = \in_array($attribute, [self::THREAD_REPLY, self::POST_EDIT, self::GROUP_POST], true);
        if ($writes && $this->isNotAllowedToPost($member)) {
            return false;
        }
        if ($this->isModerator($token)) {
            return true;
        }

        if ($subject instanceof Group) {
            return $this->accessDecisionManager->decide($token, [GroupVoter::GROUP_VIEW], $subject)
                && $this->accessDecisionManager->decide($token, [GroupVoter::GROUP_VIEW_FORUM], $subject)
                && $this->isGroupMember($subject, $member);
        }

        if ($subject instanceof ForumPost && self::POST_EDIT === $attribute) {
            return $subject->getAuthor() === $member
                && PostCanStillEditType::CAN_STILL_EDIT === $subject->getOwnerCanStillEdit()
                && $this->accessDecisionManager->decide($token, [self::POST_VIEW], $subject);
        }

        if ($subject instanceof ForumPost) {
            $thread = $subject->getThread();

            return null !== $thread
                && ForumDeleteStatusType::NOT_DELETED === $subject->getDeleted()
                && $this->canViewThread($thread, $member, $token)
                && $this->isVisible($subject->getPostvisibility(), $thread->getGroup(), $member);
        }

        /** @var ForumThread $thread */
        $thread = $subject;
        if (!$this->canViewThread($thread, $member, $token)) {
            return false;
        }

        if (self::THREAD_REPLY === $attribute) {
            return $this->isGroupMember($thread->getGroup(), $member);
        }

        return true;
    }

    private function canViewThread(ForumThread $thread, Member $member, TokenInterface $token): bool
    {
        $group = $thread->getGroup();
        if (ForumDeleteStatusType::NOT_DELETED !== $thread->getDeleted()) {
            return false;
        }
        if (null !== $group && !$this->accessDecisionManager->decide($token, [GroupVoter::GROUP_VIEW_FORUM], $group)) {
            return false;
        }

        return $this->isVisible($thread->getVisibility(), $group, $member);
    }

    private function isVisible(?string $visibility, ?Group $group, Member $member): bool
    {
        return match ($visibility) {
            ForumVisibilityType::MODERATOR_ONLY => false,
            ForumVisibilityType::GROUP_ONLY => $this->isGroupMember($group, $member),
            default => true,
        };
    }

    /**
     * Everybody is in the agora; elsewhere it takes a current membership.
     */
    private function isGroupMember(?Group $group, Member $member): bool
    {
        if (null === $group || $group->isBeWelcome()) {
            return true;
        }

        /** @var GroupRepository $repository */
        $repository = $this->entityManager->getRepository(Group::class);

        return GroupMembershipStatusType::CURRENT_MEMBER === $repository->findMembershipStatus($group, $member);
    }

    private function isNotAllowedToPost(Member $member): bool
    {
        $flag = $this->entityManager->getRepository(Flag::class)->findOneBy(['name' => self::NOT_ALLOWED_TO_POST]);
        if (null === $flag) {
            return false;
        }
        /** @var FlagMemberRepository $repository */
        $repository = $this->entityManager->getRepository(FlagMember::class);
        $current = $repository->findCurrent($member, $flag);

        return null !== $current && 0 !== $current->getLevel();
    }

    private function isModerator(TokenInterface $token): bool
    {
        return $this->accessDecisionManager->decide($token, [Member::ROLE_ADMIN_FORUMMODERATOR])
            || $this->accessDecisionManager->decide($token, [Member::ROLE_ADMIN_ADMIN]);
    }
}
