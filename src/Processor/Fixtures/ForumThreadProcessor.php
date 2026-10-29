<?php

namespace App\Processor\Fixtures;

use App\Entity\ForumThread;
use App\Entity\Group;
use Doctrine\ORM\EntityManagerInterface;
use Fidry\AliceDataFixtures\ProcessorInterface;

/**
 * Threads without a group in the fixtures are agora threads: they go into the BeWelcome group (id 0). That group
 * can't come from a fixture (an id of 0 means "the next id" to MariaDB), so it's inserted here.
 */
final readonly class ForumThreadProcessor implements ProcessorInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function preProcess(string $id, $object): void
    {
        if (!$object instanceof ForumThread || null !== $object->getGroup()) {
            return;
        }

        $this->addBeWelcomeGroup();
        $object->setGroup($this->entityManager->getReference(Group::class, Group::BEWELCOME));
    }

    public function postProcess(string $id, $object): void
    {
    }

    private function addBeWelcomeGroup(): void
    {
        $connection = $this->entityManager->getConnection();
        $sqlMode = $connection->fetchOne('SELECT @@SESSION.sql_mode');
        $connection->executeStatement("SET SESSION sql_mode = CONCAT(@@SESSION.sql_mode, ',NO_AUTO_VALUE_ON_ZERO')");
        try {
            $connection->executeStatement(
                'INSERT IGNORE INTO `group` (id, name, type, created, picture, members_only, approved)'
                . " VALUES (0, 'BeWelcome', 'Public', NOW(), '', 0, 1)"
            );
        } finally {
            $connection->executeStatement('SET SESSION sql_mode = ?', [$sqlMode]);
        }
    }
}
