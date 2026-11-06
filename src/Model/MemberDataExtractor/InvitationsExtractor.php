<?php

namespace App\Model\MemberDataExtractor;

use App\Entity\Member;
use App\Entity\Message;
use App\Repository\MessageRepository;
use Override;

final class InvitationsExtractor extends MessagesExtractor
{
    #[Override]
    public function extract(Member $member, string $tempDir): array
    {
        /** @var MessageRepository $messageRepository */
        $messageRepository = $this->getRepository(Message::class);

        return $this->writeConversations(
            $member,
            $tempDir,
            'invitations',
            $messageRepository->getInvitationsSentBy($member),
            $messageRepository->getInvitationsReceivedBy($member),
        );
    }
}
