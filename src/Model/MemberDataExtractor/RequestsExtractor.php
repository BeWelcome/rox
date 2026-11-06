<?php

namespace App\Model\MemberDataExtractor;

use App\Entity\Member;
use App\Entity\Message;
use App\Repository\MessageRepository;
use Override;

final class RequestsExtractor extends MessagesExtractor
{
    #[Override]
    public function extract(Member $member, string $tempDir): array
    {
        /** @var MessageRepository $messageRepository */
        $messageRepository = $this->getRepository(Message::class);

        return $this->writeConversations(
            $member,
            $tempDir,
            'requests',
            $messageRepository->getRequestsSentBy($member),
            $messageRepository->getRequestsReceivedBy($member),
        );
    }
}
