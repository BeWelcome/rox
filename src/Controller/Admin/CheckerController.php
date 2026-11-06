<?php

namespace App\Controller\Admin;

use App\Doctrine\SpamInfoType;
use App\Entity\Member;
use App\Model\ActivityModel;
use App\Model\Admin\CheckerModel;
use App\Model\CommunityNewsModel;
use App\Utilities\ItemsPerPageTraits;
use Doctrine\ORM\EntityManagerInterface;
use Pagerfanta\Exception\OutOfRangeCurrentPageException;
use Pagerfanta\Pagerfanta;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * The checkers' pages: the messages members reported and the ones with a block word (one decision per message, spam
 * or not spam), the decided ones to correct a decision, the activities that look like spam and the reported community
 * news comments. The menu with the open counts is on every page (admin/checker/_menu.html.twig).
 */
class CheckerController extends AbstractController
{
    use ItemsPerPageTraits;

    /** The CSRF token id of keeping and deleting reported community news comments. */
    public const string COMMUNITY_NEWS_TOKEN = 'checker_community_news';

    /** The CSRF token id of the decisions on messages and of deleting activities. */
    public const string TOKEN = 'checker';

    public const string SPAM = 'spam';
    public const string NOT_SPAM = 'ok';

    /** The four message sections: the menu entry and the route */
    private const array MESSAGES = [
        'messages' => 'admin_spam_messages',
        'processed_messages' => 'admin_spam_messages_processed',
        'blocked_words' => 'admin_spam_messages_block_words',
        'processed_blocked_words' => 'admin_spam_messages_block_words_processed',
    ];

    public function __construct(
        private readonly CheckerModel $checkerModel,
        private readonly ActivityModel $activityModel,
        private readonly CommunityNewsModel $communityNewsModel,
        private readonly EntityManagerInterface $entityManager,
        private readonly TranslatorInterface $translator,
    ) {
    }

    #[Route(path: '/admin/spam/messages', name: 'admin_spam_messages', methods: ['GET', 'POST'])]
    public function showReportedMessages(Request $request): Response
    {
        return $this->handleMessages($request, 'messages');
    }

    #[Route(path: '/admin/spam/messages/processed', name: 'admin_spam_messages_processed', methods: ['GET', 'POST'])]
    public function showProcessedMessages(Request $request): Response
    {
        return $this->handleMessages($request, 'processed_messages');
    }

    #[Route(path: '/admin/spam/messages/blocked', name: 'admin_spam_messages_block_words', methods: ['GET', 'POST'])]
    public function showBlockWordMessages(Request $request): Response
    {
        return $this->handleMessages($request, 'blocked_words');
    }

    #[Route(
        path: '/admin/spam/messages/blocked/processed',
        name: 'admin_spam_messages_block_words_processed',
        methods: ['GET', 'POST'],
    )]
    public function showProcessedBlockWordMessages(Request $request): Response
    {
        return $this->handleMessages($request, 'processed_blocked_words');
    }

    /**
     * The activities that look like spam (an organizer is banned or they last longer than a day): Delete removes one,
     * Delete selected the ticked ones.
     */
    #[Route(path: '/admin/spam/activities', name: 'admin_spam_activities', methods: ['GET', 'POST'])]
    public function showActivities(Request $request): Response
    {
        $this->denyUnlessChecker();
        $page = max(1, $request->query->getInt('page', 1));

        if ($request->isMethod('POST')) {
            $this->checkToken($request, self::TOKEN);
            $delete = $request->request->getInt('delete');
            $ids = 0 < $delete ? [$delete] : array_values(array_filter(array_map('intval', $request->request->all('ids'))));
            if ([] !== $ids) {
                $this->activityModel->deleteAsSpamByChecker($ids);
                $this->addFlash('notice', $this->translator->trans('admin.checker.activities.deleted', ['count' => \count($ids)]));
            }

            return $this->redirectToRoute('admin_spam_activities', array_filter(['page' => 1 < $page ? $page : null]));
        }

        $activities = $this->page(fn (int $page): Pagerfanta => $this->activityModel->getProblematicActivities($page, 10), $page);

        return $this->render('admin/checker/activities.html.twig', [
            'activities' => $activities,
            'counts' => $this->counts(),
            'active' => 'activities',
        ]);
    }

    /**
     * The community news comments members reported: Keep clears a comment's reports, Delete removes it (one with
     * replies stays as "Comment removed"), also for the selected ones.
     */
    #[Route(path: '/admin/spam/communitynews', name: 'admin_spam_community_news', methods: ['GET', 'POST'])]
    public function showCommunityNewsComments(Request $request): Response
    {
        $this->denyUnlessChecker();

        if ($request->isMethod('POST')) {
            $this->checkToken($request, self::COMMUNITY_NEWS_TOKEN);
            $keep = $request->request->getInt('keep');
            $delete = $request->request->getInt('delete');
            $selected = array_values(array_filter(array_map('intval', $request->request->all('ids'))));
            if (0 < $keep) {
                $this->communityNewsModel->keep([$keep]);
                $this->addFlash('notice', $this->translator->trans('admin.checker.communitynews.kept', ['count' => 1]));
            } elseif (0 < $delete || [] !== $selected) {
                $ids = 0 < $delete ? [$delete] : $selected;
                $this->communityNewsModel->deleteAsSpamByChecker($ids);
                $this->addFlash('notice', $this->translator->trans('admin.checker.communitynews.deleted', ['count' => \count($ids)]));
            }

            return $this->redirectToRoute('admin_spam_community_news', array_filter(['page' => $request->query->getInt('page')]));
        }

        return $this->render('admin/checker/communitynews.html.twig', [
            'reported' => $this->communityNewsModel->getReportedComments($request->query->getInt('page', 1), 20),
            'counts' => $this->counts(),
            'active' => 'community_news',
        ]);
    }

    #[Route(path: '/admin/spam', name: 'admin_spam')]
    public function redirectToSpamMessages(): RedirectResponse
    {
        return $this->redirectToRoute('admin_spam_messages');
    }

    /**
     * A message section: the decisions (decisions[id] = ok or spam) are saved for the messages of the page; the
     * decided ones leave the open sections.
     */
    private function handleMessages(Request $request, string $section): Response
    {
        $this->denyUnlessChecker();
        $page = max(1, $request->query->getInt('page', 1));
        /** @var Member $member */
        $member = $this->getUser();
        $limit = $this->getItemsPerPage($member);

        if ($request->isMethod('POST')) {
            $this->checkToken($request, self::TOKEN);
            $onPage = array_map(
                static fn ($message): int => $message->getId(),
                iterator_to_array($this->messages($section, $page, $limit)->getCurrentPageResults(), false),
            );
            $spam = [];
            $notSpam = [];
            foreach ($request->request->all('decisions') as $id => $decision) {
                if (!\in_array((int) $id, $onPage, true)) {
                    continue;
                }
                if (self::SPAM === $decision) {
                    $spam[] = (int) $id;
                } elseif (self::NOT_SPAM === $decision) {
                    $notSpam[] = (int) $id;
                }
            }
            if ([] !== $spam || [] !== $notSpam) {
                $this->checkerModel->markAsSpamByChecker($spam);
                $this->checkerModel->unmarkAsSpamByChecker($notSpam);
                $this->addFlash('notice', $this->translator->trans('admin.checker.messages.saved', [
                    'count' => \count($spam) + \count($notSpam),
                    'spam' => \count($spam),
                    'ok' => \count($notSpam),
                ]));
            }

            return $this->redirectToRoute(self::MESSAGES[$section], array_filter(['page' => 1 < $page ? $page : null]));
        }

        $messages = $this->page(fn (int $page): Pagerfanta => $this->messages($section, $page, $limit), $page);

        return $this->render('admin/checker/messages.html.twig', [
            'messages' => $messages,
            'counts' => $this->counts(),
            'active' => $section,
            'block_words' => \in_array($section, ['blocked_words', 'processed_blocked_words'], true),
            'processed' => \in_array($section, ['processed_messages', 'processed_blocked_words'], true),
            'spam_flag' => SpamInfoType::CHECKER_SAYS_SPAM,
        ]);
    }

    private function messages(string $section, int $page, int $limit): Pagerfanta
    {
        return match ($section) {
            'messages' => $this->checkerModel->getReportedMessages($page, $limit),
            'processed_messages' => $this->checkerModel->getProcessedReportedMessages($page, $limit),
            'blocked_words' => $this->checkerModel->getBlockWordsMessages($page, $limit),
            default => $this->checkerModel->getProcessedBlockWordsMessages($page, $limit),
        };
    }

    /**
     * The page, or the last one when it is gone (after the last rows of the last page were decided).
     *
     * @param callable(int): Pagerfanta $pager
     */
    private function page(callable $pager, int $page): Pagerfanta
    {
        try {
            return $pager($page);
        } catch (OutOfRangeCurrentPageException) {
            $first = $pager(1);

            return 1 < $first->getNbPages() ? $pager($first->getNbPages()) : $first;
        }
    }

    /**
     * @return array<string, int> what is open in the sections of the menu
     */
    private function counts(): array
    {
        return [
            'messages' => $this->checkerModel->getReportedMessages(1, 1)->getNbResults(),
            'blocked_words' => $this->checkerModel->getBlockWordsMessages(1, 1)->getNbResults(),
            'activities' => $this->activityModel->getProblematicActivities(1, 1)->getNbResults(),
            'community_news' => $this->communityNewsModel->countReportedComments(),
        ];
    }

    private function checkToken(Request $request, string $id): void
    {
        if (!$this->isCsrfTokenValid($id, $request->request->getString('_token'))) {
            throw $this->createAccessDeniedException('Invalid CSRF token.');
        }
    }

    private function denyUnlessChecker(): void
    {
        if (!$this->isGranted(Member::ROLE_ADMIN_CHECKER) && !$this->isGranted(Member::ROLE_ADMIN_SAFETYTEAM)) {
            throw $this->createAccessDeniedException('You need to have Checker right to access this.');
        }
    }
}
