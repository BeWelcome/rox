<?php

namespace App\Controller;

use App\Entity\Activity;
use App\Entity\ActivityAttendee;
use App\Entity\BroadcastMessage;
use App\Entity\CommunityNews;
use App\Entity\CommunityNewsComment;
use App\Entity\Donation;
use App\Entity\Newsletter;
use App\Entity\Comment;
use App\Entity\CryptedField;
use App\Entity\ForumPost;
use App\Entity\GroupMembership;
use App\Entity\Log;
use App\Entity\Member;
use App\Entity\MembersPhoto;
use App\Entity\MemberTranslation;
use App\Entity\Message;
use App\Entity\PasswordReset;
use App\Entity\Preference;
use App\Form\FindUserFormType;
use App\Form\ResetPasswordFormType;
use App\Logger\Logger;
use App\Model\MemberModel;
use App\Repository\MemberRepository;
use App\Repository\MessageRepository;
use App\Utilities\MailerTrait;
use App\Utilities\ManagerTrait;
use App\Utilities\TranslatedFlashTrait;
use App\Utilities\TranslatorTrait;
use Doctrine\ORM\NonUniqueResultException;
use Exception;
use Html2Text\Html2Text;
use Mockery\Container;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use Swift_Message;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGenerator;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Validator\Constraints\NotBlank;
use ZipArchive;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;

/**
 * Class MemberController.
 */
class MemberController extends AbstractController
{
    use MailerTrait;
    use ManagerTrait;
    use TranslatorTrait;
    use TranslatedFlashTrait;

    /**
     * @Route("/mydata/{username}", name="member_get_data")
     *
     * @param Request $request
     * @param Member $member
     * @param Logger $logger
     * @param ContainerBagInterface $params
     * @param MemberModel $memberModel
     * @param UrlGeneratorInterface $urlGenerator
     * @return BinaryFileResponse
     * @throws Exception
     * @ParamConverter("member", class="App\Entity\Member", options={"mapping": {"username": "username"}})
     */
    public function getPersonalData(
        Request $request,
        Member $member,
        Logger $logger,
        ContainerBagInterface $params,
        MemberModel $memberModel
    ) {
        // Either the member themselves or a person from the safety or profile team and the admin can access
        if ($member != $this->getUser()) {
            $this->denyAccessUnlessGranted(
                [Member::ROLE_ADMIN_SAFETYTEAM, Member::ROLE_ADMIN_ADMIN, Member::ROLE_ADMIN_PROFILE],
                null,
                'Unable to access this page!');
            $logger->write('Extracting personal data for '.$member->getUsername(), 'Members');
        }
        // Collect information and store in zip file
        $zipFilename = $memberModel->collectPersonalData($params, $member);

        // main dir is left over!
        $response = new BinaryFileResponse( $zipFilename );
        $response->setContentDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT
        );
        $response->deleteFileAfterSend(true);

        return $response;
    }

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
     * @param Request     $request
     * @param MemberModel $memberModel
     *
     * @return Response
     */
    public function requestResetPasswordAction(Request $request, MemberModel $memberModel)
    {
        // Someone obviously lost their way. No sense in resetting your password if you're currently logged in.
        if ($this->isGranted('ROLE_USER')) {
            return $this->redirectToRoute('landingpage');
        }

        $form = $this->createFormBuilder()
            ->add('username', TextType::class, [
                'constraints' => [
                    new NotBlank(),
                ],
            ])
            ->add('reset.password', SubmitType::class)
            ->setMethod('POST')
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $member = null;
            /** @var MemberRepository $memberRepository */
            $memberRepository = $this->getDoctrine()->getRepository(Member::class);
            try {
                /** @var Member $member */
                $member = $memberRepository->loadUserByUsername($data['username']);
            } catch (NonUniqueResultException $e) {
            }
            if (null === $member) {
                $form->addError(new FormError($this->getTranslator()->trans('flash.email.reset.password')));
            } else {
                $token = null;
                try {
                    $token = $memberModel->generatePasswordResetToken($member);
                } catch (Exception $e) {
                }
                if (null === $token) {
                    $this->addTranslatedFlash('error', 'flash.no.reset.password');

                    return $this->redirectToRoute('security_login');
                }

                /* Sent the member a link to follow to reset the password */
                $sent = $this->sendPasswordResetLink(
                    $member,
                    'Password Reset for BeWelcome',
                    $token
                );
                if ($sent) {
                    $this->addTranslatedFlash('notice', 'flash.email.reset.password');

                    return $this->redirectToRoute('security_login');
                }
                $form->addError(new FormError('There was an error sending the password reset link.'));
            }
        }

        return $this->render('member/request.password.reset.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/resetpassword/{username}/{token}", name="member_reset_password",
     *     requirements={"key": "[a-z0-9]{32}"})
     *
     * @param Request $request
     * @param Member  $member
     * @param $token
     *
     * @return Response
     */
    public function resetPasswordAction(Request $request, Member $member, $token)
    {
        // Someone obviously lost their way. No sense in resetting your password if you're currently logged in.
        if ($this->isGranted('ROLE_USER')) {
            return $this->redirectToRoute('landingpage');
        }

        $repository = $this->getDoctrine()->getRepository(PasswordReset::class);
        /** @var PasswordReset $passwordReset */
        $passwordReset = $repository->findOneBy(['member' => $member, 'token' => $token]);

        if (null === $passwordReset) {
            $this->addTranslatedFlash('error', 'flash.reset.password.invalid');

            return $this->redirectToRoute('member_request_reset_password');
        }

        $diffInDays = $passwordReset->getGenerated()->diffInDays();
        if ($diffInDays > 2) {
            $this->addFlash('error', 'flash.reset.password.invalid');

            return $this->redirectToRoute('member_request_reset_password');
        }

        $form = $this->createForm(ResetPasswordFormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $newPassword = $data['password'];
            $member->setPassword($newPassword);
            $em = $this->getDoctrine()->getManager();
            $em->persist($member);
            $em->flush();
            $this->addTranslatedFlash('notice', 'flash.password.reset');

            return $this->redirectToRoute('security_login');
        }

        return $this->render('member/reset.password.html.twig', [
            'form' => $form->createView(),
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
        $countWidget = $toastWidget = '';
        $lastUnreadCount = (int) ($request->request->get('current'));

        /** @var MessageRepository $messageRepository */
        $messageRepository = $this->getDoctrine()->getRepository(Message::class);
        $unreadMessageCount = $messageRepository->getUnreadMessagesCount($member);

        if (($unreadMessageCount !== $lastUnreadCount) && ($unreadMessageCount > $lastUnreadCount)) {
            $countWidget = $this->renderView('widgets/messagescount.hml.twig', [
                'messageCount' => $unreadMessageCount,
            ]);
            $toastWidget = $this->renderView('widgets/messages.toast.html.twig', [
                'messageCount' => $unreadMessageCount,
                'lastMessageCount' => $lastUnreadCount,
            ]);
        }
        $response = new JsonResponse();
        $response->setData([
            'oldCount' => $lastUnreadCount,
            'newCount' => $unreadMessageCount,
            'html' => $countWidget,
            'toast' => $toastWidget,
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
        $countWidget = $toastWidget = '';
        $lastUnreadCount = (int) ($request->request->get('current'));

        /** @var MessageRepository $messageRepository */
        $messageRepository = $this->getDoctrine()->getRepository(Message::class);
        $unreadRequestsCount = $messageRepository->getUnreadRequestsCount($member);

        if (($unreadRequestsCount !== $lastUnreadCount) && ($unreadRequestsCount > $lastUnreadCount)) {
            $countWidget = $this->renderView('widgets/requestscount.html.twig', [
                'requestCount' => $unreadRequestsCount,
            ]);
            $toastWidget = $this->renderView('widgets/requests.toast.html.twig', [
                'requestCount' => $unreadRequestsCount,
                'lastRequestCount' => $lastUnreadCount,
            ]);
        }
        $response = new JsonResponse();
        $response->setData([
            'oldCount' => $lastUnreadCount,
            'newCount' => $unreadRequestsCount,
            'html' => $countWidget,
            'toast' => $toastWidget,
        ]);

        return $response;
    }

    private function sendPasswordResetLink(Member $receiver, $subject, $token)
    {
        $this->sendTemplateEmail('password@bewelcome.org', $receiver, 'reset.password', [
            'receiver' => $receiver,
            'subject' => $subject,
            'token' => $token,
        ]);

        return true;
    }
}
