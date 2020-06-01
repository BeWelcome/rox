<?php

namespace App\Controller;

use App\Doctrine\CommentAdminActionType;
use App\Entity\Comment;
use App\Entity\FeedbackCategory;
use App\Entity\Member;
use App\Form\CustomDataClass\ReportCommentRequest;
use App\Form\ReportCommentType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Swift_Mailer;
use Swift_Message;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

class CommentController extends AbstractController
{
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
        Swift_Mailer $mailer,
        TranslatorInterface $translator
    ) {
//        \todo Should we only allow the receiver of a comment to report it?
//        if ($comment->getToMember()->getId() !== $member->getId() && $comment->getFromMember()->getId() !== $member->getId()) {
//            throw new AccessDeniedException('Hau ab!');
//        }

        $user = $this->getUser();

        $form = $this->createForm(ReportCommentType::class, new ReportCommentRequest());
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $feedback = trim(str_replace("\xc2\xa0", ' ', strip_tags(html_entity_decode($data->feedback, ENT_HTML5, 'UTF-8'))));
            if (empty($feedback)) {
                $form->addError(new FormError('Feedback can not be empty.'));
            } else {
                $messageText = $this->render('emails/comment.feedback.html.twig', [
                    'comment' => $comment,
                    'feedback' => $feedback,
                ]);
                // Get the email address that is associated with admin comments category
                $feedbackCategoryRepository = $this->getDoctrine()->getRepository(FeedbackCategory::class);
                $feedbackCategory = $feedbackCategoryRepository->findOneBy(['name' => 'Comment_issue']);

                $message = (new Swift_Message())
                    ->setSubject('Comment report')
                    ->setFrom(
                        [
                            $user->getEmail() => 'BeWelcome - ' . $user->getUsername(),
                        ]
                    )
                    ->setTo([
                        $feedbackCategory->getEmailToNotify() => 'Comment Issue',
                    ])
                    ->setBody(
                        $messageText,
                        'text/html'
                    );
                $recipients = $mailer->send($message);
                if (0 === $recipients) {
                    $this->addFlash('error', $translator->trans('flash.feedback.not.sent'));
                } else {
                    $em = $this->getDoctrine()->getManager();
                    $comment->setAdminAction(CommentAdminActionType::ADMIN_CHECK);
                    $em->persist($comment);
                    $em->flush();

                    $this->addFlash('notice', $translator->trans('flash.feedback.safetyteam'));

                    return $this->redirectToRoute('profile_all_comments', ['username' => $member->getUsername()]);
                }
            }
        }

        return $this->render('member/report.comment.html.twig', [
            'form' => $form->createView(),
            'comment' => $comment,
            'member' => $member,
        ]);
    }
}
