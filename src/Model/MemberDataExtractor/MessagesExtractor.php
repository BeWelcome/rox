<?php

namespace App\Model\MemberDataExtractor;

use App\Entity\Member;
use App\Entity\Message;
use App\Repository\MessageRepository;

class MessagesExtractor extends AbstractExtractor implements ExtractorInterface
{
    /**
     * {@inheritdoc}
     */
    public function extract(Member $member, string $tempDir): string
    {
        /** @var MessageRepository $messageRepository */
        $messageRepository = $this->getRepository(Message::class);

        $messagesSentBy = $messageRepository->getMessagesSentBy($member);
        $messagesReceivedBy = $messageRepository->getMessagesReceivedBy($member);
        $this->processMessagesOrRequests($messagesSentBy, $tempDir . 'messages', true);
        $this->processMessagesOrRequests($messagesReceivedBy, $tempDir . 'messages', false);

        return $this->writePersonalDataFile(
            [
                'messagesSent' => \count($messagesSentBy),
                'messagesReceived' => \count($messagesReceivedBy),
            ],
            'messages'
        );
    }

    protected function processMessagesOrRequests($items, $directory, $sent)
    {
        $i = 1;
        foreach ($items as $message) {
            $isRequest = (null !== $message->getRequest());
            $filename = ($isRequest) ? 'request' : 'message';
            $this->writePersonalDataFileSubDirectory(
                [
                    'message' => $message,
                ],
                'message_or_request',
                $directory,
                $filename . '-' . $message->getCreated()->toDateString() . '-' . $i . ($sent ? '-sent' : '-received')
            );
            ++$i;
        }
    }
}
