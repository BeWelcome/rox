<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Override;

/**
 * A private note can have several categories: note_category (App\Entity\ProfileNoteCategory), one row per note and
 * category, filled from the notes' single category (mycontacts.Category). A category with commas becomes one per
 * part: members listed several categories that way, and the note form now separates them with commas.
 *
 * mycontacts.Category stays: main reads it, and develop keeps the first of a note's categories in it.
 */
final class Version20261114110000 extends AbstractMigration
{
    #[Override]
    public function getDescription(): string
    {
        return 'Create note_category and fill it from the notes\' categories.';
    }

    public function up(Schema $schema): void
    {
        $this->connection->executeStatement('CREATE TABLE IF NOT EXISTS note_category (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, note_id INT NOT NULL, INDEX note_category_name_idx (name), UNIQUE INDEX note_category_unique (note_id, name), INDEX IDX_1617C55F26ED0855 (note_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $exists = $this->connection->fetchOne(
            "SELECT COUNT(*) FROM information_schema.referential_constraints
             WHERE constraint_schema = DATABASE() AND table_name = 'note_category' AND constraint_name = 'FK_1617C55F26ED0855'",
        );
        if (!$exists) {
            $this->connection->executeStatement('ALTER TABLE note_category ADD CONSTRAINT FK_1617C55F26ED0855 FOREIGN KEY (note_id) REFERENCES mycontacts (id) ON DELETE CASCADE');
        }

        $notes = $this->connection->iterateKeyValue(
            "SELECT n.id, n.Category FROM mycontacts n
             WHERE TRIM(n.Category) <> '' AND NOT EXISTS (SELECT 1 FROM note_category c WHERE c.note_id = n.id)",
        );
        $rows = [];
        foreach ($notes as $id => $category) {
            $names = [];
            foreach (explode(',', (string) $category) as $name) {
                $name = mb_substr(trim($name), 0, 255);
                if ('' !== $name) {
                    $names[mb_strtolower($name)] ??= $name;
                }
            }
            foreach ($names as $name) {
                $rows[] = [(int) $id, $name];
            }
            if (\count($rows) >= 500) {
                $this->insert($rows);
                $rows = [];
            }
        }
        $this->insert($rows);
    }

    #[Override]
    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE IF EXISTS note_category');
    }

    /**
     * @param list<array{int, string}> $rows note id and category name
     */
    private function insert(array $rows): void
    {
        if ([] === $rows) {
            return;
        }
        $this->connection->executeStatement(
            'INSERT IGNORE INTO note_category (note_id, name) VALUES ' . implode(', ', array_fill(0, \count($rows), '(?, ?)')),
            array_merge(...$rows),
        );
    }
}
