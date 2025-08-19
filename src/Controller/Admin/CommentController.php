<?php

namespace App\Controller\Admin;

use App\Doctrine\CommentAdminActionType;
use App\Doctrine\CommentQualityType;
use App\Entity\Comment;
use App\Entity\Member;
use App\Form\AdminCommentFormType;
use App\Model\Admin\CommentModel;
use App\Utilities\TranslatedFlashTrait;
use App\Utilities\TranslatorTrait;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * Class CommentController.
 *
 * @SuppressWarnings("PHPMD.TooManyPublicMethods")
 */
class CommentController extends AbstractController
{
    use TranslatedFlashTrait;
    use TranslatorTrait;

    /**
     * CommentController constructor.
     */
    public function __construct(
        private readonly CommentModel $commentModel,
        private readonly EntityManagerInterface $entityManager
    ) {
    }

    #[Route(path: '/admin/comment', name: 'admin_comment_overview')]
    public function adminCommentOverview(Request $request): Response
    {
        if (
            !$this->isGranted(Member::ROLE_ADMIN_COMMENTS)
            && !$this->isGranted(Member::ROLE_ADMIN_SAFETYTEAM)
        ) {
            throw $this->createAccessDeniedException('You need to have Comment right to access this.');
        }

        $page = $request->query->get('page', 1);
        $limit = $request->query->get('limit', 10);

        $comments = $this->commentModel->getComments($page, $limit);

        return $this->render('admin/comment/overview.html.twig', [
            'headline' => 'admin.comments.all',
            'route' => 'admin_comment_overview',
            'comments' => $comments,
            'submenu' => [
                'items' => $this->getSubMenuItems(),
                'active' => 'overview',
            ],
        ]);
    }

    #[Route(path: '/admin/comment/safetyteam', name: 'admin_abuser_overview')]
    public function adminSafetyTeamOverview(Request $request): Response
    {
        if (!$this->isGranted(Member::ROLE_ADMIN_SAFETYTEAM)) {
            throw $this->createAccessDeniedException('You need to have SafetyTeam right to access this.');
        }

        $page = $request->query->get('page', 1);
        $limit = $request->query->get('limit', 10);

        $comments = $this->commentModel->getCommentsByAdminAction(
            CommentAdminActionType::SAFETY_TEAM_CHECK,
            $page,
            $limit
        );

        return $this->render('admin/comment/overview.html.twig', [
            'headline' => 'admin.comment.safetyteam',
            'route' => 'admin_abuser_overview',
            'comments' => $comments,
            'submenu' => [
                'items' => $this->getSubMenuItems(),
                'active' => 'abusermustcheck',
            ],
        ]);
    }

    #[Route(path: '/admin/comment/reported', name: 'admin_comment_reported_overview')]
    public function adminReportedOverview(Request $request): Response
    {
        if (
            !$this->isGranted(Member::ROLE_ADMIN_COMMENTS)
            && !$this->isGranted(Member::ROLE_ADMIN_SAFETYTEAM)
        ) {
            throw $this->createAccessDeniedException('You need to have Group right to access this.');
        }

        $page = $request->query->get('page', 1);
        $limit = $request->query->get('limit', 10);

        $comments = $this->commentModel->getCommentsByAdminAction(CommentAdminActionType::ADMIN_CHECK, $page, $limit);

        return $this->render('admin/comment/overview.html.twig', [
            'headline' => 'admin.comment.reported',
            'route' => 'admin_reported_overview',
            'comments' => $comments,
            'submenu' => [
                'items' => $this->getSubMenuItems(),
                'active' => 'reportedcomment',
            ],
        ]);
    }

    #[Route(path: '/admin/comment/negative', name: 'admin_negative_overview')]
    public function adminNegativeOverview(Request $request): Response
    {
        if (
            !$this->isGranted(Member::ROLE_ADMIN_COMMENTS)
            && !$this->isGranted(Member::ROLE_ADMIN_SAFETYTEAM)
        ) {
            throw $this->createAccessDeniedException('You need to have Group right to access this.');
        }
        $page = $request->query->get('page', 1);
        $limit = $request->query->get('limit', 10);

        $comments = $this->commentModel->getCommentsByQuality(CommentQualityType::NEGATIVE, $page, $limit);

        return $this->render('admin/comment/overview.html.twig', [
            'headline' => 'admin.comment.negative',
            'route' => 'admin_negative_overview',
            'comments' => $comments,
            'submenu' => [
                'items' => $this->getSubMenuItems(),
                'active' => 'negativecomment',
            ],
        ]);
    }

    #[Route(path: '/admin/comment/checked', name: 'admin_checked_overview')]
    public function adminCheckedOverview(Request $request): Response
    {
        if (
            !$this->isGranted(Member::ROLE_ADMIN_COMMENTS)
            && !$this->isGranted(Member::ROLE_ADMIN_SAFETYTEAM)
        ) {
            throw $this->createAccessDeniedException('You need to have Group right to access this.');
        }

        $page = $request->query->get('page', 1);
        $limit = $request->query->get('limit', 10);

        $comments = $this->commentModel->getCommentsByAdminAction(CommentAdminActionType::ADMIN_CHECKED, $page, $limit);

        return $this->render('admin/comment/overview.html.twig', [
            'headline' => 'admin.comment.checked.headline',
            'route' => 'admin_checked_overview',
            'comments' => $comments,
            'submenu' => [
                'items' => $this->getSubMenuItems(),
                'active' => 'checkedcomment',
            ],
        ]);
    }

    #[Route(path: '/admin/comment/{to_member}/{from_member}', name: 'admin_comment')]
    public function adminComment(
        Request $request,
        #[MapEntity(mapping: ['to_member' => 'username'])] Member $toMember,
        #[MapEntity(mapping: ['from_member' => 'username'])] Member $fromMember
    ) : Response {
        if (
            !$this->isGranted(Member::ROLE_ADMIN_COMMENTS)
            && !$this->isGranted(Member::ROLE_ADMIN_SAFETYTEAM)
        ) {
            throw $this->createAccessDeniedException('error.access.comment');
        }

        $commentRepository = $this->entityManager->getRepository(Comment::class);
        $comment = $commentRepository->findOneBy([
            'toMember' => $toMember,
            'fromMember' => $fromMember,
        ]);
        $reply = $commentRepository->findOneBy([
            'toMember' => $fromMember,
            'fromMember' => $toMember,
        ]);

        $form = $this->createForm(AdminCommentFormType::class, $comment);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $clickedButton = $form->getClickedButton()->getName();
            if ('deleteComment' === $clickedButton) {
                $this->addTranslatedFlash('notice', 'flash.admin.comment.deleted');
                $this->entityManager->remove($comment);
                $this->entityManager->flush();

                return $this->redirectToRoute('admin_comment_overview');
            }
            $this->handleClickedButton($clickedButton, $comment);
            $this->entityManager->persist($comment);
            $this->entityManager->flush();

            // Redirect to self to ensure buttons are correctly labeled after update
            return $this->redirectToRoute('admin_comment', [
                'to_member' => $toMember->getUsername(),
                'from_member' => $fromMember->getUsername(),
            ]);
        }

        return $this->render('admin/comment/comment.html.twig', [
            'form' => $form->createView(),
            'comment' => $comment,
            'reply' => $reply,
        ]);
    }

    #[Route(path: '/admin/comment/{to_member}/{from_member}/safetyteam', name: 'admin_comment_assign_safetyteam')]
    public function adminCommentAssignSafetyTeamAction(
        Request $request,
        #[MapEntity(mapping: ['to_member' => 'username'])] Member $toMember,
        #[MapEntity(mapping: ['from_member' => 'username'])] Member $fromMember
    ): Response {
        if (!$this->isGranted(Member::ROLE_ADMIN_SAFETYTEAM)) {
            throw $this->createAccessDeniedException('You need to have either Comments right or be a member of the Safety Team to access this.');
        }

        $commentRepository = $this->entityManager->getRepository(Comment::class);
        $comment = $commentRepository->findOneBy([
            'toMember' => $toMember,
            'fromMember' => $fromMember,
        ]);
        $comment->setAdminAction(CommentAdminActionType::SAFETY_TEAM_CHECK);
        $this->entityManager->persist($comment);
        $this->entityManager->flush();

        $this->addTranslatedFlash('notice', 'flash.admin.comment.safetyteam');

        return $this->redirect($request->headers->get('referer'));
    }

    #[Route(path: '/admin/comment/{to_member}/{from_member}/checked', name: 'admin_comment_mark_checked')]
    public function adminCommentMarkChecked(
        Request $request,
        #[MapEntity(mapping: ['to_member' => 'username'])] Member $toMember,
        #[MapEntity(mapping: ['from_member' => 'username'])] Member $fromMember
    ): Response {
        if (
            !$this->isGranted(Member::ROLE_ADMIN_COMMENTS)
            && !$this->isGranted(Member::ROLE_ADMIN_SAFETYTEAM)
        ) {
            throw $this->createAccessDeniedException('You need to have either Comments right or be a member of the Safety Team to access this.');
        }

        $commentRepository = $this->entityManager->getRepository(Comment::class);
        $comment = $commentRepository->findOneBy([
            'toMember' => $toMember,
            'fromMember' => $fromMember,
        ]);
        $comment->setAdminAction(CommentAdminActionType::ADMIN_CHECKED);
        $this->entityManager->persist($comment);
        $this->entityManager->flush();

        $this->addTranslatedFlash('notice', 'flash.admin.comment.checked');

        return $this->redirect($request->headers->get('referer'));
    }

    #[Route(path: '/admin/comment/{to_member}/{from_member}/hide', name: 'admin_comment_hide')]
    public function adminCommentHide(
        Request $request,
        #[MapEntity(mapping: ['to_member' => 'username'])] Member $toMember,
        #[MapEntity(mapping: ['from_member' => 'username'])] Member $fromMember
    ): Response {
        if (
            !$this->isGranted(Member::ROLE_ADMIN_COMMENTS)
            && !$this->isGranted(Member::ROLE_ADMIN_SAFETYTEAM)
        ) {
            throw $this->createAccessDeniedException('You need to have either Comments right or be a member of the Safety Team to access this.');
        }

        $commentRepository = $this->entityManager->getRepository(Comment::class);
        $comment = $commentRepository->findOneBy([
            'toMember' => $toMember,
            'fromMember' => $fromMember,
        ]);
        $comment->setDisplayInPublic(false);
        $this->entityManager->persist($comment);
        $this->entityManager->flush();

        $this->addTranslatedFlash('notice', 'flash.admin.comment.hidden');

        return $this->redirect($request->headers->get('referer'));
    }

    #[Route(path: '/admin/comment/{to_member}/{from_member}/show', name: 'admin_comment_show')]
    public function adminCommentShow(
        Request $request,
        #[MapEntity(mapping: ['to_member' => 'username'])] Member $toMember,
        #[MapEntity(mapping: ['from_member' => 'username'])] Member $fromMember
    ): Response {
        if (
            !$this->isGranted(Member::ROLE_ADMIN_COMMENTS)
            && !$this->isGranted(Member::ROLE_ADMIN_SAFETYTEAM)
        ) {
            throw $this->createAccessDeniedException('You need to have either Comments right or be a member of the Safety Team to access this.');
        }

        $commentRepository = $this->entityManager->getRepository(Comment::class);
        $comment = $commentRepository->findOneBy([
            'toMember' => $toMember,
            'fromMember' => $fromMember,
        ]);
        $comment->setDisplayInPublic(true);
        $this->entityManager->persist($comment);
        $this->entityManager->flush();

        $this->addTranslatedFlash('notice', 'flash.admin.comment.visible');

        return $this->redirect($request->headers->get('referer'));
    }

    /**
     *
     * @throws AccessDeniedException
     * @return Response
     */
    #[Route(path: '/admin/comment/for/{username}', name: 'admin_comments_for_member', priority: 10)]
    public function showAllCommentsForMember(Request $request, Member $member)
    {
        if (
            !$this->isGranted(Member::ROLE_ADMIN_COMMENTS)
            && !$this->isGranted(Member::ROLE_ADMIN_SAFETYTEAM)
        ) {
            throw $this->createAccessDeniedException('You need to have Group right to access this.');
        }

        $page = $request->query->get('page', 1);
        $limit = $request->query->get('limit', 10);

        $comments = $this->commentModel->getCommentsForMember($member, $page, $limit);

        return $this->render('admin/comment/overview.html.twig', [
            'headline' => 'admin.comment.all',
            'route' => 'admin_comments_for_member',
            'comments' => $comments,
            'submenu' => [
                'items' => $this->getSubMenuItems(),
                'active' => 'overview',
            ],
        ]);
    }

    /**
     *
     * @throws AccessDeniedException
     * @return Response
     */
    #[Route(path: '/admin/comment/from/{username}', name: 'admin_comments_from_member', priority: 10)]
    public function showAllCommentsFromMember(Request $request, Member $member)
    {
        if (
            !$this->isGranted(Member::ROLE_ADMIN_COMMENTS)
            && !$this->isGranted(Member::ROLE_ADMIN_SAFETYTEAM)
        ) {
            throw $this->createAccessDeniedException('You need to have Group right to access this.');
        }

        $page = $request->query->get('page', 1);
        $limit = $request->query->get('limit', 10);

        $comments = $this->commentModel->getCommentsFromMember($member, $page, $limit);

        return $this->render('admin/comment/overview.html.twig', [
            'headline' => 'admin.comment.all',
            'route' => 'admin_comments_from_member',
            'comments' => $comments,
            'submenu' => [
                'items' => $this->getSubMenuItems(),
                'active' => 'overview',
            ],
        ]);
    }

    private function getSubMenuItems(): array
    {
        $subMenuSafetyTeam = [];
        if ($this->isGranted(Member::ROLE_ADMIN_SAFETYTEAM)) {
            $subMenuSafetyTeam = $this->addSafetyTeamItems();
        }

        $subMenuComments = [];
        if ($this->isGranted(Member::ROLE_ADMIN_COMMENTS)) {
            $subMenuComments = $this->addCommentRoleItems();
        }

        return array_merge($subMenuComments, $subMenuSafetyTeam);
    }

    private function addCommentRoleItems(): array
    {
        return [
            'reportedcomment' => [
                'key' => 'AdminReportedComment',
                'url' => $this->generateUrl('admin_comment_reported_overview'),
            ],
            'checkedcomment' => [
                'key' => 'AdminCheckedComment',
                'url' => $this->generateUrl('admin_checked_overview'),
            ],
            'overview' => [
                'key' => 'AdminComment',
                'url' => $this->generateUrl('admin_comment_overview'),
            ],
        ];
    }

    private function handleClickedButton($clickedButton, Comment &$comment)
    {
        switch ($clickedButton) {
            case 'hideComment':
                $comment->setDisplayinpublic(false);
                $this->addTranslatedFlash('notice', 'flash.admin.comment.hidden');
                break;
            case 'showComment':
                $comment->setDisplayinpublic(true);
                $this->addTranslatedFlash('notice', 'flash.admin.comment.visible');
                break;
            case 'allowEditing':
                $comment->setEditingAllowed(true);
                $this->addTranslatedFlash('notice', 'flash.admin.comment.editable');
                break;
            case 'disableEditing':
                $comment->setEditingAllowed(false);
                $this->addTranslatedFlash('notice', 'flash.admin.comment.locked');
                break;
        }
    }

    private function addSafetyTeamItems(): array
    {
        return [
            'abusermustcheck' => [
                'key' => 'AdminAbuserMustCheck',
                'url' => $this->generateUrl('admin_abuser_overview'),
            ],
            'reportedcomment' => [
                'key' => 'AdminReportedComment',
                'url' => $this->generateUrl('admin_comment_reported_overview'),
            ],
            'negativecomment' => [
                'key' => 'AdminNegativeComment',
                'url' => $this->generateUrl('admin_negative_overview'),
            ],
            'checkedcomment' => [
                'key' => 'AdminCheckedComment',
                'url' => $this->generateUrl('admin_checked_overview'),
            ],
            'overview' => [
                'key' => 'AdminComment',
                'url' => $this->generateUrl('admin_comment_overview'),
            ]
        ];
    }
}
