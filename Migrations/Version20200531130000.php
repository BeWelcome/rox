<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Override;

final class Version20200531130000 extends AbstractMigration
{
    #[Override]
    public function getDescription(): string
    {
        return 'Bring geo__names_translations to the LocationTranslation mapping (Gedmo personal translation: '
            . 'object_id instead of object_class/foreign_key, unique locale/object_id/field, FK to '
            . 'geo__names.geoname_id). Keeps the first name per place, language and field (as the geonames '
            . 'import does) and drops names of places that are not in geo__names. Runs after '
            . 'Version20200531120000, which renames geo__names.geonameId to geoname_id.';
    }

    // The DDL statements commit implicitly, so a transaction around them can't be rolled back anyway.
    #[Override]
    public function isTransactional(): bool
    {
        return false;
    }

    public function up(Schema $schema): void
    {
        if ($this->tableExists('geo__names_translations') && !$this->columnExists('geo__names_translations', 'object_class')) {
            $this->write('geo__names_translations already has the new layout - nothing to do.');

            return;
        }

        // The table is rebuilt instead of altered: about 2M of its 4.8M rows are dropped and a copy of the rest
        // is faster than deleting them and rebuilding the table for the new indexes.
        $this->connection->executeStatement('DROP TABLE IF EXISTS geo__names_translations_new');
        $this->connection->executeStatement('CREATE TABLE geo__names_translations_new (id INT AUTO_INCREMENT NOT NULL, locale VARCHAR(8) NOT NULL, field VARCHAR(32) NOT NULL, content LONGTEXT DEFAULT NULL, object_id INT DEFAULT NULL, INDEX object_id (object_id), UNIQUE INDEX lookup_unique_idx (locale, object_id, field), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_520_ci` ENGINE = InnoDB');

        if ($this->tableExists('geo__names_translations')) {
            // INSERT IGNORE in id order keeps the first name of each place, language and field.
            $this->connection->executeStatement(
                'INSERT IGNORE INTO geo__names_translations_new (id, locale, field, content, object_id)
                 SELECT t.id, t.locale, t.field, t.content, t.foreign_key
                 FROM geo__names_translations t
                 JOIN geo__names g ON g.geoname_id = t.foreign_key
                 ORDER BY t.id'
            );
            $this->connection->executeStatement('RENAME TABLE geo__names_translations TO geo__names_translations_old, geo__names_translations_new TO geo__names_translations');
            $this->connection->executeStatement('DROP TABLE geo__names_translations_old');
        } else {
            $this->connection->executeStatement('RENAME TABLE geo__names_translations_new TO geo__names_translations');
        }

        // All object_ids exist in geo__names (see the join above), so the check can be skipped and MariaDB
        // adds the key without copying the table.
        $this->connection->executeStatement('SET FOREIGN_KEY_CHECKS=0');
        $this->connection->executeStatement('ALTER TABLE geo__names_translations ADD CONSTRAINT FK_DFA7286F232D562B FOREIGN KEY (object_id) REFERENCES geo__names (geoname_id) ON DELETE CASCADE, ALGORITHM=INPLACE, LOCK=NONE');
        $this->connection->executeStatement('SET FOREIGN_KEY_CHECKS=1');
    }

    #[Override]
    public function down(Schema $schema): void
    {
        $this->skipIf(
            !$this->tableExists('geo__names_translations') || $this->columnExists('geo__names_translations', 'object_class'),
            'geo__names_translations already has the old layout - nothing to revert.'
        );

        // The dropped duplicates and names of missing places don't come back.
        $this->connection->executeStatement('ALTER TABLE geo__names_translations
            DROP FOREIGN KEY FK_DFA7286F232D562B,
            DROP INDEX lookup_unique_idx,
            DROP INDEX object_id,
            CHANGE object_id foreign_key INT NOT NULL,
            ADD object_class VARCHAR(191) NOT NULL DEFAULT \'App\\\\Entity\\\\NewLocation\' AFTER locale,
            ADD INDEX geo__names_translation_idx (locale, object_class, field, foreign_key),
            ADD INDEX geo__names_idx (foreign_key)');
        $this->connection->executeStatement('ALTER TABLE geo__names_translations ALTER object_class DROP DEFAULT');
    }

    private function tableExists(string $tableName): bool
    {
        return (int) $this->connection->executeQuery(
            'SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = ?',
            [$tableName],
        )->fetchOne() > 0;
    }

    private function columnExists(string $tableName, string $columnName): bool
    {
        return (int) $this->connection->executeQuery(
            'SELECT COUNT(*) FROM information_schema.columns
             WHERE table_schema = DATABASE() AND table_name = ? AND column_name = ?',
            [$tableName, $columnName],
        )->fetchOne() > 0;
    }
}
