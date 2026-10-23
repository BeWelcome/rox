<?php

namespace App\Processor\Fixtures;

use App\Entity\Message;
use DateTimeInterface;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Types\Types;
use Fidry\AliceDataFixtures\ProcessorInterface;

/**
 * Message::onPrePersist() sets created and dateSent to now. This keeps the created date
 * given in the fixture and writes it back once the message is stored, so that test data
 * has messages spread out over time.
 */
final class MessageProcessor implements ProcessorInterface
{
    /** @var array<string, DateTimeInterface> */
    private array $sentDates = [];

    public function __construct(
        private readonly Connection $connection,
    ) {
    }

    public function preProcess(string $id, $object): void
    {
        if (!$object instanceof Message) {
            return;
        }

        $this->sentDates[$id] = $object->getCreated();
    }

    public function postProcess(string $id, $object): void
    {
        if (!$object instanceof Message || !isset($this->sentDates[$id])) {
            return;
        }

        // Plain SQL so that Message::onPreUpdate() doesn't set updated.
        $this->connection->executeStatement(
            'UPDATE messages SET created = :sent, DateSent = :sent WHERE id = :id',
            ['sent' => $this->sentDates[$id], 'id' => $object->getId()],
            ['sent' => Types::DATETIME_MUTABLE],
        );

        unset($this->sentDates[$id]);
    }
}
