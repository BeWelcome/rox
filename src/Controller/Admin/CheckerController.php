<?php

namespace App\Controller\Admin;

use App\Entity\Member;
use App\Form\SpamActivitiesIndexFormType;
use App\Form\SpamCommunityNewsCommentsIndexFormType;
use App\Form\SpamMessagesIndexFormType;
use App\Model\ActivityModel;
use App\Model\CommunityNewsModel;
use App\Model\MessageModel;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class CheckerController extends AbstractController
{
    /**
     * @Route("/admin/spam/messages", name="admin_spam_messages")
     *
     * @throws AccessDeniedException
     *
     * @return Response
     */
    public function showOverview(Request $request, MessageModel $messageModel)
    {
        if (
            !$this->isGranted(Member::ROLE_ADMIN_CHECKER)
            && !$this->isGranted(Member::ROLE_ADMIN_SAFETYTEAM)
        ) {
            throw $this->createAccessDeniedException('You need to have Checker right to access this.');
        }

        $page = $request->query->get('page', 1);
        $limit = $request->query->get('limit', 10);

        $reportedMessages = $messageModel->getReportedMessages($page, $limit);
        $messageIds = [];
        foreach ($reportedMessages->getIterator() as $key => $val) {
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
                if (!empty($spamMessageIds)) {
                    $messageModel->markAsSpamByChecker($spamMessageIds);
                }
                if (!empty($noSpamMessageIds)) {
                    $messageModel->unmarkAsSpamByChecker($noSpamMessageIds);
                }
                $this->addFlash('notice', 'Set spam status');

                return $this->redirectToRoute('admin_spam_messages');
            }
        }

        return  $this->render('admin/checker/messages.html.twig', [
            'form' => $form->createView(),
            'reported' => $reportedMessages,
            'submenu' => [
                'active' => 'messages',
                'items' => $this->getSubmenuItems(),
            ],
        ]);
    }

    /**
     * @Route("/admin/spam/activities", name="admin_spam_activities")
     *
     * @throws AccessDeniedException
     *
     * @return Response
     */
    public function showActivities(Request $request, ActivityModel $activitiesModel)
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

        return  $this->render('admin/checker/activities.html.twig', [
            'form' => $form->createView(),
            'reported' => $latestActivities,
            'submenu' => [
                'active' => 'activities',
                'items' => $this->getSubmenuItems(),
            ],
        ]);
    }

    /**
     * @Route("/admin/spam/communitynews", name="admin_spam_community_news")
     *
     * @throws AccessDeniedException
     *
     * @return Response
     */
    public function showCommunityNewsComments(Request $request, CommunityNewsModel $communityNewsModel)
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

        return  $this->render('admin/checker/communitynews.html.twig', [
            'form' => $form->createView(),
            'reported' => $latestComments,
            'submenu' => [
                'active' => 'community_news',
                'items' => $this->getSubmenuItems(),
            ],
        ]);
    }

    /**
     * @Route("/admin/spam", name="admin_spam")
     */
    public function redirectToSpamMessages()
    {
        return new RedirectResponse($this->generateUrl('admin_spam_messages'));
    }

    private function getSubmenuItems()
    {
        return [
            'messages' => [
                'key' => 'reported.messages',
                'url' => $this->generateUrl('admin_spam_messages'),
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
}
