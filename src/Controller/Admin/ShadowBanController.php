<?php

namespace App\Controller\Admin;

use App\Entity\Member;
use App\Entity\MemberFlag;
use App\Entity\Message;
use App\Entity\SignupPattern;
use App\Model\Admin\CheckerMenu;
use App\Model\ShadowBanModel;
use App\Utilities\ItemsPerPageTraits;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * The safety team's sign-up patterns and the accounts they flagged (ShadowBanModel), next to the checkers' pages.
 * A flagged account is shadow banned: what it sends is held back here until a volunteer releases a message, or the
 * member with all their messages; a ban is given on the profile as for any member.
 */
class ShadowBanController extends AbstractController
{
    use ItemsPerPageTraits;

    public const string TOKEN = 'shadow_ban';

    public function __construct(
        private readonly ShadowBanModel $shadowBanModel,
        private readonly CheckerMenu $checkerMenu,
        // read by ItemsPerPageTraits
        private readonly EntityManagerInterface $entityManager,
        private readonly TranslatorInterface $translator,
    ) {
    }

    /**
     * The flagged accounts still shadow banned (?released=1: the released ones), each with its held messages.
     */
    #[Route(path: '/admin/spam/flagged', name: 'admin_spam_flagged', methods: ['GET'])]
    public function flagged(Request $request): Response
    {
        $this->denyUnlessSafety();
        $released = $request->query->getBoolean('released');
        /** @var Member $volunteer */
        $volunteer = $this->getUser();
        $flags = $this->shadowBanModel->getFlags($released, max(1, $request->query->getInt('page', 1)), $this->getItemsPerPage($volunteer));
        $held = [];
        foreach ($flags->getCurrentPageResults() as $flag) {
            $held[$flag->getId()] = $released ? [] : $this->shadowBanModel->getHeldMessages($flag->getMember());
        }

        return $this->render('admin/checker/flagged.html.twig', [
            'flags' => $flags,
            'held' => $held,
            'released' => $released,
            'counts' => $this->checkerMenu->counts(),
            'active' => 'flagged',
        ]);
    }

    /**
     * Releases the member: no longer shadow banned, their held messages are delivered.
     */
    #[Route(path: '/admin/spam/flagged/{id}/release', name: 'admin_spam_flagged_release', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function releaseMember(Request $request, MemberFlag $flag): RedirectResponse
    {
        $this->denyUnlessSafety($request);
        if (!$flag->isReleased()) {
            /** @var Member $volunteer */
            $volunteer = $this->getUser();
            $delivered = $this->shadowBanModel->releaseMember($flag, $volunteer);
            $this->addFlash('notice', $this->translator->trans('admin.shadow.released', [
                'username' => $flag->getMember()->getUsername(),
                'count' => $delivered,
            ]));
        }

        return $this->redirectToRoute('admin_spam_flagged');
    }

    /**
     * One held message: delivered (release) or kept back as spam (spam); the member stays shadow banned.
     */
    #[Route(path: '/admin/spam/flagged/message/{id}/{decision}', name: 'admin_spam_flagged_message', requirements: ['id' => '\d+', 'decision' => 'release|spam'], methods: ['POST'])]
    public function decideMessage(Request $request, Message $message, string $decision): RedirectResponse
    {
        $this->denyUnlessSafety($request);
        if ($message->isShadowHeld()) {
            if ('release' === $decision) {
                $this->shadowBanModel->releaseMessages([$message->getId()]);
            } else {
                $this->shadowBanModel->rejectMessages([$message->getId()]);
            }
            $this->addFlash('notice', $this->translator->trans('admin.shadow.message.' . $decision));
        }

        return $this->redirect($this->generateUrl('admin_spam_flagged') . '#flag-' . $message->getSender()->getId());
    }

    /**
     * The patterns, a form to add one; after adding, the accounts of the last days it matches, to flag them too.
     */
    #[Route(path: '/admin/spam/patterns', name: 'admin_spam_patterns', methods: ['GET', 'POST'])]
    public function patterns(Request $request): Response
    {
        $this->denyUnlessSafety();
        $error = null;
        if ($request->isMethod('POST')) {
            $this->checkToken($request);
            $text = trim($request->request->getString('pattern'));
            // at least three characters that aren't *: a shorter text matches far too many accounts
            if (mb_strlen(str_replace('*', '', $text)) < 3) {
                $error = 'admin.shadow.pattern.too.short';
            } else {
                /** @var Member $volunteer */
                $volunteer = $this->getUser();
                $pattern = $this->shadowBanModel->addPattern($text, $request->request->getString('field'), $request->request->getString('note'), $volunteer);
                $this->addFlash('notice', $this->translator->trans('admin.shadow.pattern.added', ['pattern' => $pattern->getPattern()]));

                return $this->redirectToRoute('admin_spam_pattern_matches', ['id' => $pattern->getId()]);
            }
        }

        return $this->render('admin/checker/patterns.html.twig', [
            'patterns' => $this->shadowBanModel->getPatterns(),
            'flagged' => $this->shadowBanModel->getFlaggedCounts(),
            'error' => $error,
            'fields' => SignupPattern::FIELDS,
            'counts' => $this->checkerMenu->counts(),
            'active' => 'patterns',
        ], new Response(status: null !== $error ? Response::HTTP_UNPROCESSABLE_ENTITY : Response::HTTP_OK));
    }

    /**
     * The accounts of the last days the pattern matches and that aren't flagged; the ticked ones are flagged.
     */
    #[Route(path: '/admin/spam/patterns/{id}/matches', name: 'admin_spam_pattern_matches', requirements: ['id' => '\d+'], methods: ['GET', 'POST'])]
    public function matches(Request $request, SignupPattern $pattern): Response
    {
        $this->denyUnlessSafety();
        $matches = $this->shadowBanModel->getRecentMatches($pattern);
        if ($request->isMethod('POST')) {
            $this->checkToken($request);
            $ticked = array_map('intval', $request->request->all('members'));
            $members = array_filter(
                array_map(static fn (array $match): Member => $match['member'], $matches),
                static fn (Member $member): bool => \in_array($member->getId(), $ticked, true),
            );
            $flagged = $this->shadowBanModel->flagMembers($members, $pattern);
            $this->addFlash('notice', $this->translator->trans('admin.shadow.flagged.count', ['count' => $flagged]));

            return $this->redirectToRoute('admin_spam_flagged');
        }

        return $this->render('admin/checker/pattern_matches.html.twig', [
            'pattern' => $pattern,
            'matches' => $matches,
            'days' => ShadowBanModel::RECENT_DAYS,
            'counts' => $this->checkerMenu->counts(),
            'active' => 'patterns',
        ]);
    }

    /**
     * Switches a pattern off (new accounts no longer flagged by it) or on again, or removes it; the accounts it
     * flagged stay flagged.
     */
    #[Route(path: '/admin/spam/patterns/{id}/{action}', name: 'admin_spam_pattern_action', requirements: ['id' => '\d+', 'action' => 'on|off|remove'], methods: ['POST'])]
    public function patternAction(Request $request, SignupPattern $pattern, string $action): RedirectResponse
    {
        $this->denyUnlessSafety($request);
        match ($action) {
            'remove' => $this->shadowBanModel->removePattern($pattern),
            default => $this->shadowBanModel->setActive($pattern, 'on' === $action),
        };

        return $this->redirectToRoute('admin_spam_patterns');
    }

    /**
     * The safety team, and the checkers who deal with the spam (as on the checkers' pages); a POST needs the token.
     */
    private function denyUnlessSafety(?Request $request = null): void
    {
        if (!$this->isGranted(Member::ROLE_ADMIN_SAFETYTEAM) && !$this->isGranted(Member::ROLE_ADMIN_CHECKER)) {
            throw $this->createAccessDeniedException('You need the SafetyTeam or Checker right to access this.');
        }
        if (null !== $request) {
            $this->checkToken($request);
        }
    }

    private function checkToken(Request $request): void
    {
        if (!$this->isCsrfTokenValid(self::TOKEN, $request->request->getString('_token'))) {
            throw $this->createAccessDeniedException('Invalid CSRF token.');
        }
    }
}
