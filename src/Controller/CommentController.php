<?php

namespace App\Controller;

use App\Doctrine\CommentAdminActionType;
use App\Entity\Comment;
use App\Entity\Member;
use App\Form\AddCommentType;
use App\Form\CustomDataClass\ReportCommentRequest;
use App\Form\ReportCommentType;
use App\Model\ProfileModel;
use App\Service\Mailer;
use App\Utilities\ProfileSubmenu;
use App\Utilities\TranslatedFlashTrait;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

class CommentController extends AbstractController
{
    use TranslatedFlashTrait;

    /**
     * @Route("/members/{username}/comment/{commentId}/report", name="report_comment",
     *     requirements={"username" = "(?i:[a-z](?!.*[-_.][-_.])[a-z0-9-._]{2,18}[a-z0-9])"}))
     *
     * @ParamConverter("member", class="App\Entity\Member", options={"mapping": {"username": "username"}})
     * @ParamConverter("comment", class="App\Entity\Comment", options={"mapping": {"commentId": "id"}})
     *
     * @return Response
     */
    public function reportCommentAction(
        Request $request,
        Member $member,
        Comment $comment,
        TranslatorInterface $translator,
        Mailer $mailer
    ) {
//        \todo Should we only allow the receiver of a comment to report it?
//        if ($comment->getToMember()->getId() !== $member->getId() && $comment->getFromMember()->getId() !== $member->getId()) {
//            throw new AccessDeniedException('Hau ab!');
//        }

        /** @var Member $member */
        $user = $this->getUser();

        $form = $this->createForm(ReportCommentType::class, new ReportCommentRequest());
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $feedback = trim(str_replace("\xc2\xa0", ' ', strip_tags(html_entity_decode($data->feedback, \ENT_HTML5, 'UTF-8'))));
            if (empty($feedback)) {
                $form->addError(new FormError('Feedback can not be empty.'));
            } else {
                $success = $mailer->sendCommentReportedFeedbackEmail(
                    $user,
                    [
                        'subject' => 'Comment report',
                        'comment' => $comment,
                        'feedback' => $feedback,
                    ]
                );

                if ($success) {
                    $em = $this->getDoctrine()->getManager();
                    $comment->setAdminAction(CommentAdminActionType::ADMIN_CHECK);
                    $em->persist($comment);
                    $em->flush();

                    $this->addFlash('notice', $translator->trans('flash.feedback.safetyteam'));

                    return $this->redirectToRoute('profile_all_comments', ['username' => $member->getUsername()]);
                }

                $this->addTranslatedFlash('error', 'flash.feedback.not.sent');
            }
        }

        return $this->render('member/report.comment.html.twig', [
            'form' => $form->createView(),
            'comment' => $comment,
            'member' => $member,
        ]);
    }

    /**
     * @Route("/members/{username}/comment/add", name="add_comment",
     *     requirements={"username" = "(?i:[a-z](?!.*[-_.][-_.])[a-z0-9-._]{2,18}[a-z0-9])"}))
     */
    public function addComment(Request $request, Member $member): Response
    {
        $form = $this->createForm(AddCommentType::class);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->addTranslatedFlash(
                'notice',
                'flash.comment.added',
                [
                    'username' => $member->getUsername(),
                ]
            );

            return $this->redirectToRoute('profile_all_comments', ['username' => $member->getUsername()]);
        }

        return $this->render('/comments/add.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/members/{username}/comment/edit", name="edit_comment",
     *     requirements={"username" = "(?i:[a-z](?!.*[-_.][-_.])[a-z0-9-._]{2,18}[a-z0-9])"}))
     */
    public function editComment(Request $request, Member $member): Response
    {
        $form = $this->createForm(AddCommentType::class);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->addTranslatedFlash(
                'notice',
                'flash.comment.added',
                [
                    'username' => $member->getUsername(),
                ]
            );

            return $this->redirectToRoute('profile_all_comments', ['username' => $member->getUsername()]);
        }

        return $this->render('/comments/add.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/members/{username}/comments/new", name="profile_comments",
     *     requirements={"username" = "(?i:[a-z](?!.*[-_.][-_.])[a-z0-9-._]{2,18}[a-z0-9])"}))
     */
    public function showCommentsForMember(
        Member $member,
        ProfileSubmenu $profileSubmenu,
        ProfileModel $profileModel,
        EntityManagerInterface $entityManager
    ): Response {
        /** @var Member $loggedInMember */
        $loggedInMember = $this->getUser();
        $statusForm = $profileModel->getStatusForm($loggedInMember, $member);

        $commentRepository = $entityManager->getRepository(Comment::class);
        $comments = $commentRepository->getCommentsMember($member);

        return $this->render('profile/comments.html.twig', [
            'use_lightbox' => false,
            'status_form' => $statusForm->createView(),
            'member' => $member,
            'comments' => $comments,
            'submenu' => $profileSubmenu->getSubmenu($member, $loggedInMember, [ 'active' => 'comments']),
        ]);
    }
}
