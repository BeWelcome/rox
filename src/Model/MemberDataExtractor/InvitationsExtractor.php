<?php

namespace App\Model\MemberDataExtractor;

use App\Entity\Message;
use App\Entity\NewMember as Member;
use App\Repository\MessageRepository;
use Override;

final class InvitationsExtractor extends MessagesExtractor
{
    #[Override]
    public function extract(Member $member, string $tempDir): string
    {
        /** @var MessageRepository $messageRepository */
        $messageRepository = $this->getRepository(Message::class);

        $invitationsSentBy = $messageRepository->getInvitationsSentBy($member);
        $invitationsReceivedBy = $messageRepository->getInvitationsReceivedBy($member);
        $this->process($invitationsSentBy, $tempDir . 'invitations', 'invitation', true);
        $this->process($invitationsReceivedBy, $tempDir . 'invitations', 'invitation', false);

        return $this->writePersonalDataFile(
            [
                'invitationsSent' => \count($invitationsSentBy),
                'invitationsReceived' => \count($invitationsReceivedBy),
            ],
            'invitations',
            $tempDir . 'invitations.html'
        );
    }
}
