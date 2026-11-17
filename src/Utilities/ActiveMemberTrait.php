<?php

namespace App\Utilities;

use App\Doctrine\MemberStatusType;
use App\Entity\Member;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * Members who aren't active yet (Member::isActiveMember()) don't contact others, plan trips or create groups. The
 * status is the first reason to turn them down, before anything about the profile or the other member.
 *
 * Needs addTranslatedFlash() (TranslatedFlashTrait) and redirectToRoute() (AbstractController).
 */
trait ActiveMemberTrait
{
    /**
     * Null for an active member. Otherwise a member who hasn't given the details of the sign-up yet goes to its setup
     * page, asked to finish it; one who has (the address isn't confirmed) goes $elsewhere with the $notice.
     */
    protected function refusedUntilActive(Member $member, string $notice, RedirectResponse $elsewhere): ?RedirectResponse
    {
        if ($member->isActiveMember()) {
            return null;
        }

        if (\in_array($member->getStatus(), [MemberStatusType::AWAITING_MAIL_CONFIRMATION, MemberStatusType::MAIL_CONFIRMED], true)) {
            $this->addTranslatedFlash('notice', 'flash.signup.finish.first');

            return $this->redirectToRoute('finish_setup', ['username' => $member->getUsername()]);
        }

        $this->addTranslatedFlash('notice', $notice);

        return $elsewhere;
    }
}
