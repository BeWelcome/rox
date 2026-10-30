<?php

namespace App\Security;

use App\Doctrine\GroupMembershipStatusType;
use App\Entity\Group;
use App\Entity\Member;
use App\Repository\GroupRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Vote;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

/**
 * What a member may do with a group:
 * - GROUP_VIEW: see the group's page; approved groups for every member, others for their admins and the group admin
 *   team only;
 * - GROUP_VIEW_FORUM: read the group's forum; members-only groups for their members, admins and the group admin team;
 * - GROUP_ADMINISTER: manage the group (its admins and the group admin team).
 *
 * @extends Voter<string, Group>
 */
class GroupVoter extends Voter
{
    public const string GROUP_VIEW = 'GROUP_VIEW';
    public const string GROUP_VIEW_FORUM = 'GROUP_VIEW_FORUM';
    public const string GROUP_ADMINISTER = 'GROUP_ADMINISTER';

    private const array ATTRIBUTES = [self::GROUP_VIEW, self::GROUP_VIEW_FORUM, self::GROUP_ADMINISTER];

    public function __construct(
        private readonly AccessDecisionManagerInterface $accessDecisionManager,
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    protected function supports(string $attribute, mixed $subject): bool
    {
        return \in_array($attribute, self::ATTRIBUTES, true) && $subject instanceof Group;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token, ?Vote $vote = null): bool
    {
        $member = $token->getUser();
        if (!$member instanceof Member) {
            return false;
        }

        /** @var Group $group */
        $group = $subject;
        $administers = $this->accessDecisionManager->decide($token, [Member::ROLE_ADMIN_GROUP]) || $group->isAdmin($member);

        return match ($attribute) {
            self::GROUP_ADMINISTER => $administers,
            self::GROUP_VIEW => $administers || Group::APPROVED === $group->getApproved(),
            self::GROUP_VIEW_FORUM => $administers || !$group->isMembersOnly() || $this->isMember($group, $member),
            default => false,
        };
    }

    private function isMember(Group $group, Member $member): bool
    {
        /** @var GroupRepository $repository */
        $repository = $this->entityManager->getRepository(Group::class);

        return GroupMembershipStatusType::CURRENT_MEMBER === $repository->findMembershipStatus($group, $member);
    }
}
