<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Override;

final class Version20200531120000 extends AbstractMigration
{
    #[Override]
    public function getDescription(): string
    {
        return 'Rename geo__names.geonameId to geoname_id to match the Location entity mapping '
            . '(#[ORM\Column(name: \'geoname_id\')]) - the entity was renamed ahead of any migration, '
            . 'so every FK referencing this column (geo__names self-references for country/admin1-4, '
            . 'and geo__countries.country, where that table exists) is dropped and recreated against the '
            . 'new name. Runs before Version20200601000000, which adds '
            . 'address.location -> geo__names (geoname_id).';
    }

    public function up(Schema $schema): void
    {
        // Adding a FOREIGN KEY on InnoDB otherwise forces ALGORITHM=COPY (a full table rebuild) - on a
        // table the size of geo__names (5M+ rows) that takes close to an hour per constraint. With FK
        // checks off, MariaDB accepts ALGORITHM=INPLACE instead, which is metadata-only.
        $this->connection->executeStatement('SET FOREIGN_KEY_CHECKS=0');

        $this->dropConstraintIfExists('geo__names', 'FK_D3DA9CA23A3235B3');
        $this->dropConstraintIfExists('geo__names', 'FK_D3DA9CA2A33B6409');
        $this->dropConstraintIfExists('geo__names', 'FK_D3DA9CA2D43C549F');
        $this->dropConstraintIfExists('geo__names', 'FK_D3DA9CA24A58C13C');
        $this->dropConstraintIfExists('geo__names', 'FK_D3DA9CA25373C966');
        // geo__countries doesn't exist on every database yet (some still have the pre-rename
        // geonamescountries) - its own rename is a separate, not-yet-written migration.
        $this->dropConstraintIfExists('geo__countries', 'FK_6A7A43615373C966');

        if ($this->columnExists('geo__names', 'geonameId')) {
            $this->connection->executeStatement('ALTER TABLE geo__names CHANGE geonameId geoname_id INT NOT NULL');
        } else {
            $this->write('geo__names.geonameId not found (already renamed or table absent) - skipping rename.');
        }

        $this->addConstraintIfMissing('geo__names', 'FK_D3DA9CA23A3235B3', 'ALTER TABLE geo__names ADD CONSTRAINT FK_D3DA9CA23A3235B3 FOREIGN KEY (admin1) REFERENCES geo__names (geoname_id)');
        $this->addConstraintIfMissing('geo__names', 'FK_D3DA9CA2A33B6409', 'ALTER TABLE geo__names ADD CONSTRAINT FK_D3DA9CA2A33B6409 FOREIGN KEY (admin2) REFERENCES geo__names (geoname_id)');
        $this->addConstraintIfMissing('geo__names', 'FK_D3DA9CA2D43C549F', 'ALTER TABLE geo__names ADD CONSTRAINT FK_D3DA9CA2D43C549F FOREIGN KEY (admin3) REFERENCES geo__names (geoname_id)');
        $this->addConstraintIfMissing('geo__names', 'FK_D3DA9CA24A58C13C', 'ALTER TABLE geo__names ADD CONSTRAINT FK_D3DA9CA24A58C13C FOREIGN KEY (admin4) REFERENCES geo__names (geoname_id)');
        $this->addConstraintIfMissing('geo__names', 'FK_D3DA9CA25373C966', 'ALTER TABLE geo__names ADD CONSTRAINT FK_D3DA9CA25373C966 FOREIGN KEY (country) REFERENCES geo__names (geoname_id)');
        $this->addConstraintIfMissing('geo__countries', 'FK_6A7A43615373C966', 'ALTER TABLE geo__countries ADD CONSTRAINT FK_6A7A43615373C966 FOREIGN KEY (country) REFERENCES geo__names (geoname_id)');

        $this->connection->executeStatement('SET FOREIGN_KEY_CHECKS=1');
    }

    #[Override]
    public function down(Schema $schema): void
    {
        $this->connection->executeStatement('SET FOREIGN_KEY_CHECKS=0');

        $this->dropConstraintIfExists('geo__names', 'FK_D3DA9CA23A3235B3');
        $this->dropConstraintIfExists('geo__names', 'FK_D3DA9CA2A33B6409');
        $this->dropConstraintIfExists('geo__names', 'FK_D3DA9CA2D43C549F');
        $this->dropConstraintIfExists('geo__names', 'FK_D3DA9CA24A58C13C');
        $this->dropConstraintIfExists('geo__names', 'FK_D3DA9CA25373C966');
        $this->dropConstraintIfExists('geo__countries', 'FK_6A7A43615373C966');

        if ($this->columnExists('geo__names', 'geoname_id')) {
            $this->connection->executeStatement('ALTER TABLE geo__names CHANGE geoname_id geonameId INT NOT NULL');
        } else {
            $this->write('geo__names.geoname_id not found - skipping rename.');
        }

        $this->addConstraintIfMissing('geo__names', 'FK_D3DA9CA23A3235B3', 'ALTER TABLE geo__names ADD CONSTRAINT FK_D3DA9CA23A3235B3 FOREIGN KEY (admin1) REFERENCES geo__names (geonameId)');
        $this->addConstraintIfMissing('geo__names', 'FK_D3DA9CA2A33B6409', 'ALTER TABLE geo__names ADD CONSTRAINT FK_D3DA9CA2A33B6409 FOREIGN KEY (admin2) REFERENCES geo__names (geonameId)');
        $this->addConstraintIfMissing('geo__names', 'FK_D3DA9CA2D43C549F', 'ALTER TABLE geo__names ADD CONSTRAINT FK_D3DA9CA2D43C549F FOREIGN KEY (admin3) REFERENCES geo__names (geonameId)');
        $this->addConstraintIfMissing('geo__names', 'FK_D3DA9CA24A58C13C', 'ALTER TABLE geo__names ADD CONSTRAINT FK_D3DA9CA24A58C13C FOREIGN KEY (admin4) REFERENCES geo__names (geonameId)');
        $this->addConstraintIfMissing('geo__names', 'FK_D3DA9CA25373C966', 'ALTER TABLE geo__names ADD CONSTRAINT FK_D3DA9CA25373C966 FOREIGN KEY (country) REFERENCES geo__names (geonameId)');
        $this->addConstraintIfMissing('geo__countries', 'FK_6A7A43615373C966', 'ALTER TABLE geo__countries ADD CONSTRAINT FK_6A7A43615373C966 FOREIGN KEY (country) REFERENCES geo__names (geonameId)');

        $this->connection->executeStatement('SET FOREIGN_KEY_CHECKS=1');
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

    private function tableExists(string $tableName): bool
    {
        $count = $this->connection->executeQuery(
            'SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = ?',
            [$tableName],
        )->fetchOne();

        return (int) $count > 0;
    }

    private function constraintExists(string $tableName, string $constraintName): bool
    {
        if (!$this->tableExists($tableName)) {
            return false;
        }

        $count = $this->connection->executeQuery(
            'SELECT COUNT(*) FROM information_schema.table_constraints
             WHERE constraint_schema = DATABASE() AND table_name = ? AND constraint_name = ?',
            [$tableName, $constraintName],
        )->fetchOne();

        return (int) $count > 0;
    }

    private function dropConstraintIfExists(string $tableName, string $constraintName): void
    {
        if (!$this->constraintExists($tableName, $constraintName)) {
            return;
        }

        $this->connection->executeStatement("ALTER TABLE {$tableName} DROP FOREIGN KEY {$constraintName}");
    }

    private function addConstraintIfMissing(string $tableName, string $constraintName, string $sql): void
    {
        if (!$this->tableExists($tableName) || $this->constraintExists($tableName, $constraintName)) {
            return;
        }

        // Only valid with FOREIGN_KEY_CHECKS=0 (see up()/down()) - otherwise MariaDB falls back to
        // ALGORITHM=COPY, which rebuilds the whole table.
        $this->connection->executeStatement($sql . ', ALGORITHM=INPLACE, LOCK=NONE');
    }
}
