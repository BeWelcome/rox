<?php

namespace App\Service;

use App\Entity\Message;
use Manticoresearch\Client;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Throwable;

/**
 * Keeps a message's document in the message search index (message_rt, see ManticoreIndicesMessagesCommand) up to
 * date. Messages work without the search: failures are logged, not thrown.
 */
class MessageSearchIndex
{
    public const string INDEX = 'message_rt';

    public function __construct(
        #[Autowire('%manticore.host%')] private readonly string $host,
        #[Autowire('%manticore.port%')] private readonly int $port,
        private readonly LoggerInterface $logger,
    ) {
    }

    /**
     * The document of a message, as the index command builds it: the subject and the text (the index strips the
     * HTML), the conversation (subject) and the two members.
     *
     * @return array{subject_id: int, sender: int, receiver: int, content: string}
     */
    public static function document(?int $subjectId, int $senderId, int $receiverId, ?string $subject, ?string $text): array
    {
        return [
            'subject_id' => (int) $subjectId,
            'sender' => $senderId,
            'receiver' => $receiverId,
            'content' => trim(($subject ?? '') . "\n" . ($text ?? '')),
        ];
    }

    public function index(Message $message): void
    {
        $subject = $message->getSubject();
        $document = self::document(
            $subject?->getId(),
            $message->getSender()->getId(),
            $message->getReceiver()->getId(),
            $subject?->getSubject(),
            $message->getMessage(),
        );

        try {
            $client = new Client(['host' => $this->host, 'port' => $this->port]);
            $client->table(self::INDEX)->replaceDocument($document, $message->getId());
        } catch (Throwable $throwable) {
            $this->logger->warning('Message #{message} could not be indexed: {error}', [
                'message' => $message->getId(),
                'error' => $throwable->getMessage(),
            ]);
        }
    }
}
