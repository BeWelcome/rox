<?php

namespace App\Controller\Admin;

use App\Doctrine\CommentAdminActionType;
use App\Doctrine\CommentQualityType;
use App\Entity\Comment;
use App\Entity\Member;
use App\Form\AdminCommentFormType;
use App\Model\Admin\CommentModel;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class CommentController.
 *
 *
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 */
class CommentController extends AbstractController
{
    /**
     * @Route("/admin/comment", name="admin_comment_overview")
     *
     * @param Request $request
     *
     * @return Response
     */
    public function adminCommentOverview(Request $request)
    {
        $page = $request->query->get('page', 1);
        $limit = $request->query->get('limit', 10);

        $commentModel = new CommentModel($this->getDoctrine());
        $comments = $commentModel->getComments($page, $limit);

        return $this->render('admin/comment/overview.html.twig', [
            'headline' => 'admin.comment.all',
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
     * @param Request $request
     *
     * @return Response
     */
    public function adminSafetyTeamOverview(Request $request)
    {
        $page = $request->query->get('page', 1);
        $limit = $request->query->get('limit', 10);

        $commentModel = new CommentModel($this->getDoctrine());
        $comments = $commentModel->getCommentsByAdminAction(CommentAdminActionType::SAFETY_TEAM_CHECK, $page, $limit);

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
     * @param Request $request
     *
     * @return Response
     */
    public function adminReportedOverview(Request $request)
    {
        $page = $request->query->get('page', 1);
        $limit = $request->query->get('limit', 10);

        $commentModel = new CommentModel($this->getDoctrine());
        $comments = $commentModel->getCommentsByAdminAction(CommentAdminActionType::ADMIN_CHECK, $page, $limit);

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
     * @param Request $request
     *
     * @return Response
     */
    public function adminNegativeOverview(Request $request)
    {
        $page = $request->query->get('page', 1);
        $limit = $request->query->get('limit', 10);

        $commentModel = new CommentModel($this->getDoctrine());
        $comments = $commentModel->getCommentsByQuality(CommentQualityType::NEGATIVE, $page, $limit);

        return $this->render('admin/comment/overview.html.twig', [
            'headline' => 'admin.comment.negative',
            'route' => 'admin_negative_overview',
            'comments' => $comments,
            'submenu' => [
                'items' => $this->getSubMenuItems(),
                'active' => 'reportedcomment',
            ],
        ]);
    }

    /**
     * @Route("/admin/comment/{commentId}", name="admin_comment")
     *
     * @param Request $request
     * @param Comment $comment
     *
     * @return Response
     * @ParamConverter("comment", class="App\Entity\Comment", options={"mapping": {"commentId": "id"}})
     */
    public function adminCommentAction(Request $request, Comment $comment)
    {
        if (!$this->isGranted([Member::ROLE_ADMIN_COMMENTS, Member::ROLE_ADMIN_SAFETYTEAM])) {
            throw $this->createAccessDeniedException('You need to have either Comments right or be a member of the Safety Team to access this.');
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
            if ('hideComment' === $clickedButton) {
                $comment->setDisplayinpublic(false);
                $this->addFlash('notice', 'Comment is now hidden.');
            }
            if ('showComment' === $clickedButton) {
                $comment->setDisplayinpublic(true);
                $this->addFlash('notice', 'Comment is now visible again.');
            }
            if ('allowEditing' === $clickedButton) {
                $comment->setAllowedit(true);
                $this->addFlash('notice', 'Comment can be edited by author now.');
            }
            if ('disableEditing' === $clickedButton) {
                $comment->setAllowedit(false);
                $this->addFlash('notice', 'Comment is now locked.');
            }
            if ('delectComment' === $clickedButton) {
                $this->addFlash('notice', 'Comment was deleted.');
                $em->remove($comment);
                $em->flush();

                return $this->redirectToRoute('admin_comment_overview');
            }
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
     * @param Request $request
     * @param Comment $comment
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     *
     * @ParamConverter("comment", class="App\Entity\Comment", options={"mapping": {"commentId": "id"}})
     */
    public function adminCommentAssignSafetyTeamAction(Request $request, Comment $comment)
    {
        if (!$this->isGranted([Member::ROLE_ADMIN_COMMENTS, Member::ROLE_ADMIN_SAFETYTEAM])) {
            throw $this->createAccessDeniedException('You need to have either Comments right or be a member of the Safety Team to access this.');
        }

        $em = $this->getDoctrine()->getManager();
        $comment->setAdminAction(CommentAdminActionType::SAFETY_TEAM_CHECK);
        $em->persist($comment);
        $em->flush();

        $this->addFlash('notice', 'Comment has been assigned to safety team');

        return $this->redirect($request->headers->get('referer'));
    }

    /**
     * @Route("/admin/comment/{commentId}/checked", name="admin_comment_mark_checked")
     *
     * @param Request $request
     * @param Comment $comment
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     *
     * @ParamConverter("comment", class="App\Entity\Comment", options={"mapping": {"commentId": "id"}})
     */
    public function adminCommentMarkChecked(Request $request, Comment $comment)
    {
        if (!$this->isGranted([Member::ROLE_ADMIN_COMMENTS, Member::ROLE_ADMIN_SAFETYTEAM])) {
            throw $this->createAccessDeniedException('You need to have either Comments right or be a member of the Safety Team to access this.');
        }

        $em = $this->getDoctrine()->getManager();
        $comment->setAdminAction(CommentAdminActionType::ADMIN_CHECKED);
        $em->persist($comment);
        $em->flush();

        $this->addFlash('notice', 'Comment has been marked as checked');

        return $this->redirect($request->headers->get('referer'));
    }

    /**
     * @Route("/admin/comment/{commentId}/hide", name="admin_comment_hide")
     *
     * @param Request $request
     * @param Comment $comment
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     *
     * @ParamConverter("comment", class="App\Entity\Comment", options={"mapping": {"commentId": "id"}})
     */
    public function adminCommentHide(Request $request, Comment $comment)
    {
        if (!$this->isGranted([Member::ROLE_ADMIN_COMMENTS, Member::ROLE_ADMIN_SAFETYTEAM])) {
            throw $this->createAccessDeniedException('You need to have either Comments right or be a member of the Safety Team to access this.');
        }

        $em = $this->getDoctrine()->getManager();
        $comment->setDisplayinpublic(false);
        $em->persist($comment);
        $em->flush();

        $this->addFlash('notice', 'Comment has been hidden.');

        return $this->redirect($request->headers->get('referer'));
    }

    /**
     * @Route("/admin/comment/{commentId}/show", name="admin_comment_show")
     *
     * @param Request $request
     * @param Comment $comment
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     *
     * @ParamConverter("comment", class="App\Entity\Comment", options={"mapping": {"commentId": "id"}})
     */
    public function adminCommentShow(Request $request, Comment $comment)
    {
        if (!$this->isGranted([Member::ROLE_ADMIN_COMMENTS, Member::ROLE_ADMIN_SAFETYTEAM])) {
            throw $this->createAccessDeniedException('You need to have either Comments right or be a member of the Safety Team to access this.');
        }

        $em = $this->getDoctrine()->getManager();
        $comment->setDisplayinpublic(true);
        $em->persist($comment);
        $em->flush();

        $this->addFlash('notice', 'Comment is now visible.');

        return $this->redirect($request->headers->get('referer'));
    }

    /**
     * @Route("/admin/comment/for/{username}", name="admin_comments_for_member")
     *
     * @param Request $request
     * @param Member  $member
     *
     * @return Response
     */
    public function showAllCommentsForMember(Request $request, Member $member)
    {
        $page = $request->query->get('page', 1);
        $limit = $request->query->get('limit', 10);

        $commentModel = new CommentModel($this->getDoctrine());
        $comments = $commentModel->getCommentsForMember($member, $page, $limit);

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
     * @param Request $request
     * @param Member  $member
     *
     * @return Response
     */
    public function showAllCommentsFromMember(Request $request, Member $member)
    {
        $page = $request->query->get('page', 1);
        $limit = $request->query->get('limit', 10);

        $commentModel = new CommentModel($this->getDoctrine());
        $comments = $commentModel->getCommentsFromMember($member, $page, $limit);

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
            'overview' => [
                'key' => 'AdminComment',
                'url' => $this->generateUrl('admin_comment_overview'),
            ],
        ];
    }
}
