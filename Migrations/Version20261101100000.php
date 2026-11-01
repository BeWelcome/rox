<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Override;

/**
 * word.format: how an English text is used on the page (App\Doctrine\TranslationFormat: html, inline, plain), so that
 * translations fit into their place. Empty for all texts: then it is detected from the English text.
 */
final class Version20261101100000 extends AbstractMigration
{
    #[Override]
    public function getDescription(): string
    {
        return 'Add word.format.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE word ADD format VARCHAR(6) DEFAULT NULL');
    }

    #[Override]
    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE word DROP format');
    }
}
