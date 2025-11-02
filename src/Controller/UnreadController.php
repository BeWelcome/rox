<?php

namespace App\Controller;

use App\Entity\Message;
use App\Entity\NewMember as Member;
use App\Repository\MessageRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

class UnreadController extends AbstractController
{
    #[Route(path: '/count/conversations/unread', name: 'count_conversations_unread')]
    public function getUnreadConversationsCount(EntityManagerInterface $entityManager): JsonResponse
    {
        /** @var Member $member */
        $member = $this->getUser();

        /** @var MessageRepository $messageRepository */
        $messageRepository = $entityManager->getRepository(Message::class);
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
