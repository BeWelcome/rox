<?php

namespace App\Controller;

use App\Doctrine\AccommodationType;
use App\Entity\Member;
use App\Entity\Message;
use App\Form\HostingRequestGuest;
use App\Form\HostingRequestHost;
use App\Utilities\ManagerTrait;
use App\Utilities\TranslatorTrait;
use Exception;
use InvalidArgumentException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * Class HostingRequestController.
 *
 * Ignore complexity warning. \todo fix this.
 *
 * @SuppressWarnings(PHPMD.CyclomaticComplexity)
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 */
class HostingRequestController extends BaseHostingRequestAndInvitationController
{
    use ManagerTrait;
    use TranslatorTrait;

    /**
     * Deals with replies to messages and hosting requests.
     *
     * @Route("/request/{id}/reply", name="hosting_request_reply",
     *     requirements={"id": "\d+"})
     *
     * @throws AccessDeniedException
     */
    public function replyToHostingRequest(Message $message): Response
    {
        if (!$this->isMessageOfMember($message)) {
            throw $this->createAccessDeniedException('Not your message/hosting request');
        }

        if (!$this->isHostingRequest($message)) {
            return $this->redirectToMessageReply($message);
        }

        if ($this->isInvitation($message)) {
            return $this->redirectToRoute('invitation_reply', [
                'id' => $message->getId(),
                'leg' => $message->getRequest()->getInviteForLeg()->getId(),
            ]);
        }

        $thread = $this->messageModel->getThreadForMessage($message);
        $current = $thread[0];

        // Always reply to the last item in the thread
        if ($message->getId() !== $current->getId()) {
            return $this->redirectToRoute('hosting_request_reply', ['id' => $current->getId()]);
        }

        // determine if guest or host reply to a request
        $member = $this->getUser();
        $first = $thread[\count($thread) - 1];
        $parentId = ($message->getParent()) ? $message->getParent()->getId() : $message->getId();
        if ($member === $first->getSender()) {
            return $this->redirectToRoute('hosting_request_reply_guest', [
                'id' => $message->getId(),
                'parentId' => $parentId,
            ]);
        }

        return $this->redirectToRoute('hosting_request_reply_host', [
            'id' => $message->getId(),
            'parentId' => $parentId,
        ]);
    }

    /**
     * @Route("/request/{id}/reply/guest/{parentId}", name="hosting_request_reply_guest",
     *     requirements={"id": "\d+"})
     *
     * @ParamConverter("parent", class="App\Entity\Message", options={"id": "parentId"})
     *
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    public function hostingRequestGuestReply(Request $request, Message $hostingRequest, Message $parent): Response
    {
        if (!$this->isMessageOfMember($hostingRequest)) {
            throw $this->createAccessDeniedException('Not your message/hosting request');
        }

        /** @var Message $last */
        /** @var Member $guest */
        /** @var Member $host */
        list($thread, , $last, $guest, $host) =
            $this->messageModel->getThreadInformationForMessage($hostingRequest);

        if ($this->checkRequestExpired($last)) {
            $this->addExpiredFlash($host);

            return $this->redirectToRoute('hosting_request_show', ['id' => $last->getId()]);
        }

        // keep all information from current hosting request except the message text
        $hostingRequest = $this->getRequestClone($last);

        // A reply consists of a new message and maybe a change of the status of the hosting request
        // Additionally the user might change the dates of the request or cancel the request altogether
        /** @var Form $requestForm */
        $requestForm = $this->createForm(HostingRequestGuest::class, $hostingRequest);
        $requestForm->handleRequest($request);

        if ($requestForm->isSubmitted() && $requestForm->isValid()) {
            $realParent = $this->getParent($parent);

            $newRequest = $this->persistRequest($requestForm, $realParent, $guest, $host);

            $subject = $this->getSubjectForReply($newRequest);

            $this->sendGuestReplyNotification(
                $host,
                $guest,
                $newRequest,
                $subject,
                ($newRequest->getRequest()->getId() !== $realParent->getRequest()->getId())
            );
            $this->addTranslatedFlash('success', 'flash.notification.updated');

            return $this->redirectToRoute('hosting_request_show', ['id' => $newRequest->getId()]);
        }

        return $this->render('request/reply_from_guest.html.twig', [
            'guest' => $guest,
            'host' => $host,
            'form' => $requestForm->createView(),
            'thread' => $thread,
        ]);
    }

    /**
     * @Route("/request/{id}/reply/host/{parentId}", name="hosting_request_reply_host",
     *     requirements={"id": "\d+"})
     *
     * @ParamConverter("parent", class="App\Entity\Message", options={"id": "parentId"})
     *
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    public function hostingRequestHostReply(Request $request, Message $hostingRequest, Message $parent): Response
    {
        /** @var Message $last */
        /** @var Member $guest */
        /** @var Member $host */
        list($thread, , $last, $guest, $host) =
            $this->messageModel->getThreadInformationForMessage($hostingRequest);

        if ($this->checkRequestExpired($last)) {
            $this->addExpiredFlash($guest);

            return $this->redirectToRoute('hosting_request_show', ['id' => $last->getId()]);
        }

        // keep all information from current hosting request except the message text
        $hostingRequest = $this->getRequestClone($last);

        /** @var Form $requestForm */
        $requestForm = $this->createForm(HostingRequestHost::class, $hostingRequest);
        $requestForm->handleRequest($request);

        if ($requestForm->isSubmitted() && $requestForm->isValid()) {
            $realParent = $this->getParent($parent);

            $newRequest = $this->persistRequest($requestForm, $realParent, $host, $guest);

            $subject = $this->getSubjectForReply($newRequest);

            $this->sendHostReplyNotification(
                $host,
                $guest,
                $newRequest,
                $subject,
                ($newRequest->getRequest()->getId() !== $realParent->getRequest()->getId())
            );
            $this->addTranslatedFlash('notice', 'flash.notification.updated');

            return $this->redirectToRoute('hosting_request_show', ['id' => $newRequest->getId()]);
        }

        return $this->render('request/reply_from_host.html.twig', [
            'guest' => $guest,
            'host' => $host,
            'form' => $requestForm->createView(),
            'thread' => $thread,
        ]);
    }

    /**
     * @Route("/request/{id}", name="hosting_request_show",
     *     requirements={"id": "\d+"})
     *
     * @throws AccessDeniedException
     */
    public function show(Message $message): Response
    {
        return $this->showThread($message, 'request/view.html.twig', 'hosting_request_show');
    }

    /**
     * @Route("/request/{id}/deleted", name="hosting_request_show_with_deleted",
     *     requirements={"id": "\d+"})
     *
     * @throws AccessDeniedException
     */
    public function showDeleted(Message $message): Response
    {
        return $this->showThreadWithDeleted($message, 'request/view.html.twig', 'hosting_request_show_with_deleted');
    }

    /**
     * @Route("/new/request/{username}", name="hosting_request")
     *
     * @throws Exception
     */
    public function newHostingRequest(Request $request, Member $host): Response
    {
        /** @var Member $member */
        $member = $this->getUser();
        if ($member === $host) {
            $this->addTranslatedFlash('notice', 'flash.request.self');

            return $this->redirectToRoute('members_profile', ['username' => $member->getUsername()]);
        }

        if (!$host->isBrowseable()) {
            $this->addTranslatedFlash('note', 'flash.member.invalid');
        }

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

        if (AccommodationType::NO === $host->getAccommodation()) {
            $this->addTranslatedFlash('notice', 'request.not.hosting');

            return $this->redirectToRoute('members_profile', ['username' => $host->getUsername()]);
        }

        $requestForm = $this->createForm(HostingRequestGuest::class, null);
        $requestForm->handleRequest($request);

        if ($requestForm->isSubmitted() && $requestForm->isValid()) {
            // Write request to database after doing some checks
            $hostingRequest = $this->getMessageFromData($requestForm, $member, $host);

            $em = $this->getDoctrine()->getManager();
            $em->persist($hostingRequest);
            $em->flush();

            $this->sendInitialRequestNotification(
                $host,
                $member,
                $hostingRequest
            );
            $this->addTranslatedFlash('success', 'flash.request.sent');

            return $this->redirectToRoute('members_profile', ['username' => $host->getUsername()]);
        }

        return $this->render('request/request.html.twig', [
            'guest' => $member,
            'host' => $host,
            'form' => $requestForm->createView(),
        ]);
    }

    /**
     * @Route("/requests/{folder}", name="requests",
     *     defaults={"folder": "inbox"})
     *
     * @throws InvalidArgumentException
     */
    public function requests(Request $request, string $folder): Response
    {
        /** @var Member $member */
        $member = $this->getUser();
        list($page, $limit, $sort, $direction) = $this->getOptionsFromRequest($request);

        $requests = $this->messageModel->getFilteredRequests(
            $member,
            $folder,
            $sort,
            $direction,
            $page,
            $limit
        );

        return $this->handleFolderRequest($request, $folder, 'requests', $requests);
    }

    /**
     * @param mixed $subject
     * @param $requestChanged
     */
    protected function sendGuestReplyNotification(
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
            'reply_from_guest',
            $requestChanged
        );
    }

    private function sendInitialRequestNotification(Member $host, Member $guest, Message $request)
    {
        $subject = $request->getSubject()->getSubject();

        $this->messageModel->sendRequestNotification($host, $guest, $host, $request, $subject, 'request', false);
    }

    /**
     * @param mixed $subject
     * @param mixed $requestChanged
     */
    private function sendHostReplyNotification(
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
            'reply_from_host',
            $requestChanged
        );
    }
}
