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
use App\Logger\Logger;
use App\Model\ConversationModel;
use App\Model\InvitationModel;
use App\Service\Mailer;
use App\Utilities\ConversationThread;
use App\Utilities\TranslatedFlashTrait;
use App\Utilities\TranslatorTrait;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class InvitationController extends BaseRequestAndInvitationController
{
    use TranslatedFlashTrait;
    use TranslatorTrait;

    private Mailer $mailer;
    private Logger $logger;
    private InvitationModel $invitationModel;

    public function __construct(
        ConversationModel $conversationModel,
        InvitationModel $invitationModel,
        EntityManagerInterface $entityManager,
        Mailer $mailer,
        Logger $logger
    ) {
        parent::__construct($invitationModel, $entityManager);
        $this->mailer = $mailer;
        $this->conversationModel = $conversationModel;
        $this->invitationModel = $invitationModel;
        $this->logger = $logger;
    }

    /**
     * @Route("/new/invitation/{leg}", name="hosting_invitation")
     *
     * @throws Exception
     */
    public function newInvitation(Request $request, Subtrip $leg): Response
    {
        /** @var Member $host */
        $host = $this->getUser();
        $guest = $leg->getTrip()->getCreator();
        if ($host === $guest) {
            $this->addTranslatedFlash('notice', 'flash.request.invitation.self');

            return $this->redirectToRoute('homepage');
        }

        if (!$guest->isBrowsable()) {
            $this->addTranslatedFlash('note', 'flash.member.invalid');
        }

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

        $redirectOnNotAllowed = false;
        $hasProfilePicture = $this->checkIfMemberHasProfilePicture($host);
        $allowWithoutProfilePicture = $this->getAllowRequestsWithoutProfilePicture($guest);
        if (!$allowWithoutProfilePicture && !$hasProfilePicture) {
            $redirectOnNotAllowed = true;
            $this->addTranslatedFlash('notice', 'invitation.not.without.profile.picture');
        }

        $hasAboutMe = $this->checkIfMemberHasAboutMe($guest);
        $allowWithoutAboutMe = $this->getAllowRequestsWithoutAboutMe($guest);
        if (!$allowWithoutAboutMe && !$hasAboutMe) {
            $redirectOnNotAllowed = true;
            $this->addTranslatedFlash('notice', 'invitation.not.without.about_me');
        }

        if ($redirectOnNotAllowed) {
            return $this->redirectToRoute('members_profile', ['username' => $host->getUsername()]);
        }

        $hostingRequest = new HostingRequest();
        $hostingRequest->setArrival($leg->getArrival());
        $hostingRequest->setDeparture($leg->getDeparture());
        $hostingRequest->setNumberOfTravellers($leg->getTrip()->getCountOfTravellers());

        $subject = new Subject();
        $invitation = new Message();
        $invitation->setSubject($subject);
        $invitation->setRequest($hostingRequest);

        $invitationForm = $this->createForm(InvitationType::class, $invitation);
        $invitationForm->handleRequest($request);

        if ($invitationForm->isSubmitted() && $invitationForm->isValid()) {
            $invitation = $this->getMessageFromData($invitationForm->getData(), $host, $guest);
            $invitation->getRequest()->setInviteForLeg($leg);
            $leg->addInvitation($invitation->getRequest());

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
            'subject' => '',
            'form' => $invitationForm->createView(),
        ]);
    }


    /**
     * Deals with declines
     */
    public function decline(Request $request, Message $message): Response
    {
        $conversationThread = new ConversationThread($this->entityManager);
        $conversation = $conversationThread->getThread($message);
        $current = $conversation[0];
        $request = $current->getRequest();
        $request->setStatus(HostingRequest::REQUEST_DECLINED);
        $em = $this->getDoctrine()->getManager();
        $em->persist($request);
        $em->flush();

        $host = $message->getInitiator();
        $guest = $message->getReceiver() === $host ? $message->getSender() : $message->getReceiver();

        $subject = $this->getSubjectForReply($message);
        $this->sendInvitationGuestDeclineNotification($host, $guest, $message, $subject, $request->getInviteForLeg());

        $this->addTranslatedFlash('notice', 'flash.invitation.declined');
        $this->logger->write('Directly declined', 'Invitation');

        return $this->redirectToRoute('conversation_view', ['id' => $message->getId()]);
    }

    /**
     * Deals with replies to invitations.
     */
    public function reply(Request $request, Message $message): Response
    {
        // determine if guest or host reply to a request
        $host = $message->getInitiator();
        $guest = $message->getReceiver() === $host ? $message->getSender() : $message->getReceiver();

        $member = $this->getUser();
        if ($member === $guest) {
            return $this->guestReply($request, $message, $guest, $host);
        }

        return $this->hostReply($request, $message, $guest, $host);
    }

    public function guestReply(
        Request $request,
        Message $invitation,
        Member $guest,
        Member $host
    ): Response {
        if (
            $this->model->hasExpired($invitation)
            || HostingRequest::REQUEST_CANCELLED === $invitation->getRequest()->getStatus()
        ) {
            $this->addExpiredFlash($host);

            return $this->forward(MessageController::class . ':reply', ['message' => $invitation]);
        }

        list($thread) = $this->conversationModel->getThreadInformationForMessage($invitation);

        // keep all information from current hosting request except the message text
        $invitation = $this->getMessageClone($invitation);
        $leg = $invitation->getRequest()->getInviteForLeg();
        $alreadyAccepted = (null !== $leg->getInvitedBy());

        // A reply consists of a new message and maybe a change of the status of the hosting request
        // Additionally the user might change the dates of the request or cancel the request altogether
        /** @var Form $requestForm */
        $requestForm = $this->createForm(InvitationGuest::class, $invitation, [
            'already_accepted' => $alreadyAccepted,
        ]);
        $requestForm->handleRequest($request);

        if ($requestForm->isSubmitted() && $requestForm->isValid()) {
            $realParent = $this->conversationModel->getLastMessageInConversation($invitation);
            $finalInvitation = $this->getFinalInvitation($requestForm, $realParent, $guest, $host);

            if (HostingRequest::REQUEST_ACCEPTED === $finalInvitation->getRequest()->getStatus()) {
                $this->handleAcceptOfInvitation($leg, $host, $finalInvitation);
            }

            // In case the potential guest declines the invitation remove the invitedBy from the leg
            if (HostingRequest::REQUEST_DECLINED === $finalInvitation->getRequest()->getStatus()) {
                $this->handleDeclineOfInvitation($leg, $host);
                $this->logger->write('Regular decline', 'Invitation');
            }
            $this->entityManager->persist($finalInvitation);
            $this->entityManager->flush();

            $subject = $this->getSubjectForReply($finalInvitation);

            $requestUpdated = $finalInvitation->getRequest()->getId() !== $realParent->getRequest()->getId();

            if ($requestUpdated) {
                $invitation->getRequest()->setInviteForLeg(null);
            }
            $this->entityManager->flush();

            $this->sendInvitationGuestReplyNotification(
                $host,
                $guest,
                $finalInvitation,
                $subject,
                $requestUpdated,
                $leg
            );
            $this->addTranslatedFlash('notice', 'flash.notification.updated');

            return $this->redirectToRoute('conversation_view', ['id' => $finalInvitation->getId()]);
        }

        return $this->render('invitation/reply_from_guest.html.twig', [
            'guest' => $guest,
            'host' => $host,
            'form' => $requestForm->createView(),
            'invitation' => $invitation->getRequest(),
            'already_accepted' => $alreadyAccepted,
            'thread' => $thread,
            'leg' => $leg,
        ]);
    }

    public function hostReply(Request $request, Message $invitation, Member $guest, Member $host): Response
    {
        if (
            $this->model->hasExpired($invitation)
            || HostingRequest::REQUEST_CANCELLED === $invitation->getRequest()->getStatus()
        ) {
            $this->addExpiredFlash($guest);

            return $this->forward(MessageController::class . '::reply', ['message' => $invitation]);
        }

        list($thread) = $this->conversationModel->getThreadInformationForMessage($invitation);

        // keep all information from current invitation except the message text
        $invitation = $this->getMessageClone($invitation);
        $leg = $invitation->getRequest()->getInviteForLeg();

        /** @var Form $invitationForm */
        $invitationForm = $this->createForm(InvitationHost::class, $invitation);
        $invitationForm->handleRequest($request);

        if ($invitationForm->isSubmitted() && $invitationForm->isValid()) {
            $realParent = $this->conversationModel->getLastMessageInConversation($invitation);
            $invitation->setParent($realParent);
            $invitation->setSender($host);
            $invitation->setReceiver($guest);

            $clickedButton = $invitationForm->getClickedButton()->getName();

            if ('cancel' === $clickedButton) {
                $invitation->getRequest()->setStatus(HostingRequest::REQUEST_CANCELLED);
                if ($leg->getInvitedBy() === $host) {
                    $leg->setInvitedBy(null);
                }
                $this->entityManager->persist($leg);
                $this->entityManager->persist($invitation->getRequest());
            }
            $this->entityManager->persist($invitation);
            $this->entityManager->flush();

            $subject = $this->getSubjectForReply($invitation);

            $this->sendInvitationHostReplyNotification(
                $host,
                $guest,
                $invitation,
                $subject,
                false,
                $leg
            );
            $this->addTranslatedFlash('notice', 'flash.notification.updated');

            return $this->redirectToRoute('conversation_view', ['id' => $invitation->getId()]);
        }

        return $this->render('invitation/reply_from_host.html.twig', [
            'guest' => $guest,
            'host' => $host,
            'form' => $invitationForm->createView(),
            'invitation' => $invitation->getRequest(),
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

    private function getFinalInvitation(
        Form $requestForm,
        Message $currentRequest,
        Member $sender,
        Member $receiver
    ): Message {
        $data = $requestForm->getData();

        $clickedButton = $requestForm->getClickedButton()->getName();

        // handle changes in invitation
        $newRequest = $this->model->getFinalRequest($sender, $receiver, $currentRequest, $data, $clickedButton);

        return $newRequest;
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

    private function sendInvitationGuestDeclineNotification(
        Member $host,
        Member $guest,
        Message $request,
        string $subject,
        SubTrip $leg
    ): void {
        $this->sendInvitationNotification(
            $guest,
            $host,
            $host,
            $request,
            $subject,
            'invitation_decline_from_guest',
            false,
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

    private function handleAcceptOfInvitation(
        ?Subtrip $leg,
        Member $host,
        Message $finalInvitation
    ): void {
        $leg->setInvitedBy($host);
        $this->entityManager->persist($leg);
        if (
            $leg->getArrival()->format('Y-m-d') != $finalInvitation->getRequest()->getArrival()->format('Y-m-d') ||
            $leg->getDeparture()->format('Y-m-d') != $finalInvitation->getRequest()->getDeparture()->format('Y-m-d')
        ) {
            $this->addTranslatedFlash('notice', 'trip.incomplete.leg', [
                'location' => $leg->getLocation()->getFullname(),
                'arrival' => $leg->getArrival(),
                'departure' => $leg->getDeparture(),
            ]);
            $this->logger->write('Invitation accepted (incomplete)', 'Trips');
        } else {
            $this->logger->write('Invitation accepted (complete)', 'Trips');
        }
    }

    private function handleDeclineOfInvitation(?Subtrip $leg, Member $host): void
    {
        if ($leg->getInvitedBy() === $host) {
            $leg->setInvitedBy(null);
        }
        $this->entityManager->persist($leg);
    }
}
