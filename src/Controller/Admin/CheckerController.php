<?php

namespace App\Controller\Admin;

use App\Entity\Member;
use App\Form\SpamActivitiesIndexFormType;
use App\Form\SpamMessagesIndexFormType;
use App\Model\ActivityModel;
use App\Model\Admin\CheckerModel;
use App\Model\CommunityNewsModel;
use App\Utilities\ItemsPerPageTraits;
use Doctrine\ORM\EntityManagerInterface;
use InvalidArgumentException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

class CheckerController extends AbstractController
{
    use ItemsPerPageTraits;

    /** The CSRF token id of keeping and deleting reported community news comments. */
    public const string COMMUNITY_NEWS_TOKEN = 'checker_community_news';

    private const int MESSAGES_REPORTED = 1;
    private const int MESSAGES_PROCESSED = 2;
    private const int MESSAGES_BLOCK_WORDS = 3;
    private const int MESSAGES_BLOCK_WORDS_PROCESSED = 4;

    public function __construct(
        private readonly CheckerModel $checkerModel,
        private readonly EntityManagerInterface $entityManager,
        private readonly TranslatorInterface $translator,
    ) {
    }

    #[Route(path: '/admin/spam/messages', name: 'admin_spam_messages')]
    public function showReportedMessages(Request $request): Response
    {
        return $this->handleMessages($request, self::MESSAGES_REPORTED);
    }

    #[Route(path: '/admin/spam/messages/processed', name: 'admin_spam_messages_processed')]
    public function showProcessedMessages(Request $request): Response
    {
        return $this->handleMessages($request, self::MESSAGES_PROCESSED);
    }

    #[Route(path: '/admin/spam/messages/blocked', name: 'admin_spam_messages_block_words')]
    public function showBlockWordMessages(Request $request): Response
    {
        return $this->handleMessages($request, self::MESSAGES_BLOCK_WORDS);
    }

    #[Route(path: '/admin/spam/messages/blocked/processed', name: 'admin_spam_messages_block_words_processed')]
    public function showProcessedBlockWordMessages(Request $request): Response
    {
        return $this->handleMessages($request, self::MESSAGES_BLOCK_WORDS_PROCESSED);
    }

    #[Route(path: '/admin/spam/activities', name: 'admin_spam_activities')]
    public function showActivities(Request $request, ActivityModel $activitiesModel): Response
    {
        if (
            !$this->isGranted(Member::ROLE_ADMIN_CHECKER)
            && !$this->isGranted(Member::ROLE_ADMIN_SAFETYTEAM)
        ) {
            throw $this->createAccessDeniedException('You need to have Group right to access this.');
        }

        $page = $request->query->get('page', 1);
        $limit = $request->query->get('limit', 10);

        $latestActivities = $activitiesModel->getProblematicActivities($page, $limit);
        $activityIds = [];
        foreach ($latestActivities->getIterator() as $key => $val) {
            $activityIds[$key] = $val->getId();
        }

        $form = $this->createForm(SpamActivitiesIndexFormType::class, null, [
            'ids' => $activityIds,
        ]);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $activitiesModel->deleteAsSpamByChecker($data['spamActivities']);
            $this->addFlash('notice', 'deleted spam activities');

            return $this->redirectToRoute('admin_spam_activities');
        }

        return $this->render('admin/checker/activities.html.twig', [
            'form' => $form->createView(),
            'reported' => $latestActivities,
            'submenu' => [
                'active' => 'activities',
                'items' => $this->getSubmenuItems(),
            ],
        ]);
    }

    /**
     * The community news comments members reported: Keep clears a comment's reports, Delete removes it (one with
     * replies stays as "Comment removed"), also for the selected ones.
     */
    #[Route(path: '/admin/spam/communitynews', name: 'admin_spam_community_news')]
    public function showCommunityNewsComments(Request $request, CommunityNewsModel $communityNewsModel): Response
    {
        if (
            !$this->isGranted(Member::ROLE_ADMIN_CHECKER)
            && !$this->isGranted(Member::ROLE_ADMIN_SAFETYTEAM)
        ) {
            throw $this->createAccessDeniedException('You need to have Checker right to access this.');
        }

        if ($request->isMethod('POST')) {
            if (!$this->isCsrfTokenValid(self::COMMUNITY_NEWS_TOKEN, $request->request->getString('_token'))) {
                throw $this->createAccessDeniedException('Invalid CSRF token.');
            }
            $keep = $request->request->getInt('keep');
            $delete = $request->request->getInt('delete');
            $selected = array_values(array_filter(array_map('intval', $request->request->all('ids'))));
            if (0 < $keep) {
                $communityNewsModel->keep([$keep]);
                $this->addFlash('notice', $this->translate('admin.checker.communitynews.kept', ['count' => 1]));
            } elseif (0 < $delete || [] !== $selected) {
                $ids = 0 < $delete ? [$delete] : $selected;
                $communityNewsModel->deleteAsSpamByChecker($ids);
                $this->addFlash('notice', $this->translate('admin.checker.communitynews.deleted', ['count' => \count($ids)]));
            }

            return $this->redirectToRoute('admin_spam_community_news', array_filter(['page' => $request->query->getInt('page')]));
        }

        return $this->render('admin/checker/communitynews.html.twig', [
            'reported' => $communityNewsModel->getReportedComments($request->query->getInt('page', 1), 20),
            'open' => $communityNewsModel->countReportedComments(),
            'menu' => $this->getSubmenuItems(),
            'active' => 'community_news',
        ]);
    }

    #[Route(path: '/admin/spam', name: 'admin_spam')]
    public function redirectToSpamMessages(): RedirectResponse
    {
        return new RedirectResponse($this->generateUrl('admin_spam_messages'));
    }

    public function getActiveMenuAndMessages(int $type, int $page, int $limit): array
    {
        switch ($type) {
            case self::MESSAGES_REPORTED:
                $active = 'messages';
                $messages = $this->checkerModel->getReportedMessages($page, $limit);
                break;
            case self::MESSAGES_PROCESSED:
                $active = 'processed_messages';
                $messages = $this->checkerModel->getProcessedReportedMessages($page, $limit);
                break;
            case self::MESSAGES_BLOCK_WORDS:
                $active = 'blocked_words';
                $messages = $this->checkerModel->getBlockWordsMessages($page, $limit);
                break;
            case self::MESSAGES_BLOCK_WORDS_PROCESSED:
                $active = 'processed_blocked_words';
                $messages = $this->checkerModel->getProcessedBlockWordsMessages($page, $limit);
                break;
            default:
                throw new InvalidArgumentException();
        }

        return [$active, $messages];
    }

    /**
     * @param array<string, int|string> $parameters
     */
    private function translate(string $key, array $parameters = []): string
    {
        return $this->translator->trans($key, $parameters);
    }

    private function getSubmenuItems(): array
    {
        return [
            'messages' => [
                'key' => 'reported.messages',
                'icon' => 'envelope',
                'url' => $this->generateUrl('admin_spam_messages'),
            ],
            'processed_messages' => [
                'key' => 'reported.messages.processed',
                'icon' => 'envelope-circle-check',
                'url' => $this->generateUrl('admin_spam_messages_processed'),
            ],
            'blocked_words' => [
                'key' => 'block.words.messages',
                'icon' => 'ban',
                'url' => $this->generateUrl('admin_spam_messages_block_words'),
            ],
            'processed_blocked_words' => [
                'key' => 'block.words.processed',
                'icon' => 'circle-check',
                'url' => $this->generateUrl('admin_spam_messages_block_words_processed'),
            ],
            'activities' => [
                'key' => 'activities',
                'icon' => 'calendar-days',
                'url' => $this->generateUrl('admin_spam_activities'),
            ],
            'community_news' => [
                'key' => 'community_news',
                'icon' => 'newspaper',
                'url' => $this->generateUrl('admin_spam_community_news'),
            ],
        ];
    }

    private function handleMessages(Request $request, int $type): Response
    {
        if (
            !$this->isGranted(Member::ROLE_ADMIN_CHECKER)
            && !$this->isGranted(Member::ROLE_ADMIN_SAFETYTEAM)
        ) {
            throw $this->createAccessDeniedException('You need to have Checker right to access this.');
        }

        $page = $request->query->get('page', 1);

        /** @var Member $member */
        $member = $this->getUser();
        $limit = $this->getItemsPerPage($member);

        [$active, $messages] = $this->getActiveMenuAndMessages($type, $page, $limit);

        $messageIds = [];
        foreach ($messages->getIterator() as $key => $val) {
            $messageIds[$key] = $val->getId();
        }

        $form = $this->createForm(SpamMessagesIndexFormType::class, null, [
            'ids' => $messageIds,
        ]);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $spamMessageIds = $data['spamMessages'];
            $noSpamMessageIds = $data['noSpamMessages'];
            $ids = array_intersect($spamMessageIds, $noSpamMessageIds);
            if (!empty($ids)) {
                $form->addError(new FormError('Spam and no spam are mutually exclusive'));
            } else {
                $this->checkerModel->markAsSpamByChecker($spamMessageIds);
                $this->checkerModel->unmarkAsSpamByChecker($noSpamMessageIds);
                $this->addFlash('notice', 'Set spam status');

                if (self::MESSAGES_BLOCK_WORDS_PROCESSED === $type) {
                    return $this->redirectToRoute('admin_spam_messages_block_words');
                }

                return $this->redirectToRoute('admin_spam_messages');
            }
        }

        return $this->render('admin/checker/messages.html.twig', [
            'form' => $form->createView(),
            'reported' => $messages,
            'submenu' => [
                'active' => $active,
                'items' => $this->getSubmenuItems(),
            ],
        ]);
    }
}
