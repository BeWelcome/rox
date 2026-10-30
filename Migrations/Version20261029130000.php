<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Override;

/**
 * Deletes the forum posts whose thread doesn't exist, with their translations and
 * queued notifications. Only develop's forum_post; main's forums_posts stays.
 */
final class Version20261029130000 extends AbstractMigration
{
    #[Override]
    public function getDescription(): string
    {
        return 'Delete forum posts without a thread.';
    }

    #[Override]
    public function isTransactional(): bool
    {
        // the temporary table commits implicitly
        return false;
    }

    public function up(Schema $schema): void
    {
        $this->connection->executeStatement(<<<'SQL'
            CREATE TEMPORARY TABLE orphan_post (id INT NOT NULL PRIMARY KEY)
            SELECT p.id FROM forum_post p LEFT JOIN forum_thread t ON t.id = p.threadid WHERE t.id IS NULL
            SQL);
        $this->connection->executeStatement('SET FOREIGN_KEY_CHECKS = 0');
        try {
            if ($this->tableExists('posts_notificationqueue')) {
                $this->connection->executeStatement('DELETE n FROM posts_notificationqueue n JOIN orphan_post o ON o.id = n.IdPost');
            }
            $this->connection->executeStatement('DELETE t FROM forum_post_translation t JOIN orphan_post o ON o.id = t.object_id');
            $this->connection->executeStatement('DELETE p FROM forum_post p JOIN orphan_post o ON o.id = p.id');
        } finally {
            $this->connection->executeStatement('SET FOREIGN_KEY_CHECKS = 1');
            $this->connection->executeStatement('DROP TEMPORARY TABLE IF EXISTS orphan_post');
        }
    }

    #[Override]
    public function down(Schema $schema): void
    {
        // the posts are gone
    }

    private function tableExists(string $table): bool
    {
        return (bool) $this->connection->fetchOne(
            'SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = ?',
            [$table],
        );
    }
}
