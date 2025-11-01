<?php

namespace App\Controller\Admin;

use App\Entity\NewMember as Member;
use App\Form\SpamActivitiesIndexFormType;
use App\Form\SpamCommunityNewsCommentsIndexFormType;
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

class CheckerController extends AbstractController
{
    use ItemsPerPageTraits;

    private const int MESSAGES_REPORTED = 1;
    private const int MESSAGES_PROCESSED = 2;
    private const int MESSAGES_BLOCK_WORDS = 3;
    private const int MESSAGES_BLOCK_WORDS_PROCESSED = 4;

    public function __construct(
        private readonly CheckerModel $checkerModel,
        private readonly EntityManagerInterface $entityManager,
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

    #[Route(path: '/admin/spam/communitynews', name: 'admin_spam_community_news')]
    public function showCommunityNewsComments(Request $request, CommunityNewsModel $communityNewsModel): Response
    {
        if (
            !$this->isGranted(Member::ROLE_ADMIN_CHECKER)
            && !$this->isGranted(Member::ROLE_ADMIN_SAFETYTEAM)
        ) {
            throw $this->createAccessDeniedException('You need to have Checker right to access this.');
        }

        $page = $request->query->get('page', 1);
        $limit = $request->query->get('limit', 10);

        $latestComments = $communityNewsModel->getLatestCommunityNewsComments($page, $limit);
        $commentIds = [];
        foreach ($latestComments->getIterator() as $key => $val) {
            $commentIds[$key] = $val->getId();
        }

        $form = $this->createForm(SpamCommunityNewsCommentsIndexFormType::class, null, [
            'ids' => $commentIds,
        ]);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $communityNewsModel->deleteAsSpamByChecker($data['spamComments']);
            $this->addFlash('notice', 'deleted spam community news comment');

            return $this->redirectToRoute('admin_spam_community_news');
        }

        return $this->render('admin/checker/communitynews.html.twig', [
            'form' => $form->createView(),
            'reported' => $latestComments,
            'submenu' => [
                'active' => 'community_news',
                'items' => $this->getSubmenuItems(),
            ],
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

    private function getSubmenuItems(): array
    {
        return [
            'messages' => [
                'key' => 'reported.messages',
                'url' => $this->generateUrl('admin_spam_messages'),
            ],
            'processed_messages' => [
                'key' => 'reported.messages.processed',
                'url' => $this->generateUrl('admin_spam_messages_processed'),
            ],
            'blocked_words' => [
                'key' => 'block.words.messages',
                'url' => $this->generateUrl('admin_spam_messages_block_words'),
            ],
            'processed_blocked_words' => [
                'key' => 'block.words.processed',
                'url' => $this->generateUrl('admin_spam_messages_block_words_processed'),
            ],
            'activities' => [
                'key' => 'activities',
                'url' => $this->generateUrl('admin_spam_activities'),
            ],
            'community_news' => [
                'key' => 'community_news',
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
