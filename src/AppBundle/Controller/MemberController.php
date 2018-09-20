<?php

namespace AppBundle\Controller;

use AppBundle\Doctrine\CommentAdminActionType;
use AppBundle\Entity\Comment;
use AppBundle\Entity\FeedbackCategory;
use AppBundle\Entity\Member;
use AppBundle\Entity\Message;
use AppBundle\Form\CustomDataClass\ReportCommentRequest;
use AppBundle\Form\ReportCommentType;
use AppBundle\Repository\MemberRepository;
use AppBundle\Repository\MessageRepository;
use Doctrine\ORM\NonUniqueResultException;
use Html2Text\Html2Text;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Swift_Message;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Finder\Exception\AccessDeniedException;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Constraints\NotBlank;

class MemberController extends Controller
{
    /**
     * @Route("/member/autocomplete", name="members_autocomplete")
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function autoCompleteAction(Request $request)
    {
        $names = [];
        $callback = trim(strip_tags($request->get('callback')));
        $term = trim(strip_tags($request->get('term')));

        $em = $this->getDoctrine()->getManager();

        /** @var MemberRepository $memberRepository */
        $memberRepository = $em->getRepository(Member::class);
            $entities = $memberRepository->loadMembersByUsernamePart($term);

        foreach ($entities as $entity) {
            $names[] = [
                'id' => $entity['username'],
                'label' => $entity['username'],
                'value' => $entity['username'],
            ];
        }

        $response = new JsonResponse();
        $response->setCallback($callback);
        $response->setData($names);

        return $response;
    }

    /**
     * @Route("/resetpassword", name="member_request_reset_password")
     *
     * @param Request $request
     *
     * @return Response
     */
    public function requestResetPasswordAction(Request $request)
    {
        // Someone obviously lost their way. No sense in resetting your password if you're currently logged in.
        if ($this->isGranted('ROLE_USER')) {
            return $this->redirectToRoute('landingpage');
        }

        $form = $this->createFormBuilder()
            ->add('usernameOrEmail', TextType::class, [
                'constraints' => [
                    new NotBlank()
                ],
            ])
            ->getForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $member = null;
            /** @var MemberRepository $memberRepository */
            $memberRepository = $this->getDoctrine()->getRepository(Member::class);
            try {
                /** @var Member $member */
                $member = $memberRepository->loadUserByUsername($data['usernameOrEmail']);
            }
            catch (NonUniqueResultException $e) {

            }
            if ($member === null) {
                $form->addError( new FormError('No member with that username or email address.'));
            } else {
                /* Sent the member a link to follow to reset the password */
                $sent = $this->sendPasswordResetLink($member, 'Password Reset for BeWelcome', $member->generatePasswordResetKey());
                if ($sent) {
                    $this->addFlash('notice', 'We just sent you a mail with a link that allows you to reset your password.');
                    return $this->redirectToRoute('security_login');
                } else {
                    $form->addError( new FormError('There was an error sending the password reset link.'));
                }
            }
        }
        return $this->render(':member:request.password.reset.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/resetpassword/{username}/{key}", name="member_reset_password")
     *
     * @param Request $request
     * @param Member $member
     * @param $key
     * @return Response
     */
    public function resetPasswordAction(Request $request, Member $member, $key)
    {
        // Someone obviously lost their way. No sense in resetting your password if you're currently logged in.
        if ($this->isGranted('ROLE_USER')) {
            return $this->redirectToRoute('landingpage');
        }

        $resetPasswordKey = $member->generatePasswordResetKey();
        if ($resetPasswordKey !== $key) {
            $this->addFlash('notice', 'Either username or key aren\'t valid to reset the password.');
            return $this->redirectToRoute('login');
        }

        $form = $this->createFormBuilder()
            ->add('password', RepeatedType::class, [
                'type' => PasswordType::class,
                'invalid_message' => 'The password fields must match.',
                'required' => true,
                'first_options'  => ['label' => 'Password'],
                'second_options' => ['label' => 'Repeat Password'],
            ])
            ->getForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $newPassword = $data['password'];
            $member->setPassword($newPassword);
            $em = $this->getDoctrine()->getManager();
            $em->persist($member);
            $em->flush();
            $this->addFlash('notice', 'Your password has been reset. Please login now with the new password.');
            return $this->redirectToRoute('security_login');
        }

        return $this->render(':member:reset.password.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    private function sendPasswordResetLink(Member $receiver, $subject, $key)
    {
        // Send mail notification
        $html = $this->renderView('emails/reset.password.html.twig', [
            'receiver' => $receiver,
            'subject' => $subject,
            'key' => $key,
        ]);
        $plainText = new Html2Text($html);
        $message = new Swift_Message();
        $message
            ->setSubject($subject)
            ->setFrom(
                [
                    'password@bewelcome.org' => 'BeWelcome',
                ]
            )
            ->setTo($receiver->getEmail())
            ->setBody(
                $html,
                'text/html'
            )
            ->addPart(
                $plainText->getText(),
                'text/plain'
            )
        ;
        $recipients = $this->get('mailer')->send($message);

        return (0 === $recipients) ? false : true;
    }

    /**
     * @Route("/members/{username}/comment/{commentId}/report", name="report_comment",
     *     requirements={"username" = "(?i:[a-z](?!.*[-_.][-_.])[a-z0-9-._]{2,18}[a-z0-9])"}))
     *
     * @ParamConverter("member", class="AppBundle\Entity\Member", options={"mapping": {"username": "username"}})
     * @ParamConverter("comment", class="AppBundle\Entity\Comment", options={"mapping": {"commentId": "id"}})
     *
     * @param Request $request
     * @param Member  $member
     * @param Comment $comment
     *
     * @return Response
     */
    public function reportCommentAction(Request $request, Member $member, Comment $comment)
    {
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
                $messageText = $this->render(':emails:comment.feedback.html.twig', [
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
                            $user->getEmail() => 'BeWelcome - '.$user->getUsername(),
                        ]
                    )
                    ->setTo([
                        $feedbackCategory->getEmailToNotify() => 'Comment Issue',
                    ])
                    ->setBody(
                        $messageText,
                        'text/html'
                    );
                $recipients = $this->get('mailer')->send($message);
                if (0 === $recipients) {
                    $this->addFlash('error', 'Your feedback couldn\'t be sent. Please try again later.');
                } else {
                    $em = $this->getDoctrine()->getManager();
                    $comment->setAdminAction(CommentAdminActionType::ADMIN_CHECK);
                    $em->persist($comment);
                    $em->flush();

                    $this->addFlash('notice', 'Your feedback has been forwarded to the Safety Team.');

                    return $this->redirectToRoute('profile_all_comments', ['username' => $member->getUsername()]);
                }
            }
        }

        return $this->render(':member:report.comment.html.twig', [
            'form' => $form->createView(),
            'comment' => $comment,
            'member' => $member,
        ]);
    }

    /**
     * @Route("/count/messages/unread", name="count_messages_unread")
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function getUnreadMessagesCount(Request $request)
    {
        $member = $this->getUser();
        $countWidget = '';
        $lastUnreadCount = (int) ($request->request->get('current'));

        /** @var MessageRepository $messageRepository */
        $messageRepository = $this->getDoctrine()->getRepository(Message::class);
        $unreadMessageCount = $messageRepository->getUnreadMessagesCount($member);

        if ($unreadMessageCount !== $lastUnreadCount) {
            $countWidget = $this->renderView(':widgets:messagescount.hml.twig', [
                'messageCount' => $unreadMessageCount,
            ]);
        }
        $response = new JsonResponse();
        $response->setData([
            'oldCount' => $lastUnreadCount,
            'newCount' => $unreadMessageCount,
            'html' => $countWidget,
        ]);

        return $response;
    }

    /**
     * @Route("/count/requests/unread", name="count_requests_unread")
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function getUnreadRequestsCount(Request $request)
    {
        $member = $this->getUser();
        $countWidget = '';
        $lastUnreadCount = (int) ($request->request->get('current'));

        /** @var MessageRepository $messageRepository */
        $messageRepository = $this->getDoctrine()->getRepository(Message::class);
        $unreadRequestsCount = $messageRepository->getUnreadRequestsCount($member);

        if ($unreadRequestsCount !== $lastUnreadCount) {
            $countWidget = $this->renderView(':widgets:requestscount.html.twig', [
                'requestCount' => $unreadRequestsCount,
            ]);
        }
        $response = new JsonResponse();
        $response->setData([
            'oldCount' => $lastUnreadCount,
            'newCount' => $unreadRequestsCount,
            'html' => $countWidget,
        ]);

        return $response;
    }
}
