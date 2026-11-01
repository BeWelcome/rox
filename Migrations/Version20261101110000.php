<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Override;

/**
 * word.previous_sentence and word.sentence_changed: an English text keeps the text it had before its last change and
 * when that was, so that translators see what changed (App\Entity\Word::setSentence()).
 */
final class Version20261101110000 extends AbstractMigration
{
    #[Override]
    public function getDescription(): string
    {
        return 'Add word.previous_sentence and word.sentence_changed.';
    }

    #[Override]
    public function isTransactional(): bool
    {
        // ALTER TABLE commits implicitly.
        return false;
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE word ADD COLUMN IF NOT EXISTS previous_sentence TEXT DEFAULT NULL, ADD COLUMN IF NOT EXISTS sentence_changed DATETIME DEFAULT NULL');
    }

    #[Override]
    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE word DROP COLUMN IF EXISTS previous_sentence, DROP COLUMN IF EXISTS sentence_changed');
    }
}
