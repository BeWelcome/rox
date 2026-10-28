<?php

namespace App\Security;

use App\Doctrine\MemberStatusType;
use App\Entity\Member;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Vote;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

/**
 * Who may see a member: their profile, gallery, picture and preview, and contact them.
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
        return self::MEMBER_VIEW === $attribute && $subject instanceof Member;
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

        if ($member->isBrowsable() && !\in_array($member->getStatus(), MemberStatusType::SIGNUP_UNFINISHED, true)) {
            return true;
        }

        foreach (self::VOLUNTEERS as $role) {
            if ($this->accessDecisionManager->decide($token, [$role])) {
                return true;
            }
        }

        return false;
    }
}
