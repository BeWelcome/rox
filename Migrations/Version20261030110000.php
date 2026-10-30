<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Override;

/**
 * App\Entity\Flag maps flags.updated (LifecycleCallbacksTrait), which the table never had: loading a flag failed.
 */
final class Version20261030110000 extends AbstractMigration
{
    #[Override]
    public function getDescription(): string
    {
        return 'Add flags.updated.';
    }

    #[Override]
    public function isTransactional(): bool
    {
        // ALTER TABLE commits implicitly.
        return false;
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE flags ADD COLUMN IF NOT EXISTS updated DATETIME DEFAULT NULL');
    }

    #[Override]
    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE flags DROP COLUMN IF EXISTS updated');
    }
}
