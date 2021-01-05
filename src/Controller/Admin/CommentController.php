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
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * Class CommentController.
 *
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 */
class CommentController extends AbstractController
{
    use TranslatedFlashTrait;
    use TranslatorTrait;

    /** @var CommentModel */
    private $commentModel;

    /**
     * CommentController constructor.
     */
    public function __construct(CommentModel $commentModel)
    {
        $this->commentModel = $commentModel;
    }

    /**
     * @Route("/admin/comment", name="admin_comment_overview")
     *
     * @throws AccessDeniedException
     *
     * @return Response
     */
    public function adminCommentOverview(Request $request)
    {
        if (
            !$this->isGranted(Member::ROLE_ADMIN_COMMENTS)
            || !$this->isGranted(Member::ROLE_ADMIN_SAFETYTEAM)
        ) {
            throw $this->createAccessDeniedException('You need to have Group right to access this.');
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

    /**
     * @Route("/admin/comment/safetyteam", name="admin_abuser_overview")
     *
     * @throws AccessDeniedException
     *
     * @return Response
     */
    public function adminSafetyTeamOverview(Request $request)
    {
        if (!$this->isGranted(Member::ROLE_ADMIN_SAFETYTEAM)) {
            throw $this->createAccessDeniedException('You need to have Group right to access this.');
        }

        $page = $request->query->get('page', 1);
        $limit = $request->query->get('limit', 10);

        $comments = $this->commentModel->getCommentsByAdminAction(CommentAdminActionType::SAFETY_TEAM_CHECK, $page, $limit);

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

    /**
     * @Route("/admin/comment/reported", name="admin_comment_reported_overview")
     *
     * @throws AccessDeniedException
     *
     * @return Response
     */
    public function adminReportedOverview(Request $request)
    {
        if (
            !$this->isGranted(Member::ROLE_ADMIN_COMMENTS)
            || !$this->isGranted(Member::ROLE_ADMIN_SAFETYTEAM)
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

    /**
     * @Route("/admin/comment/negative", name="admin_negative_overview")
     *
     * @throws AccessDeniedException
     *
     * @return Response
     */
    public function adminNegativeOverview(Request $request)
    {
        if (
            !$this->isGranted(Member::ROLE_ADMIN_COMMENTS)
            || !$this->isGranted(Member::ROLE_ADMIN_SAFETYTEAM)
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

    /**
     * @Route("/admin/comment/checked", name="admin_checked_overview")
     *
     * @throws AccessDeniedException
     *
     * @return Response
     */
    public function adminCheckedOverview(Request $request)
    {
        if (
            !$this->isGranted(Member::ROLE_ADMIN_COMMENTS)
            || !$this->isGranted(Member::ROLE_ADMIN_SAFETYTEAM)
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

    /**
     * @Route("/admin/comment/{commentId}", name="admin_comment")
     *
     * @throws AccessDeniedException
     *
     * @return Response
     * @ParamConverter("comment", class="App\Entity\Comment", options={"mapping": {"commentId": "id"}})
     */
    public function adminComment(Request $request, Comment $comment)
    {
        if (
            !$this->isGranted(Member::ROLE_ADMIN_COMMENTS)
            || !$this->isGranted(Member::ROLE_ADMIN_SAFETYTEAM)
        ) {
            throw $this->createAccessDeniedException('error.access.comment');
        }

        $commentRepository = $this->getDoctrine()->getRepository(Comment::class);
        $reply = $commentRepository->findOneBy([
            'toMember' => $comment->getFromMember(),
            'fromMember' => $comment->getToMember(),
        ]);

        $form = $this->createForm(AdminCommentFormType::class, $comment);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $clickedButton = $form->getClickedButton()->getName();
            if ('deleteComment' === $clickedButton) {
                $this->addTranslatedFlash('notice', 'flash.admin.comment.deleted');
                $em->remove($comment);
                $em->flush();

                return $this->redirectToRoute('admin_comment_overview');
            }
            $this->handleClickedButton($clickedButton, $comment);
            $em->persist($comment);
            $em->flush();

            // Redirect to self to ensure buttons are correctly labeled after update
            return $this->redirectToRoute('admin_comment', [
                'commentId' => $comment->getId(),
            ]);
        }

        return $this->render('admin/comment/comment.html.twig', [
            'form' => $form->createView(),
            'comment' => $comment,
            'reply' => $reply,
        ]);
    }

    /**
     * @Route("/admin/comment/{commentId}/safetyteam", name="admin_comment_assign_safetyteam")
     *
     * @throws AccessDeniedException
     *
     * @return RedirectResponse
     *
     * @ParamConverter("comment", class="App\Entity\Comment", options={"mapping": {"commentId": "id"}})
     */
    public function adminCommentAssignSafetyTeamAction(Request $request, Comment $comment)
    {
        if (
            !$this->isGranted(Member::ROLE_ADMIN_COMMENTS)
            || !$this->isGranted(Member::ROLE_ADMIN_SAFETYTEAM)
        ) {
            throw $this->createAccessDeniedException('You need to have either Comments right or be a member of the Safety Team to access this.');
        }

        $em = $this->getDoctrine()->getManager();
        $comment->setAdminAction(CommentAdminActionType::SAFETY_TEAM_CHECK);
        $em->persist($comment);
        $em->flush();

        $this->addTranslatedFlash('notice', 'flash.admin.comment.safetyteam');

        return $this->redirect($request->headers->get('referer'));
    }

    /**
     * @Route("/admin/comment/{commentId}/checked", name="admin_comment_mark_checked")
     *
     * @throws AccessDeniedException
     *
     * @return RedirectResponse
     *
     * @ParamConverter("comment", class="App\Entity\Comment", options={"mapping": {"commentId": "id"}})
     */
    public function adminCommentMarkChecked(Request $request, Comment $comment)
    {
        if (
            !$this->isGranted(Member::ROLE_ADMIN_COMMENTS)
            || !$this->isGranted(Member::ROLE_ADMIN_SAFETYTEAM)
        ) {
            throw $this->createAccessDeniedException('You need to have either Comments right or be a member of the Safety Team to access this.');
        }

        $em = $this->getDoctrine()->getManager();
        $comment->setAdminAction(CommentAdminActionType::ADMIN_CHECKED);
        $em->persist($comment);
        $em->flush();

        $this->addTranslatedFlash('notice', 'flash.admin.comment.checked');

        return $this->redirect($request->headers->get('referer'));
    }

    /**
     * @Route("/admin/comment/{commentId}/hide", name="admin_comment_hide")
     *
     * @throws AccessDeniedException
     *
     * @return RedirectResponse
     *
     * @ParamConverter("comment", class="App\Entity\Comment", options={"mapping": {"commentId": "id"}})
     */
    public function adminCommentHide(Request $request, Comment $comment)
    {
        if (
            !$this->isGranted(Member::ROLE_ADMIN_COMMENTS)
            || !$this->isGranted(Member::ROLE_ADMIN_SAFETYTEAM)
        ) {
            throw $this->createAccessDeniedException('You need to have either Comments right or be a member of the Safety Team to access this.');
        }

        $em = $this->getDoctrine()->getManager();
        $comment->setDisplayinpublic(false);
        $em->persist($comment);
        $em->flush();

        $this->addTranslatedFlash('notice', 'flash.admin.comment.hidden');

        return $this->redirect($request->headers->get('referer'));
    }

    /**
     * @Route("/admin/comment/{commentId}/show", name="admin_comment_show")
     *
     * @throws AccessDeniedException
     *
     * @return RedirectResponse
     *
     * @ParamConverter("comment", class="App\Entity\Comment", options={"mapping": {"commentId": "id"}})
     */
    public function adminCommentShow(Request $request, Comment $comment)
    {
        if (
            !$this->isGranted(Member::ROLE_ADMIN_COMMENTS)
            || !$this->isGranted(Member::ROLE_ADMIN_SAFETYTEAM)
        ) {
            throw $this->createAccessDeniedException('You need to have either Comments right or be a member of the Safety Team to access this.');
        }

        $em = $this->getDoctrine()->getManager();
        $comment->setDisplayinpublic(true);
        $em->persist($comment);
        $em->flush();

        $this->addTranslatedFlash('notice', 'flash.admin.comment.visible');

        return $this->redirect($request->headers->get('referer'));
    }

    /**
     * @Route("/admin/comment/for/{username}", name="admin_comments_for_member")
     *
     * @throws AccessDeniedException
     *
     * @return Response
     */
    public function showAllCommentsForMember(Request $request, Member $member)
    {
        if (
            !$this->isGranted(Member::ROLE_ADMIN_COMMENTS)
            || !$this->isGranted(Member::ROLE_ADMIN_SAFETYTEAM)
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
     * @Route("/admin/comment/from/{username}", name="admin_comments_from_member")
     *
     * @throws AccessDeniedException
     *
     * @return Response
     */
    public function showAllCommentsFromMember(Request $request, Member $member)
    {
        if (
            !$this->isGranted(Member::ROLE_ADMIN_COMMENTS)
            || !$this->isGranted(Member::ROLE_ADMIN_SAFETYTEAM)
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

    /**
     * @return array
     */
    private function getSubMenuItems()
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
            ],
        ];
    }

    private function handleClickedButton($clickedButton, &$comment)
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
                $comment->setAllowedit(true);
                $this->addTranslatedFlash('notice', 'flash.admin.comment.editable');
                break;
            case 'disableEditing':
                $comment->setAllowedit(false);
                $this->addTranslatedFlash('notice', 'flash.admin.comment.locked');
                break;
        }
    }
}
