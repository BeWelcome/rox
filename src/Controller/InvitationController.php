<?php

namespace App\Controller;

use App\Entity\HostingRequest;
use App\Entity\Member;
use App\Entity\Message;
use App\Entity\Subject;
use App\Entity\Subtrip;
use App\Form\InvitationGuest;
use App\Form\InvitationHost;
use App\Form\InvitationType;
use App\Model\ConversationModel;
use App\Model\InvitationModel;
use App\Service\Mailer;
use App\Utilities\TranslatedFlashTrait;
use App\Utilities\TranslatorTrait;
use DateTime;
use Exception;
use InvalidArgumentException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Contracts\Translation\TranslatorInterface;

class InvitationController extends BaseRequestAndInvitationController
{
    use TranslatedFlashTrait;
    use TranslatorTrait;

    private Mailer $mailer;

    public function __construct(
        ConversationModel $conversationModel,
        InvitationModel $invitationModel,
        Mailer $mailer
    ) {
        parent::__construct($invitationModel);
        $this->mailer = $mailer;
        $this->conversationModel = $conversationModel;
    }

    /**
     * @Route("/new/invitation/{leg}", name="hosting_invitation")
     *
     * @throws Exception
     */
    public function newInvitation(Request $request, Subtrip $leg, TranslatorInterface $translator): Response
    {
        /** @var Member $host */
        $host = $this->getUser();
        $guest = $leg->getTrip()->getCreator();
        if ($host === $guest) {
            $this->addTranslatedFlash('notice', 'flash.request.invitation.self');

            return $this->forward('MessageController::reply', ['message' => $invitation]);
        }

        if (!$guest->isBrowseable()) {
            $this->addTranslatedFlash('note', 'flash.member.invalid');
        }

        // \todo Decide if this should be removed in this case
        if (
            $this->conversationModel->hasRequestLimitExceeded(
                $host,
                $this->getParameter('new_members_requests_per_hour'),
                $this->getParameter('new_members_requests_per_day')
            )
        ) {
            $this->addTranslatedFlash('error', 'flash.request.limit');
            $referrer = $request->headers->get('referer');

            return $this->redirect($referrer);
        }

        $subject = new Subject();
        $subjectText = $translator->trans(
            'invitation',
            [],
            null,
            $guest->getPreferredLanguage()->getShortcode()
        );
        $subjectText .= ' - ' . $leg->getTrip()->getSummary() . ' - ';
        $subjectText .= $leg->getLocation()->getName();
        $subject->setSubject($subjectText);

        $hostingRequest = new HostingRequest();
        $hostingRequest->setArrival($leg->getArrival());
        $hostingRequest->setDeparture($leg->getDeparture());
        $hostingRequest->setNumberOfTravellers($leg->getTrip()->getCountOfTravellers());

        $invitation = new Message();
        $invitation->setSubject($subject);
        $invitation->setRequest($hostingRequest);

        $invitationForm = $this->createForm(InvitationType::class, $invitation);
        $invitationForm->handleRequest($request);

        if ($invitationForm->isSubmitted() && $invitationForm->isValid()) {
            $invitation = $this->getMessageFromData($invitationForm->getData(), $host, $guest);
            $invitation->getRequest()->setInviteForLeg($leg);

            $em = $this->getDoctrine()->getManager();
            $em->persist($invitation);
            $em->flush();

            $this->sendInvitationNotification(
                $host,
                $guest,
                $host,
                $invitation,
                $invitation->getSubject()->getSubject(),
                'invitation',
                false,
                null
            );

            $this->addTranslatedFlash('notice', 'flash.request.invitation.sent');

            return $this->redirectToRoute('members_profile', ['username' => $guest->getUsername()]);
        }

        return $this->render('invitation/invite.html.twig', [
            'leg' => $leg,
            'subject' => $subjectText,
            'form' => $invitationForm->createView(),
        ]);
    }

    public function guestReply(
        Request $request,
        Message $invitation,
        Member $guest,
        Member $host
    ): Response {
        if ($this->model->hasExpired($invitation)) {
            $this->addExpiredFlash($host);

            return $this->forward(MessageController::class . ':reply', ['message' => $invitation]);
        }

        list($thread) = $this->conversationModel->getThreadInformationForMessage($invitation);

        // keep all information from current hosting request except the message text
        $invitation = $this->getRequestClone($invitation);
        $leg = $invitation->getRequest()->getInviteForLeg();

        // A reply consists of a new message and maybe a change of the status of the hosting request
        // Additionally the user might change the dates of the request or cancel the request altogether
        /** @var Form $requestForm */
        $requestForm = $this->createForm(InvitationGuest::class, $invitation);
        $requestForm->handleRequest($request);

        if ($requestForm->isSubmitted() && $requestForm->isValid()) {
            $realParent = $this->conversationModel->getLastMessageInConversation($invitation);
            $newRequest = $this->persistRequest($requestForm, $realParent, $guest, $host);

            // In case the potential guest declines the invitation remove the invitedBy from the leg
            $em = $this->getDoctrine()->getManager();
            if (HostingRequest::REQUEST_ACCEPTED === $newRequest->getRequest()->getStatus()) {
                $leg->setInvitedBy($host);
                $em->persist($leg);
            }
            if (HostingRequest::REQUEST_DECLINED === $newRequest->getRequest()->getStatus()) {
                $leg->setInvitedBy(null);
                $em->persist($leg);
            }
            $em->flush();

            $subject = $this->getSubjectForReply($newRequest);

            $this->sendInvitationGuestReplyNotification(
                $host,
                $guest,
                $newRequest,
                $subject,
                ($newRequest->getRequest()->getId() !== $realParent->getRequest()->getId()),
                $leg
            );
            $this->addTranslatedFlash('notice', 'flash.notification.updated');

            return $this->redirectToRoute('conversation_view', ['id' => $newRequest->getId()]);
        }

        return $this->render('invitation/reply_from_guest.html.twig', [
            'guest' => $guest,
            'host' => $host,
            'form' => $requestForm->createView(),
            'thread' => $thread,
            'leg' => $leg,
        ]);
    }

    public function hostReply(Request $request, Message $invitation, Member $guest, Member $host): Response
    {
        if ($this->model->hasExpired($invitation)) {
            $this->addExpiredFlash($guest);

            return $this->forward(MessageController::class . '::reply', ['message' => $invitation]);
        }

        list($thread) = $this->conversationModel->getThreadInformationForMessage($invitation);

        // keep all information from current invitation except the message text
        $invitation = $this->getRequestClone($invitation);
        $leg = $invitation->getRequest()->getInviteForLeg();

        /** @var Form $requestForm */
        $requestForm = $this->createForm(InvitationHost::class, $invitation);
        $requestForm->handleRequest($request);

        if ($requestForm->isSubmitted() && $requestForm->isValid()) {
            $realParent = $this->conversationModel->getLastMessageInConversation($invitation);

            // Switch $guest and $host for persist request as the thread is started by the potential host.
            $newRequest = $this->persistRequest($requestForm, $realParent, $host, $guest);

            $subject = $this->getSubjectForReply($newRequest);

            $this->sendInvitationHostReplyNotification(
                $host,
                $guest,
                $newRequest,
                $subject,
                ($newRequest->getRequest()->getId() !== $realParent->getRequest()->getId()),
                $leg
            );
            $this->addTranslatedFlash('notice', 'flash.notification.updated');

            return $this->redirectToRoute('conversation_view', ['id' => $newRequest->getId()]);
        }

        return $this->render('invitation/reply_from_host.html.twig', [
            'guest' => $guest,
            'host' => $host,
            'form' => $requestForm->createView(),
            'thread' => $thread,
            'leg' => $leg,
        ]);
    }

    protected function addExpiredFlash(Member $receiver)
    {
        $this->addTranslatedFlash('notice', 'flash.invitation.expired', [
            '%link_start%' => '<a href="' . $this->generateUrl('message_new', [
                    'username' => $receiver->getUsername(),
                ]) . '" class="text-primary">',
            '%link_end%' => '</a>',
        ]);
    }

    private function sendInvitationGuestReplyNotification(
        Member $host,
        Member $guest,
        Message $request,
        string $subject,
        bool $requestChanged,
        SubTrip $leg
    ): void {
        $this->sendInvitationNotification(
            $guest,
            $host,
            $host,
            $request,
            $subject,
            'invitation_reply_from_guest',
            $requestChanged,
            $leg
        );
    }

    private function sendInvitationHostReplyNotification(
        Member $host,
        Member $guest,
        Message $request,
        string $subject,
        bool $requestChanged,
        SubTrip $leg
    ): void {
        $this->sendInvitationNotification(
            $host,
            $guest,
            $host,
            $request,
            $subject,
            'invitation_reply_from_host',
            $requestChanged,
            $leg
        );
    }

    /**
     * @SuppressWarnings(PHPMD.BooleanArgumentFlag)
     *
     * @param mixed $subject
     */
    private function sendInvitationNotification(
        Member $sender,
        Member $receiver,
        Member $host,
        Message $request,
        $subject,
        string $template,
        bool $requestChanged,
        ?Subtrip $leg
    ): bool {
        // Send mail notification
        $this->mailer->sendMessageNotificationEmail($sender, $receiver, $template, [
            'host' => $host,
            'subject' => $subject,
            'message' => $request,
            'request' => $request->getRequest(),
            'changed' => $requestChanged,
            'leg' => $leg,
        ]);

        return true;
    }
}
