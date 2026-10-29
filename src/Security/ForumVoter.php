<?php

namespace App\Security;

use App\Doctrine\ForumDeleteStatusType;
use App\Doctrine\ForumVisibilityType;
use App\Doctrine\GroupMembershipStatusType;
use App\Entity\ForumPost;
use App\Entity\ForumThread;
use App\Entity\Group;
use App\Entity\Member;
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
 * @extends Voter<string, ForumThread|ForumPost>
 */
class ForumVoter extends Voter
{
    public const string THREAD_VIEW = 'FORUM_THREAD_VIEW';
    public const string THREAD_REPLY = 'FORUM_THREAD_REPLY';
    public const string POST_VIEW = 'FORUM_POST_VIEW';

    public function __construct(
        private readonly AccessDecisionManagerInterface $accessDecisionManager,
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    protected function supports(string $attribute, mixed $subject): bool
    {
        return match ($attribute) {
            self::THREAD_VIEW, self::THREAD_REPLY => $subject instanceof ForumThread,
            self::POST_VIEW => $subject instanceof ForumPost,
            default => false,
        };
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token, ?Vote $vote = null): bool
    {
        $member = $token->getUser();
        if (!$member instanceof Member) {
            return false;
        }
        if ($this->isModerator($token)) {
            return true;
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

    private function isModerator(TokenInterface $token): bool
    {
        return $this->accessDecisionManager->decide($token, [Member::ROLE_ADMIN_FORUMMODERATOR])
            || $this->accessDecisionManager->decide($token, [Member::ROLE_ADMIN_ADMIN]);
    }
}
