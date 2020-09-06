<?php

namespace App\Model\MemberDataExtractor;

use App\Entity\Member;
use App\Entity\Message;
use App\Repository\MessageRepository;

final class RequestsExtractor extends MessagesExtractor
{
    /**
     * {@inheritdoc}
     */
    public function extract(Member $member, string $tempDir): string
    {
        /** @var MessageRepository $messageRepository */
        $messageRepository = $this->getRepository(Message::class);

        $requestsSentBy = $messageRepository->getRequestsSentBy($member);
        $requestsReceivedBy = $messageRepository->getRequestsReceivedBy($member);
        $this->processMessagesOrRequests($requestsSentBy, $tempDir . 'requests', true);
        $this->processMessagesOrRequests($requestsReceivedBy, $tempDir . 'requests', false);

        return $this->writePersonalDataFile(
            [
                'requestsSent' => \count($requestsSentBy),
                'requestsReceived' => \count($requestsReceivedBy),
            ],
            'requests',
            $tempDir . 'requests.html'
        );
    }
}
