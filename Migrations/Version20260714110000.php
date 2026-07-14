<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Override;

final class Version20260714110000 extends AbstractMigration
{
    #[Override]
    public function getDescription(): string
    {
        return 'Store forum notification message IDs for RFC-compliant email threading';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE posts_notificationqueue ADD COLUMN IF NOT EXISTS MessageId VARCHAR(255) DEFAULT NULL, ADD UNIQUE INDEX IF NOT EXISTS posts_notificationqueue_message_id_unique (MessageId)');
    }

    #[Override]
    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE posts_notificationqueue DROP INDEX posts_notificationqueue_message_id_unique, DROP MessageId');
    }
}
