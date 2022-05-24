<?php

namespace App\Model\MemberDataExtractor;

use App\Entity\Member;
use App\Entity\Message;
use App\Repository\MessageRepository;

final class InvitationsExtractor extends MessagesExtractor
{
    /**
     * {@inheritdoc}
     */
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
