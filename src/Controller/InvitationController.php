<?php

namespace App\Controller;

use App\Entity\HostingRequest;
use App\Entity\Member;
use App\Entity\Message;
use App\Entity\Subtrip;
use App\Form\HostingRequestGuest;
use App\Form\InvitationGuest;
use App\Form\InvitationHost;
use DateTime;
use Exception;
use InvalidArgumentException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class InvitationController extends BaseHostingRequestAndInvitationController
{
    /**
     * @Route("/new/invitation/{leg}", name="hosting_invitation")
     *
     * @throws Exception
     */
    public function newInvitationT(Request $request, Subtrip $leg): Response
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
                $this->getParameter('new_members_messages_per_hour'),
                $this->getParameter('new_members_messages_per_day')
            )
        ) {
            $this->addTranslatedFlash('error', 'flash.request.limit');
            $referrer = $request->headers->get('referer');

            return $this->redirect($referrer);
        }

        $hostingRequest = new HostingRequest();
        $hostingRequest->setArrival($leg->getArrival());
        $hostingRequest->setDeparture($leg->getDeparture());
        $hostingRequest->setNumberOfTravellers($leg->getTrip()->getCountOfTravellers());

        $invitation = new Message();
        $invitation->setRequest($hostingRequest);

        $requestForm = $this->createForm(HostingRequestGuest::class, $invitation);
        $requestForm->handleRequest($request);

        if ($requestForm->isSubmitted() && $requestForm->isValid()) {
            // Write request to database after doing some checks
            /** @var Message $invitation */
            $invitation = $this->getMessageFromData($requestForm, $member, $guest);
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
            'guest' => $guest,
            'host' => $member,
            'form' => $requestForm->createView(),
        ]);
    }

    /**
     * @Route("/invitation/{id}/reply/{leg}", name="invitation_reply")
     *
     * @throws InvalidArgumentException
     */
    public function replyToInvitation(Message $message, Subtrip $leg): Response
    {
        if (!$this->isMessageOfMember($message)) {
            throw $this->createAccessDeniedException('Not your message/hosting request');
        }

        if (!$this->isInvitation($message)) {
            return $this->redirectToMessageReply($message);
        }

        $thread = $this->messageModel->getThreadForMessage($message);
        $current = $thread[0];

        // Always reply to the last item in the thread
        if ($message->getId() !== $current->getId()) {
            return $this->redirectToRoute('invitation_reply', [
                'id' => $current->getId(),
                'leg' => $leg->getId(),
            ]);
        }

        // determine if guest or host reply to a request
        $member = $this->getUser();
        $first = $thread[\count($thread) - 1];
        $parentId = ($message->getParent()) ? $message->getParent()->getId() : $message->getId();
        if ($member === $first->getSender()) {
            return $this->redirectToRoute('invitation_reply_host', [
                'id' => $message->getId(),
                'leg' => $leg->getId(),
                'parentId' => $parentId,
            ]);
        }

        return $this->redirectToRoute('invitation_reply_guest', [
            'id' => $message->getId(),
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
    public function invitationGuestReply(
        Request $request,
        Message $invitation,
        Subtrip $leg,
        Message $parent
    ): Response {
        if (!$this->isMessageOfMember($invitation)) {
            throw $this->createAccessDeniedException('Not your message/hosting request');
        }

        /** @var Message $last */
        /** @var Member $guest */
        /** @var Member $host */
        list($thread, , $last, $guest, $host) =
            $this->messageModel->getThreadInformationForMessage($invitation);

        if ($this->checkRequestExpired($last)) {
            $this->addExpiredFlash($host);

            return $this->redirectToRoute('hosting_request_show', ['id' => $last->getId()]);
        }

        // keep all information from current hosting request except the message text
        $invitation = $this->getRequestClone($last);

        // A reply consists of a new message and maybe a change of the status of the hosting request
        // Additionally the user might change the dates of the request or cancel the request altogether
        /** @var Form $requestForm */
        $requestForm = $this->createForm(InvitationGuest::class, $invitation);
        $requestForm->handleRequest($request);

        if ($requestForm->isSubmitted() && $requestForm->isValid()) {
            $realParent = $this->getParent($parent);

            $newRequest = $this->persistRequest($requestForm, $realParent, $guest, $host);

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
                ($newRequest->getRequest()->getId() !== $realParent->getRequest()->getId())
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
    public function invitationHostReply(
        Request $request,
        Message $invitation,
        Subtrip $leg,
        Message $parent
    ): Response {
        /** @var Message $last */
        /** @var Member $guest */
        /** @var Member $host */
        list($thread, , $last, $guest, $host) =
            $this->messageModel->getThreadInformationForMessage($invitation);

        if ($this->checkRequestExpired($last)) {
            $this->addExpiredFlash($guest);

            return $this->redirectToRoute('hosting_request_show', ['id' => $last->getId()]);
        }

        // keep all information from current hosting request except the message text
        $invitation = $this->getRequestClone($last);

        /** @var Form $requestForm */
        $requestForm = $this->createForm(InvitationHost::class, $invitation);
        $requestForm->handleRequest($request);

        if ($requestForm->isSubmitted() && $requestForm->isValid()) {
            $realParent = $this->getParent($parent);

            $newRequest = $this->persistRequest($requestForm, $realParent, $host, $guest);

            $subject = $this->getSubjectForReply($newRequest);

            $this->sendInvitationHostReplyNotification(
                $host,
                $guest,
                $newRequest,
                $subject,
                ($newRequest->getRequest()->getId() !== $realParent->getRequest()->getId())
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
     * @param mixed $subject
     * @param $requestChanged
     */
    protected function sendInvitationGuestReplyNotification(
        Member $host,
        Member $guest,
        Message $request,
        $subject,
        $requestChanged
    ): void {
        $this->messageModel->sendRequestNotification(
            $guest,
            $host,
            $host,
            $request,
            $subject,
            'invitation_reply_from_guest',
            $requestChanged
        );
    }

    private function sendInvitationNotification(Member $host, Member $guest, Message $request)
    {
        $subject = $request->getSubject()->getSubject();

        $this->messageModel->sendRequestNotification($guest, $host, $host, $request, $subject, 'invitation', false);
    }

    /**
     * @param mixed $subject
     * @param mixed $requestChanged
     */
    private function sendInvitationHostReplyNotification(
        Member $host,
        Member $guest,
        Message $request,
        $subject,
        $requestChanged
    ): void {
        $this->messageModel->sendRequestNotification(
            $host,
            $guest,
            $host,
            $request,
            $subject,
            'invitation_reply_from_host',
            $requestChanged
        );
    }
}
