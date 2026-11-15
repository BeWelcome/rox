<?php

namespace App\Security;

use App\Entity\Member;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Vote;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

/**
 * Who may see a member: their profile, gallery, picture and preview, and contact them (MEMBER_VIEW); and who may edit
 * their profile (MEMBER_EDIT): the member, and admins and the safety team (the Admin box's "Admin: Edit Profile").
 *
 * Everybody sees browsable members who finished the sign-up. Members who just signed up (e-mail not confirmed or
 * details not filled in) and members who aren't browsable (banned, taken out, ...) are seen only by themselves and
 * by volunteers who need to: admins, the safety team, checkers and profile admins.
 *
 * @extends Voter<string, Member>
 */
class MemberVoter extends Voter
{
    public const string MEMBER_VIEW = 'MEMBER_VIEW';
    public const string MEMBER_EDIT = 'MEMBER_EDIT';

    private const array EDITORS = [
        Member::ROLE_ADMIN_ADMIN,
        Member::ROLE_ADMIN_SAFETYTEAM,
    ];

    private const array VOLUNTEERS = [
        Member::ROLE_ADMIN_ADMIN,
        Member::ROLE_ADMIN_SAFETYTEAM,
        Member::ROLE_ADMIN_CHECKER,
        Member::ROLE_ADMIN_PROFILE,
    ];

    public function __construct(private readonly AccessDecisionManagerInterface $accessDecisionManager)
    {
    }

    protected function supports(string $attribute, mixed $subject): bool
    {
        return \in_array($attribute, [self::MEMBER_VIEW, self::MEMBER_EDIT], true) && $subject instanceof Member;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token, ?Vote $vote = null): bool
    {
        $viewer = $token->getUser();
        if (!$viewer instanceof Member) {
            return false;
        }

        /** @var Member $member */
        $member = $subject;
        if ($viewer === $member) {
            return true;
        }

        if (self::MEMBER_VIEW === $attribute && $member->isVisibleToMembers()) {
            return true;
        }

        foreach (self::MEMBER_EDIT === $attribute ? self::EDITORS : self::VOLUNTEERS as $role) {
            if ($this->accessDecisionManager->decide($token, [$role])) {
                return true;
            }
        }

        return false;
    }
}
