<?php

namespace App\Controller;

use App\Entity\Member;
use App\Entity\Message;
use App\Repository\MessageRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class UnreadController extends AbstractController
{
    /**
     * @Route("/count/messages/unread", name="count_messages_unread")
     *
     * @return JsonResponse
     */
    public function getUnreadMessagesCount(Request $request)
    {
        /** @var Member $member */
        $member = $this->getUser();

        /** @var MessageRepository $messageRepository */
        $messageRepository = $this->getDoctrine()->getRepository(Message::class);
        $unreadMessageCount = $messageRepository->getUnreadMessagesCount($member);

        $countWidget = $this->renderView('widgets/messagescount.hml.twig', [
            'messageCount' => $unreadMessageCount,
        ]);

        $response = new JsonResponse();
        $response->setData([
            'html' => $countWidget,
        ]);

        return $response;
    }

    /**
     * @Route("/count/requests/unread", name="count_requests_unread")
     *
     * @return JsonResponse
     */
    public function getUnreadRequestsCount(Request $request)
    {
        /** @var Member $member */
        $member = $this->getUser();

        /** @var MessageRepository $messageRepository */
        $messageRepository = $this->getDoctrine()->getRepository(Message::class);
        $unreadRequestsCount = $messageRepository->getUnreadRequestsCount($member);

        $countWidget = $this->renderView('widgets/requestscount.html.twig', [
            'requestCount' => $unreadRequestsCount,
        ]);

        $response = new JsonResponse();
        $response->setData([
            'html' => $countWidget,
        ]);

        return $response;
    }

    /**
     * @Route("/count/invitations/unread", name="count_invitations_unread")
     *
     * @return JsonResponse
     */
    public function getUnreadInvitationsCount(Request $request)
    {
        /** @var Member $member */
        $member = $this->getUser();

        /** @var MessageRepository $messageRepository */
        $messageRepository = $this->getDoctrine()->getRepository(Message::class);
        $unreadInvitationsCount = $messageRepository->getUnreadInvitationsCount($member);

        $countWidget = $this->renderView('widgets/invitationscount.html.twig', [
            'invitationCount' => $unreadInvitationsCount,
        ]);

        $response = new JsonResponse();
        $response->setData([
            'html' => $countWidget,
        ]);

        return $response;
    }
}
