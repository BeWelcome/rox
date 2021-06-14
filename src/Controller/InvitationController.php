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
use App\Model\HostingRequestModel;
use App\Model\InvitationModel;
use App\Model\MessageModel;
use App\Utilities\TranslatedFlashTrait;
use App\Utilities\TranslatorTrait;
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

class InvitationController extends BaseHostingRequestAndInvitationController
{
    use TranslatedFlashTrait;
    use TranslatorTrait;

    private InvitationModel $invitationModel;

    public function __construct(
        MessageModel $messageModel,
        HostingRequestModel $requestModel,
        InvitationModel $invitationModel
    ) {
        parent::__construct($requestModel, $messageModel);

        $this->invitationModel = $invitationModel;
    }

    /**
     * @Route("/new/invitation/{leg}", name="hosting_invitation")
     *
     * @throws Exception
     */
    public function newInvitation(Request $request, Subtrip $leg, TranslatorInterface $translator): Response
    {
        /** @var Member $member */
        $member = $this->getUser();
        $guest = $leg->getTrip()->getCreator();
        if ($member === $guest) {
            $this->addTranslatedFlash('notice', 'flash.request.invitation.self');

            return $this->redirectToRoute('members_profile', ['username' => $member->getUsername()]);
        }

        if (!$guest->isBrowseable()) {
            $this->addTranslatedFlash('note', 'flash.member.invalid');
        }

        // \todo Decide if this should be removed in this case
        if (
            $this->messageModel->hasRequestLimitExceeded(
                $member,
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
            $invitation = $this->getMessageFromData($invitationForm->getData(), $guest, $member);
            $invitation->getRequest()->setInviteForLeg($leg);

            $em = $this->getDoctrine()->getManager();
            $em->persist($invitation);
            $em->flush();

            $this->sendInvitationNotification(
                $member,
                $guest,
                $invitation
            );
            $this->addTranslatedFlash('success', 'flash.request.invitation.sent');

            return $this->redirectToRoute('members_profile', ['username' => $guest->getUsername()]);
        }

        return $this->render('invitation/invite.html.twig', [
            'leg' => $leg,
            'subject' => $subjectText,
            'form' => $invitationForm->createView(),
        ]);
    }

    /**
     * @Route("/invitation/{id}/reply/{leg}", name="invitation_reply")
     *
     * @throws InvalidArgumentException
     */
    public function replyToInvitation(Message $invitation, Subtrip $leg): Response
    {
        if (!$this->isMessageOfMember($invitation)) {
            throw $this->createAccessDeniedException('Not your message/hosting request');
        }

        if ($this->needsRedirect($invitation, self::INVITATION)) {
            return $this->redirectReplyTo($invitation);
        }

        $thread = $this->messageModel->getThreadForMessage($invitation);
        $current = $thread[0];

        // Always reply to the last item in the thread
        if ($invitation->getId() !== $current->getId()) {
            return $this->redirectToRoute('invitation_reply', [
                'id' => $current->getId(),
                'leg' => $leg->getId(),
            ]);
        }

        // determine if guest or host reply to a request
        $member = $this->getUser();
        $parentId = ($invitation->getParent()) ? $invitation->getParent()->getId() : $invitation->getId();
        if ($member === $invitation->getInitiator()) {
            return $this->redirectToRoute('invitation_reply_host', [
                'id' => $invitation->getId(),
                'leg' => $leg->getId(),
                'parentId' => $parentId,
            ]);
        }

        return $this->redirectToRoute('invitation_reply_guest', [
            'id' => $invitation->getId(),
            'leg' => $leg->getId(),
            'parentId' => $parentId,
        ]);
    }

    /**
     * @Route("/invitation/{id}/reply/{leg}/guest/{parentId}", name="invitation_reply_guest",
     *     requirements={"id": "\d+"})
     *
     * @ParamConverter("parent", class="App\Entity\Message", options={"id": "parentId"})
     *
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    public function guestReplyToInvitation(
        Request $request,
        Message $invitation,
        Subtrip $leg,
        Message $parent
    ): Response {
        if (!$this->isMessageOfMember($invitation)) {
            throw $this->createAccessDeniedException('Not your message/hosting request');
        }

        $host = $invitation->getInitiator();
        $guest = ($host === $invitation->getSender()) ? $invitation->getReceiver() : $invitation->getSender();
        list($thread) =
            $this->messageModel->getThreadInformationForMessage($invitation);

        if ($this->checkInvitationExpired($invitation)) {
            $this->addExpiredFlash($host);

            return $this->redirectToRoute('hosting_request_show', ['id' => $invitation->getId()]);
        }

        // keep all information from current hosting request except the message text
        $invitation = $this->getRequestClone($invitation);

        // A reply consists of a new message and maybe a change of the status of the hosting request
        // Additionally the user might change the dates of the request or cancel the request altogether
        /** @var Form $requestForm */
        $requestForm = $this->createForm(InvitationGuest::class, $invitation);
        $requestForm->handleRequest($request);

        if ($requestForm->isSubmitted() && $requestForm->isValid()) {
            $newRequest = $this->persistRequest($requestForm, $parent, $guest, $host);

            // In case the potential guest declines the invitation remove the invitedBy from the leg
            if (HostingRequest::REQUEST_ACCEPTED === $newRequest->getRequest()->getStatus()) {
                $em = $this->getDoctrine()->getManager();

                $leg->setInvitedBy($host);
                $em->persist($leg);
                $em->flush();
            }

            $subject = $this->getSubjectForReply($newRequest);

            $this->sendInvitationGuestReplyNotification(
                $host,
                $guest,
                $newRequest,
                $subject,
                ($newRequest->getRequest()->getId() !== $parent->getRequest()->getId()),
                $leg
            );
            $this->addTranslatedFlash('success', 'flash.notification.updated');

            return $this->redirectToRoute('hosting_request_show', ['id' => $newRequest->getId()]);
        }

        return $this->render('invitation/reply_from_guest.html.twig', [
            'guest' => $guest,
            'host' => $host,
            'form' => $requestForm->createView(),
            'thread' => $thread,
        ]);
    }

    /**
     * @Route("/invitation/{id}/reply/{leg}/host/{parentId}", name="invitation_reply_host",
     *     requirements={"id": "\d+"})
     *
     * @ParamConverter("parent", class="App\Entity\Message", options={"id": "parentId"})
     *
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    public function hostReplyToInvitation(
        Request $request,
        Message $invitation,
        Subtrip $leg,
        Message $parent
    ): Response {
        $host = $invitation->getInitiator();

        $guest = ($host === $invitation->getSender()) ? $invitation->getReceiver() : $invitation->getSender();
        list($thread, , $last) = $this->messageModel->getThreadInformationForMessage($invitation);

        if ($this->checkRequestExpired($invitation)) {
            $this->addExpiredFlash($guest);

            return $this->redirectToRoute('hosting_request_show', ['id' => $last->getId()]);
        }

        // keep all information from current hosting request except the message text
        $invitation = $this->getRequestClone($invitation);

        /** @var Form $requestForm */
        $requestForm = $this->createForm(InvitationHost::class, $invitation);
        $requestForm->handleRequest($request);

        if ($requestForm->isSubmitted() && $requestForm->isValid()) {
            $realParent = $this->getParent($parent);

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

            return $this->redirectToRoute('hosting_request_show', ['id' => $newRequest->getId()]);
        }

        return $this->render('invitation/reply_from_host.html.twig', [
            'guest' => $guest,
            'host' => $host,
            'form' => $requestForm->createView(),
            'thread' => $thread,
        ]);
    }

    /**
     * @Route("/invitation/{id}", name="invitation_show",
     *     requirements={"id": "\d+"})
     *
     * @throws AccessDeniedException
     */
    public function show(Message $message): Response
    {
        if ($this->needsRedirect($message, self::INVITATION)) {
            return $this->redirectShow($message, false);
        }

        return $this->showThread($message, 'invitation/view.html.twig', 'invitation_show', false);
    }

    /**
     * @Route("/invitation/{id}/deleted", name="invitation_show_with_deleted",
     *     requirements={"id": "\d+"})
     *
     * @throws AccessDeniedException
     */
    public function showDeleted(Message $message): Response
    {
        if ($this->needsRedirect($message, self::INVITATION)) {
            return $this->redirectShow($message, true);
        }

        return $this->showThread($message, 'invitation/view.html.twig', 'invitation_show', true);
    }

    protected function checkInvitationExpired(Message $hostingRequest): bool
    {
        return $this->invitationModel->isInvitationExpired($hostingRequest->getRequest());
    }

    protected function sendInvitationGuestReplyNotification(
        Member $host,
        Member $guest,
        Message $request,
        string $subject,
        bool $requestChanged,
        SubTrip $leg
    ): void {
        $this->invitationModel->sendInvitationNotification(
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

    private function sendInvitationNotification(Member $host, Member $guest, Message $request)
    {
        $subject = $request->getSubject()->getSubject();

        $this->invitationModel->sendInvitationNotification(
            $host,
            $guest,
            $host,
            $request,
            $subject,
            'invitation',
            false,
            null
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
        $this->invitationModel->sendInvitationNotification(
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
}
