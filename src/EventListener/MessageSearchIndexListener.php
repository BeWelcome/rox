<?php

namespace App\EventListener;

use App\Entity\Message;
use App\Service\MessageSearchIndex;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Doctrine\ORM\Events;

/**
 * Every new message goes into the message search index, wherever it was written (messages, requests, invitations,
 * replies).
 */
#[AsEntityListener(event: Events::postPersist, method: 'postPersist', entity: Message::class)]
class MessageSearchIndexListener
{
    public function __construct(private readonly MessageSearchIndex $searchIndex)
    {
    }

    public function postPersist(Message $message): void
    {
        $this->searchIndex->index($message);
    }
}
