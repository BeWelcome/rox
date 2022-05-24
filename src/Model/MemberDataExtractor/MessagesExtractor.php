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
        $this->process($messagesSentBy, $tempDir . 'messages', 'message', true);
        $this->process($messagesReceivedBy, $tempDir . 'messages', 'message', false);

        return $this->writePersonalDataFile(
            [
                'messagesSent' => \count($messagesSentBy),
                'messagesReceived' => \count($messagesReceivedBy),
            ],
            'messages',
            $tempDir . 'messages.html'
        );
    }

    protected function process($items, $directory, $template, $sent)
    {
        $i = 1;
        foreach ($items as $message) {
            $templateName = $this->getTemplateName($message);
            $this->writePersonalDataFileSubDirectory(
                [
                    'message' => $message,
                ],
                $template,
                $directory,
                $templateName . '-' . $message->getCreated()->toDateString() . '-' . $i . ($sent ? '-sent' : '-received') . '.html'
            );
            ++$i;
        }
    }

    private function getTemplateName($message): string
    {
        if ($message->isMessage()) {
            return 'message';
        }
        if ($message->isHostingRequest()) {
            return 'request';
        }
        if ($message->isInvitation()) {
            return 'invitation';
        }

        return 'unknown';
    }
}
