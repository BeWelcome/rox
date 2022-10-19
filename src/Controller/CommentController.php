<?php

namespace App\Controller;

use App\Doctrine\CommentAdminActionType;
use App\Doctrine\CommentQualityType;
use App\Entity\Comment;
use App\Entity\Member;
use App\Entity\Preference;
use App\Form\CommentType;
use App\Form\CustomDataClass\ReportCommentRequest;
use App\Form\ReportCommentType;
use App\Model\CommentModel;
use App\Model\ProfileModel;
use App\Service\Mailer;
use App\Utilities\ProfileSubmenu;
use App\Utilities\TranslatedFlashTrait;
use App\Utilities\TranslatorTrait;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Contracts\Translation\TranslatorInterface;

class CommentController extends AbstractController
{
    use TranslatorTrait;
    use TranslatedFlashTrait;

    /**
     * @Route("/members/{to_member}/comment/{from_member}/report", name="report_comment",
     *     requirements={"username" = "(?i:[a-z](?!.*[-_.][-_.])[a-z0-9-._]{2,18}[a-z0-9])"}))
     *
     * @ParamConverter("member", class="App\Entity\Member", options={"mapping": {"to_member": "username"}})
     * @ParamConverter("member", class="App\Entity\Member", options={"mapping": {"from_member": "username"}})
     *
     * @return Response
     */
    public function reportCommentAction(
        Request $request,
        Member $toMember,
        Member $fromMember,
        EntityManagerInterface $entityManager,
        TranslatorInterface $translator,
        Mailer $mailer
    ) {
        /** @var Member $member */
        $member = $this->getUser();

        if ($member != $toMember) {
            throw new AccessDeniedException();
        }

        $commentRepository = $entityManager->getRepository(Comment::class);
        $comment = $commentRepository->findOneBy([
            'toMember' => $toMember,
            'fromMember' => $fromMember,
        ]);

        $form = $this->createForm(ReportCommentType::class, new ReportCommentRequest());
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $feedback = trim(
                str_replace(
                    "\xc2\xa0",
                    ' ',
                    strip_tags(html_entity_decode($data->feedback, \ENT_HTML5, 'UTF-8'))
                )
            );
            if (empty($feedback)) {
                $form->addError(new FormError('Feedback can not be empty.'));
            } else {
                $success = $mailer->sendCommentReportedFeedbackEmail(
                    $member,
                    [
                        'subject' => 'Comment report',
                        'comment' => $comment,
                        'feedback' => $feedback,
                    ]
                );

                if ($success) {
                    $comment->setAdminAction(CommentAdminActionType::ADMIN_CHECK);
                    $entityManager->persist($comment);
                    $entityManager->flush();

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
    public function addComment(
        Request $request,
        Member $member,
        CommentModel $commentModel,
        ProfileSubmenu $profileSubmenu,
        EntityManagerInterface $entityManager
    ): Response {
        /** @var Member $loggedInMember */
        $loggedInMember = $this->getUser();

        if ($loggedInMember === $member) {
            return $this->redirectToRoute('members_profile', ['username' => $member->getUsername()]);
        }

        $comment = $commentModel->getCommentForMemberPair($loggedInMember, $member);

        if (null !== $comment) {
            return $this->redirectToRoute('edit_comment', ['username' => $member->getUsername()]);
        }

        $preferenceRepository = $this->getDoctrine()->getRepository(Preference::class);

        /** @var Preference $preference */
        $preference = $preferenceRepository->findOneBy(['codename' => Preference::READ_COMMENT_GUIDELINES]);
        $memberPreference = $loggedInMember->getMemberPreference($preference);
        $showCommentGuideline = ('0' === $memberPreference->getValue());

        /** @var Member $loggedInMember */
        $form = $this->createForm(CommentType::class, null, [
            'to_member' => $member,
            'show_comment_guideline' => $showCommentGuideline,
        ]);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var Comment $comment */
            $comment = $form->getData();
            $comment->setToMember($member);
            $comment->setFromMember($loggedInMember);
            $entityManager->persist($comment);

            // Mark comment guidelines as read and hide the checkbox for the future
            $memberPreference->setValue('1');
            $entityManager->persist($memberPreference);
            $entityManager->flush();
            $this->addTranslatedFlash(
                'notice',
                'flash.comment.added',
                [
                    'username' => $member->getUsername(),
                ]
            );

            return $this->redirectToRoute('profile_comments', ['username' => $member->getUsername()]);
        }

        return $this->render('/profile/comment.add.html.twig', [
            'form' => $form->createView(),
            'member' => $member,
            'submenu' => $profileSubmenu->getSubmenu($member, $loggedInMember, ['active' => 'comment']),
        ]);
    }

    /**
     * @Route("/members/{username}/comment/edit", name="edit_comment",
     *     requirements={"username" = "(?i:[a-z](?!.*[-_.][-_.])[a-z0-9-._]{2,18}[a-z0-9])"}))
     */
    public function editComment(
        Request $request,
        Member $member,
        ProfileSubmenu $profileSubmenu,
        CommentModel $commentModel,
        EntityManagerInterface $entityManager
    ): Response {
        /** @var Member $loggedInMember */
        $loggedInMember = $this->getUser();

        if ($loggedInMember === $member) {
            return $this->redirectToRoute('members_profile', ['username' => $member->getUsername()]);
        }

        $comment = $commentModel->getCommentForMemberPair($loggedInMember, $member);
        if (null === $comment) {
            return $this->redirectToRoute('add_comment', ['username' => $member->getUsername()]);
        }

        if ($comment->getQuality() == CommentQualityType::NEGATIVE && !$comment->getEditingAllowed()) {
            $this->addTranslatedFlash('comment.editing.not.allowed', []);

            return $this->redirectToRoute('members_profile', ['username' => $member->getUsername()]);
        }

        $originalComment = clone $comment;
        $form = $this->createForm(
            CommentType::class,
            $comment,
            [
                'to_member' => $member,
                'show_comment_guideline' => false,
            ]
        );

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var Comment $comment */
            $comment = $form->getData();
            $newExperience = $commentModel->checkIfNewExperience($originalComment, $comment);
            if ($newExperience) {
                $comment->setUpdated(new DateTime());
            }
            $entityManager->persist($comment);
            $entityManager->flush();

            return $this->redirectToRoute('profile_comments', ['username' => $member->getUsername()]);
        }

        return $this->render('/profile/comment.edit.html.twig', [
            'form' => $form->createView(),
            'member' => $member,
            'submenu' => $profileSubmenu->getSubmenu($member, $loggedInMember, ['active' => 'comment']),
        ]);
    }

    /**
     * @Route("/members/{username}/comments", name="profile_comments",
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
        $statusFormView = (null === $statusForm) ? null : $statusForm->createView();

        $commentRepository = $entityManager->getRepository(Comment::class);
        $comments = $commentRepository->getCommentsMember($member);

        return $this->render('profile/comments.html.twig', [
            'use_lightbox' => false,
            'status_form' => $statusFormView,
            'member' => $member,
            'comments' => $comments,
            'submenu' => $profileSubmenu->getSubmenu($member, $loggedInMember, ['active' => 'comments']),
        ]);
    }
}
