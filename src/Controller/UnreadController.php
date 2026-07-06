<?php

namespace App\Controller;

use App\Entity\BrowserPushNotification;
use App\Entity\Member;
use App\Entity\Message;
use App\Repository\BrowserPushNotificationRepository;
use App\Repository\MessageRepository;
use App\Service\BrowserNotificationPayload;
use App\Service\BrowserPushPreferenceService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

class UnreadController extends AbstractController
{
    #[Route(path: '/count/conversations/unread', name: 'count_conversations_unread')]
    public function getUnreadConversationsCount(
        Request $request,
        EntityManagerInterface $entityManager,
        BrowserPushPreferenceService $browserPushPreferenceService,
        TranslatorInterface $translator,
    ): JsonResponse {
        /** @var Member $member */
        $member = $this->getUser();

        /** @var MessageRepository $messageRepository */
        $messageRepository = $entityManager->getRepository(Message::class);
        $unreadConversationCount = $messageRepository->getUnreadConversationsCount($member);

        $countWidget = $this->renderView('widgets/conversationcount.hml.twig', [
            'conversationCount' => $unreadConversationCount,
        ]);

        $response = new JsonResponse();
        $data = [
            'html' => $countWidget,
        ];
        if ($browserPushPreferenceService->isOpenOnly($member)) {
            $data['browserNotification'] = $this->getBrowserNotificationData(
                $request,
                $entityManager,
                $translator,
                $member
            );
        }

        $response->setData($data);

        return $response;
    }

    private function getBrowserNotificationData(
        Request $request,
        EntityManagerInterface $entityManager,
        TranslatorInterface $translator,
        Member $member,
    ): array {
        $sinceId = max(0, $request->query->getInt('browserNotificationSince'));
        /** @var BrowserPushNotificationRepository $notificationRepository */
        $notificationRepository = $entityManager->getRepository(BrowserPushNotification::class);
        $notifications = $notificationRepository->findOpenOnlyNotificationsSince($member, $sinceId);
        $latestId = $sinceId;
        $payloads = [];
        foreach ($notifications as $notification) {
            $latestId = max($latestId, (int) $notification->getId());
            $message = BrowserNotificationPayload::fromStored(
                $notification->getType(),
                $notification->getSenderUsername(),
                $notification->getUrl()
            )->toMessage($translator, $member->getLocale());
            $payloads[] = [
                'id' => $notification->getId(),
            ] + $message->toArray();
        }

        return [
            'memberId' => $member->getId(),
            'latestId' => $latestId,
            'notifications' => $payloads,
        ];
    }
}
