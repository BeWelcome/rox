<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Override;

final class Version20260624115900 extends AbstractMigration
{
    #[Override]
    public function getDescription(): string
    {
        return 'Drop preferences.EvalString - legacy eval-string markup for the old PHP frontend\'s '
            . 'preference widgets. The Preference entity no longer maps this column, but it was never '
            . 'actually migrated away, so it remained a NOT NULL column with no default on databases '
            . 'that still had it, breaking plain INSERTs into preferences (e.g. Version20260624120000).';
    }

    public function up(Schema $schema): void
    {
        if ($this->columnExists('preferences', 'EvalString')) {
            $this->addSql('ALTER TABLE preferences DROP COLUMN EvalString');
        }
    }

    #[Override]
    public function down(Schema $schema): void
    {
        if (!$this->columnExists('preferences', 'EvalString')) {
            $this->addSql("ALTER TABLE preferences ADD COLUMN EvalString MEDIUMTEXT NOT NULL DEFAULT ''");
        }
    }

    private function columnExists(string $tableName, string $columnName): bool
    {
        $count = $this->connection->executeQuery(
            'SELECT COUNT(*) FROM information_schema.columns
             WHERE table_schema = DATABASE() AND table_name = ? AND column_name = ?',
            [$tableName, $columnName],
        )->fetchOne();

        return (int) $count > 0;
    }
}
