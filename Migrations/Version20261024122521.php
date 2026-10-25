<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Override;

final class Version20261024122521 extends AbstractMigration
{
    private const array FOREIGN_KEYS = [
        'FK_79EAAC25FE54D947' => 'FOREIGN KEY (group_id) REFERENCES groups (id) ON DELETE CASCADE',
        'FK_79EAAC254162C001' => 'FOREIGN KEY (related_id) REFERENCES groups (id) ON DELETE CASCADE',
        'FK_79EAAC25724EA09E' => 'FOREIGN KEY (addedby) REFERENCES member (id) ON DELETE SET NULL',
        'FK_79EAAC255AC430DE' => 'FOREIGN KEY (deletedby) REFERENCES member (id) ON DELETE CASCADE',
    ];

    #[Override]
    public function getDescription(): string
    {
        return 'Map groups_related (related groups) with the RelatedGroup entity. Creates the table on new '
            . 'databases; on databases migrated from production (where build/relatedgroups created it by '
            . 'hand) it removes rows pointing at deleted groups or members and adds the keys of the entity.';
    }

    public function up(Schema $schema): void
    {
        if (!$this->tableExists('groups_related')) {
            $this->addSql('CREATE TABLE groups_related (id INT AUTO_INCREMENT NOT NULL, ts DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, group_id INT NOT NULL, related_id INT NOT NULL, addedby INT DEFAULT NULL, deletedby INT DEFAULT NULL, INDEX IDX_79EAAC25FE54D947 (group_id), INDEX IDX_79EAAC254162C001 (related_id), INDEX IDX_79EAAC25724EA09E (addedby), INDEX IDX_79EAAC255AC430DE (deletedby), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
            $this->addForeignKeys();

            return;
        }

        // The hand-made table allows NULLs and has no keys. Group::delete() never removed its rows,
        // so drop links that point at missing groups, forget missing authors and drop removals
        // done by missing members (the link was removed, the row is just its log entry).
        $this->addSql('DELETE FROM groups_related WHERE group_id IS NULL OR related_id IS NULL
            OR group_id NOT IN (SELECT id FROM `groups`) OR related_id NOT IN (SELECT id FROM `groups`)');
        $this->addSql('UPDATE groups_related SET addedby = NULL WHERE addedby IS NOT NULL AND addedby NOT IN (SELECT id FROM member)');
        $this->addSql('DELETE FROM groups_related WHERE deletedby IS NOT NULL AND deletedby NOT IN (SELECT id FROM member)');

        // TIMESTAMP ON UPDATE becomes DATETIME: the entity sets ts itself when a link is removed.
        $this->addSql('ALTER TABLE groups_related
            MODIFY ts DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL,
            MODIFY group_id INT NOT NULL,
            MODIFY related_id INT NOT NULL,
            MODIFY addedby INT DEFAULT NULL,
            MODIFY deletedby INT DEFAULT NULL');
        foreach (['IDX_79EAAC25FE54D947' => 'group_id', 'IDX_79EAAC254162C001' => 'related_id', 'IDX_79EAAC25724EA09E' => 'addedby', 'IDX_79EAAC255AC430DE' => 'deletedby'] as $index => $column) {
            if (!$this->indexExists('groups_related', $index)) {
                $this->addSql("CREATE INDEX {$index} ON groups_related ({$column})");
            }
        }
        $this->addForeignKeys();
    }

    #[Override]
    public function down(Schema $schema): void
    {
        // Only the keys are removed: on databases migrated from production the table and its rows
        // existed before this migration.
        foreach (array_keys(self::FOREIGN_KEYS) as $name) {
            if ($this->foreignKeyExists('groups_related', $name)) {
                $this->addSql("ALTER TABLE groups_related DROP FOREIGN KEY {$name}");
            }
        }
    }

    // test:database:create builds the schema from the entities before it migrates, so the
    // keys can exist already.
    private function addForeignKeys(): void
    {
        foreach (self::FOREIGN_KEYS as $name => $definition) {
            if (!$this->foreignKeyExists('groups_related', $name)) {
                $this->addSql("ALTER TABLE groups_related ADD CONSTRAINT {$name} {$definition}");
            }
        }
    }

    private function foreignKeyExists(string $table, string $name): bool
    {
        return (bool) $this->connection->fetchOne(
            'SELECT COUNT(*) FROM information_schema.table_constraints WHERE constraint_schema = DATABASE() AND table_name = ? AND constraint_name = ? AND constraint_type = \'FOREIGN KEY\'',
            [$table, $name],
        );
    }

    private function tableExists(string $table): bool
    {
        return (bool) $this->connection->fetchOne(
            'SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = ?',
            [$table],
        );
    }

    private function indexExists(string $table, string $index): bool
    {
        return (bool) $this->connection->fetchOne(
            'SELECT COUNT(*) FROM information_schema.statistics WHERE table_schema = DATABASE() AND table_name = ? AND index_name = ?',
            [$table, $index],
        );
    }
}
