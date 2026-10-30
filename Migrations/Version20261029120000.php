<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Override;

/**
 * The forum and groups move to src:
 * - the BeWelcome group (id 0, App\Entity\Group::BEWELCOME); its forum is the agora, whose threads had no group;
 * - threads of groups that don't exist any more are deleted with their posts, then forum_thread.IdGroup is required
 *   and points at `group`;
 * - group categories (group_category, group_category_translation, group_category_group);
 * - a group's location (`group`.location_id, a geonames location);
 * - group_membership.welcome_notice_sent.
 *
 * All of these are develop's tables; main's forums_threads, forums_posts and groups stay untouched.
 */
final class Version20261029120000 extends AbstractMigration
{
    #[Override]
    public function getDescription(): string
    {
        return 'Add the BeWelcome group (id 0) for the agora, delete threads of deleted groups, add group categories, '
            . 'group locations and group_membership.welcome_notice_sent.';
    }

    #[Override]
    public function isTransactional(): bool
    {
        // CREATE and ALTER TABLE commit implicitly.
        return false;
    }

    public function up(Schema $schema): void
    {
        $this->connection->executeStatement('CREATE TABLE IF NOT EXISTS group_category (id INT AUTO_INCREMENT NOT NULL, created DATETIME NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->connection->executeStatement('CREATE TABLE IF NOT EXISTS group_category_translation (id INT AUTO_INCREMENT NOT NULL, locale VARCHAR(8) NOT NULL, field VARCHAR(32) NOT NULL, content LONGTEXT DEFAULT NULL, object_id INT DEFAULT NULL, UNIQUE INDEX lookup_unique_idx (locale, object_id, field), INDEX IDX_4D2EA6CA232D562B (object_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->connection->executeStatement('CREATE TABLE IF NOT EXISTS group_category_group (group_id INT NOT NULL, group_category_id INT NOT NULL, INDEX IDX_1D7EC2FEFE54D947 (group_id), INDEX IDX_1D7EC2FE37FE8223 (group_category_id), PRIMARY KEY (group_id, group_category_id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addForeignKey('group_category_translation', 'FK_4D2EA6CA232D562B', 'FOREIGN KEY (object_id) REFERENCES group_category (id) ON DELETE CASCADE');
        $this->addForeignKey('group_category_group', 'FK_1D7EC2FEFE54D947', 'FOREIGN KEY (group_id) REFERENCES `group` (id) ON DELETE CASCADE');
        $this->addForeignKey('group_category_group', 'FK_1D7EC2FE37FE8223', 'FOREIGN KEY (group_category_id) REFERENCES group_category (id) ON DELETE CASCADE');

        $this->connection->executeStatement('ALTER TABLE `group` ADD COLUMN IF NOT EXISTS location_id INT DEFAULT NULL');
        $this->connection->executeStatement('CREATE INDEX IF NOT EXISTS IDX_6DC044C564D218E ON `group` (location_id)');
        $this->addForeignKey('group', 'FK_6DC044C564D218E', 'FOREIGN KEY (location_id) REFERENCES location (geoname_id) ON DELETE SET NULL');

        $this->connection->executeStatement('ALTER TABLE group_membership ADD COLUMN IF NOT EXISTS welcome_notice_sent TINYINT DEFAULT 0 NOT NULL');

        $this->addBeWelcomeGroup();
        $this->deleteThreadsOfDeletedGroups();

        $this->connection->executeStatement('UPDATE forum_thread SET IdGroup = 0 WHERE IdGroup IS NULL');
        $this->connection->executeStatement('ALTER TABLE forum_thread MODIFY IdGroup INT NOT NULL');
        $this->addForeignKey('forum_thread', 'FK_298F7F52B5B93E44', 'FOREIGN KEY (IdGroup) REFERENCES `group` (id)');
    }

    #[Override]
    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE forum_thread DROP FOREIGN KEY IF EXISTS FK_298F7F52B5B93E44');
        $this->addSql('ALTER TABLE forum_thread MODIFY IdGroup INT DEFAULT NULL');
        $this->addSql('UPDATE forum_thread SET IdGroup = NULL WHERE IdGroup = 0');
        $this->addSql('DELETE FROM `group` WHERE id = 0');
        $this->addSql('ALTER TABLE group_membership DROP COLUMN IF EXISTS welcome_notice_sent');
        $this->addSql('ALTER TABLE `group` DROP FOREIGN KEY IF EXISTS FK_6DC044C564D218E');
        $this->addSql('ALTER TABLE `group` DROP COLUMN IF EXISTS location_id');
        $this->addSql('DROP TABLE IF EXISTS group_category_group');
        $this->addSql('DROP TABLE IF EXISTS group_category_translation');
        $this->addSql('DROP TABLE IF EXISTS group_category');
    }

    private function addBeWelcomeGroup(): void
    {
        // An id of 0 in an AUTO_INCREMENT column means "the next id", unless NO_AUTO_VALUE_ON_ZERO is set.
        $sqlMode = $this->connection->fetchOne('SELECT @@SESSION.sql_mode');
        $this->connection->executeStatement("SET SESSION sql_mode = CONCAT(@@SESSION.sql_mode, ',NO_AUTO_VALUE_ON_ZERO')");
        try {
            $this->connection->executeStatement(<<<'SQL'
                INSERT IGNORE INTO `group` (id, name, type, created, picture, members_only, approved)
                VALUES (0, 'BeWelcome', 'Public', NOW(), '', 0, 1)
                SQL);
        } finally {
            $this->connection->executeStatement('SET SESSION sql_mode = ?', [$sqlMode]);
        }
    }

    /**
     * Threads whose group doesn't exist any more go, with their posts, the posts' and threads' translations,
     * the queued notifications and the subscriptions.
     */
    private function deleteThreadsOfDeletedGroups(): void
    {
        $this->connection->executeStatement(<<<'SQL'
            CREATE TEMPORARY TABLE orphan_thread (id INT NOT NULL PRIMARY KEY)
            SELECT t.id FROM forum_thread t LEFT JOIN `group` g ON g.id = t.IdGroup
            WHERE t.IdGroup IS NOT NULL AND g.id IS NULL
            SQL);
        $this->connection->executeStatement(<<<'SQL'
            CREATE TEMPORARY TABLE orphan_post (id INT NOT NULL PRIMARY KEY)
            SELECT p.id FROM forum_post p JOIN orphan_thread o ON o.id = p.threadid
            SQL);

        $this->connection->executeStatement('SET FOREIGN_KEY_CHECKS = 0');
        try {
            if ($this->tableExists('posts_notificationqueue')) {
                $this->connection->executeStatement('DELETE n FROM posts_notificationqueue n JOIN orphan_post o ON o.id = n.IdPost');
            }
            if ($this->tableExists('members_threads_subscribed')) {
                $this->connection->executeStatement('DELETE s FROM members_threads_subscribed s JOIN orphan_thread o ON o.id = s.IdThread');
            }
            $this->connection->executeStatement('DELETE t FROM forum_post_translation t JOIN orphan_post o ON o.id = t.object_id');
            $this->connection->executeStatement('DELETE p FROM forum_post p JOIN orphan_post o ON o.id = p.id');
            $this->connection->executeStatement('DELETE t FROM forum_thread_translation t JOIN orphan_thread o ON o.id = t.object_id');
            $this->connection->executeStatement('DELETE t FROM forum_thread t JOIN orphan_thread o ON o.id = t.id');
        } finally {
            $this->connection->executeStatement('SET FOREIGN_KEY_CHECKS = 1');
            $this->connection->executeStatement('DROP TEMPORARY TABLE IF EXISTS orphan_post');
            $this->connection->executeStatement('DROP TEMPORARY TABLE IF EXISTS orphan_thread');
        }
    }

    private function tableExists(string $table): bool
    {
        return (bool) $this->connection->fetchOne(
            'SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = ?',
            [$table],
        );
    }

    private function addForeignKey(string $table, string $name, string $definition): void
    {
        $exists = $this->connection->fetchOne(
            'SELECT COUNT(*) FROM information_schema.referential_constraints
             WHERE constraint_schema = DATABASE() AND table_name = ? AND constraint_name = ?',
            [$table, $name],
        );
        if (!$exists) {
            $this->connection->executeStatement(\sprintf('ALTER TABLE `%s` ADD CONSTRAINT %s %s', $table, $name, $definition));
        }
    }
}
