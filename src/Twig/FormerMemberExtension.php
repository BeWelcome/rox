<?php

namespace App\Twig;

use App\Entity\Member;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;
use Twig\TwigTest;

/**
 * Members retired by data:retention (Member::isRetired(): renamed retired_<id>) are shown to members as a former
 * member, without a link to their profile: `member is former_member`, `member_name(member)`, for a member or a
 * username (the macros of macros.twig take usernames). The admin pages keep showing retired_<id>.
 */
class FormerMemberExtension extends AbstractExtension
{
    public function __construct(private readonly TranslatorInterface $translator)
    {
    }

    public function getTests(): array
    {
        return [
            new TwigTest('former_member', $this->isFormerMember(...)),
        ];
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('member_name', $this->memberName(...)),
            new TwigFunction('member_display_name', $this->memberDisplayName(...)),
        ];
    }

    public function isFormerMember(mixed $member): bool
    {
        return match (true) {
            $member instanceof Member => $member->isRetired(),
            // data:retention's name for a retired member; sign-ups can't take it (no underscore before digits only)
            \is_string($member) => 1 === preg_match('/^retired_\d+$/', $member),
            default => false,
        };
    }

    /**
     * The username, or "Former member" for a retired member.
     */
    public function memberName(Member|string|null $member): string
    {
        if (null === $member) {
            return '';
        }
        if ($this->isFormerMember($member)) {
            return $this->translator->trans('forum.former.member');
        }

        return $member instanceof Member ? $member->getUsername() : $member;
    }

    /**
     * The short name, else the name unless hidden, else the username (Member::getDisplayName()), or "Former
     * member" for a retired member.
     */
    public function memberDisplayName(Member|string|null $member): string
    {
        if ($member instanceof Member && !$this->isFormerMember($member)) {
            return $member->getDisplayName();
        }

        return $this->memberName($member);
    }
}
