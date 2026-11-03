<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Override;

/**
 * group_category.icon and group_category.keywords: an icon for the category's tiles and pills, and the words that make
 * it a suggestion for a group (App\Entity\GroupCategory). Group 0, the agora, is named "BeWelcome Agora".
 * members_threads_subscribed.last_read: when the subscriber last opened the thread, for the unread ones in the
 * subscriptions (main's table: a new nullable column, which main doesn't write).
 */
final class Version20261103100000 extends AbstractMigration
{
    #[Override]
    public function getDescription(): string
    {
        return 'Add group_category.icon, group_category.keywords and members_threads_subscribed.last_read; name group 0 "BeWelcome Agora".';
    }

    #[Override]
    public function isTransactional(): bool
    {
        // ALTER TABLE commits implicitly.
        return false;
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE group_category ADD COLUMN IF NOT EXISTS icon VARCHAR(40) DEFAULT NULL, ADD COLUMN IF NOT EXISTS keywords JSON DEFAULT NULL');
        $this->addSql("UPDATE `group` SET name = 'BeWelcome Agora' WHERE id = 0 AND name = 'BeWelcome'");
        $this->addSql('ALTER TABLE members_threads_subscribed ADD COLUMN IF NOT EXISTS last_read DATETIME DEFAULT NULL');
    }

    #[Override]
    public function down(Schema $schema): void
    {
        $this->addSql("UPDATE `group` SET name = 'BeWelcome' WHERE id = 0 AND name = 'BeWelcome Agora'");
        $this->addSql('ALTER TABLE group_category DROP COLUMN IF EXISTS icon, DROP COLUMN IF EXISTS keywords');
        $this->addSql('ALTER TABLE members_threads_subscribed DROP COLUMN IF EXISTS last_read');
    }
}
