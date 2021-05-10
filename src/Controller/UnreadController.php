<?php

namespace App\Controller;

use App\Entity\Member;
use App\Entity\Message;
use App\Repository\MessageRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class UnreadController extends AbstractController
{
    /**
     * @Route("/count/conversations/unread", name="count_conversations_unread")
     */
    public function getUnreadConversationsCount(): JsonResponse
    {
        /** @var Member $member */
        $member = $this->getUser();

        /** @var MessageRepository $messageRepository */
        $messageRepository = $this->getDoctrine()->getRepository(Message::class);
        $unreadConversationCount = $messageRepository->getUnreadConversationsCount($member);

        $countWidget = $this->renderView('widgets/conversationcount.hml.twig', [
            'conversationCount' => $unreadConversationCount,
        ]);

        $response = new JsonResponse();
        $response->setData([
            'html' => $countWidget,
        ]);

        return $response;
    }
}
