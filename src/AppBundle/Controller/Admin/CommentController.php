<?php

namespace AppBundle\Controller\Admin;

use AppBundle\Entity\Comment;
use AppBundle\Entity\Member;
use AppBundle\Form\AdminCommentFormType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class CommentController extends Controller
{
    /**
     * @Route("/admin/comment/{commentId}", name="admin_comment")
     * @Route("/admin/comment", name="admin_comment_overview")
     *
     * @param Request $request
     * @param Comment $comment
     *
     * @return Response
     * @ParamConverter("comment", class="AppBundle\Entity\Comment", options={"mapping": {"commentId": "id"}})
     */
    public function showOverview(Request $request, Comment $comment)
    {
        if (!$this->isGranted([Member::ROLE_ADMIN_COMMENTS, Member::ROLE_ADMIN_SAFETYTEAM])) {
            $this->createAccessDeniedException('You need to have either Comments right or be a member of the Safety Team to access this.');
        }

        $commentRepository = $this->getDoctrine()->getRepository(Comment::class);
        $reply = $commentRepository->findOneBy([
            'toMember' => $comment->getFromMember(),
            'fromMember' => $comment->getToMember(),
        ]);

        $form = $this->createForm(AdminCommentFormType::class, $comment);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
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
            $em = $this->getDoctrine()->getManager();
            $em->persist($comment);
            $em->flush();
        }

        return $this->render(':admin:comment/comment.html.twig', [
            'form' => $form->createView(),
            'comment' => $comment,
            'reply' => $reply,
            'submenu' => [
                'active' => 'overview',
                'items' => $this->getSubMenuItems(),
            ],
        ]);
    }

    private function getSubMenuItems()
    {
        return [
            'overview' => [
                'key' => 'AdminComment',
                'url' => $this->generateUrl('admin_comment_overview'),
            ],
        ];
    }
}
