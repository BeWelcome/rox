<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Override;

/**
 * The places get tables of their own, location and location_translation, mapped by Location and
 * LocationTranslation. main keeps using geo__names and geo__names_translations, which stay as they are.
 */
final class Version20200531140000 extends AbstractMigration
{
    #[Override]
    public function getDescription(): string
    {
        return 'Create location and location_translation (Location, LocationTranslation) and copy the places and '
            . 'their names from geo__names and geo__names_translations, which main keeps using. Keeps the first '
            . 'name per place, language and field, as the geonames import does.';
    }

    #[Override]
    public function isTransactional(): bool
    {
        // DDL commits implicitly
        return false;
    }

    public function up(Schema $schema): void
    {
        // Doctrine's DDL for Location and LocationTranslation
        $this->connection->executeStatement('CREATE TABLE IF NOT EXISTS location (name VARCHAR(200) DEFAULT NULL, coordinates POINT NOT NULL, feature_class VARCHAR(1) DEFAULT NULL, feature_code VARCHAR(10) DEFAULT NULL, country_id VARCHAR(255) DEFAULT NULL, admin_1_id VARCHAR(255) DEFAULT NULL, admin_2_id VARCHAR(255) DEFAULT NULL, admin_3_id VARCHAR(255) DEFAULT NULL, admin_4_id VARCHAR(255) DEFAULT NULL, population BIGINT DEFAULT NULL, moddate DATE DEFAULT NULL, geoname_id INT NOT NULL, country INT DEFAULT NULL, admin1 INT DEFAULT NULL, admin2 INT DEFAULT NULL, admin3 INT DEFAULT NULL, admin4 INT DEFAULT NULL, INDEX IDX_5E9E89CB5E237E06 (name), INDEX IDX_5E9E89CB24151396 (feature_class), INDEX IDX_5E9E89CB253FBFEB (feature_code), SPATIAL INDEX IDX_5E9E89CB9816D676 (coordinates), INDEX IDX_5E9E89CB5373C966 (country), INDEX IDX_5E9E89CB3A3235B3 (admin1), INDEX IDX_5E9E89CBA33B6409 (admin2), INDEX IDX_5E9E89CBD43C549F (admin3), INDEX IDX_5E9E89CB4A58C13C (admin4), INDEX IDX_5E9E89CBF92F3E70 (country_id), INDEX IDX_5E9E89CBD0CADE1A (admin_1_id), INDEX IDX_5E9E89CBC27F71F4 (admin_2_id), INDEX IDX_5E9E89CB7AC31691 (admin_3_id), INDEX IDX_5E9E89CBE7142E28 (admin_4_id), INDEX IDX_5E9E89CBF92F3E7023F5422B (country_id, geoname_id), INDEX IDX_5E9E89CBF92F3E70D0CADE1AC27F71F4 (country_id, admin_1_id, admin_2_id), PRIMARY KEY (geoname_id)) DEFAULT CHARACTER SET utf8mb4');
        $this->connection->executeStatement('CREATE TABLE IF NOT EXISTS location_translation (id INT AUTO_INCREMENT NOT NULL, locale VARCHAR(8) NOT NULL, field VARCHAR(32) NOT NULL, content LONGTEXT DEFAULT NULL, object_id INT DEFAULT NULL, UNIQUE INDEX lookup_unique_idx (locale, object_id, field), INDEX IDX_A2BCA22B232D562B (object_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');

        $this->connection->executeStatement('SET FOREIGN_KEY_CHECKS=0');
        try {
            $this->copyPlaces();
            $this->copyNames();

            $this->addConstraintIfMissing('location', 'FK_5E9E89CB5373C966', 'ALTER TABLE location ADD CONSTRAINT FK_5E9E89CB5373C966 FOREIGN KEY (country) REFERENCES location (geoname_id)');
            $this->addConstraintIfMissing('location', 'FK_5E9E89CB3A3235B3', 'ALTER TABLE location ADD CONSTRAINT FK_5E9E89CB3A3235B3 FOREIGN KEY (admin1) REFERENCES location (geoname_id)');
            $this->addConstraintIfMissing('location', 'FK_5E9E89CBA33B6409', 'ALTER TABLE location ADD CONSTRAINT FK_5E9E89CBA33B6409 FOREIGN KEY (admin2) REFERENCES location (geoname_id)');
            $this->addConstraintIfMissing('location', 'FK_5E9E89CBD43C549F', 'ALTER TABLE location ADD CONSTRAINT FK_5E9E89CBD43C549F FOREIGN KEY (admin3) REFERENCES location (geoname_id)');
            $this->addConstraintIfMissing('location', 'FK_5E9E89CB4A58C13C', 'ALTER TABLE location ADD CONSTRAINT FK_5E9E89CB4A58C13C FOREIGN KEY (admin4) REFERENCES location (geoname_id)');
            $this->addConstraintIfMissing('location_translation', 'FK_A2BCA22B232D562B', 'ALTER TABLE location_translation ADD CONSTRAINT FK_A2BCA22B232D562B FOREIGN KEY (object_id) REFERENCES location (geoname_id) ON DELETE CASCADE');

            // geo__countries (Country) isn't used by main; its countries are places of location now
            $this->dropConstraintIfExists('geo__countries', 'FK_6A7A43615373C966');
            $this->addConstraintIfMissing('geo__countries', 'FK_6987CD3E5373C966', 'ALTER TABLE geo__countries ADD CONSTRAINT FK_6987CD3E5373C966 FOREIGN KEY (country) REFERENCES location (geoname_id)');
        } finally {
            $this->connection->executeStatement('SET FOREIGN_KEY_CHECKS=1');
        }
    }

    #[Override]
    public function down(Schema $schema): void
    {
        $this->connection->executeStatement('SET FOREIGN_KEY_CHECKS=0');
        $this->dropConstraintIfExists('geo__countries', 'FK_6987CD3E5373C966');
        $this->connection->executeStatement('DROP TABLE IF EXISTS location_translation');
        $this->connection->executeStatement('DROP TABLE IF EXISTS location');
        $this->connection->executeStatement('SET FOREIGN_KEY_CHECKS=1');
    }

    /**
     * Production's geo__names has geonameId and latitude/longitude. A database that ran this branch's earlier
     * migrations may have geoname_id and coordinates instead.
     */
    private function copyPlaces(): void
    {
        if (!$this->tableExists('geo__names')) {
            $this->write('geo__names not found - no places to copy.');

            return;
        }
        $id = $this->columnExists('geo__names', 'geonameId') ? 'geonameId' : 'geoname_id';
        $coordinates = $this->columnExists('geo__names', 'latitude')
            ? 'Point(COALESCE(g.longitude, 0), COALESCE(g.latitude, 0))'
            : 'g.coordinates';

        $this->connection->executeStatement(
            "INSERT IGNORE INTO location (geoname_id, name, coordinates, feature_class, feature_code, country_id,
                 admin_1_id, admin_2_id, admin_3_id, admin_4_id, population, moddate, country, admin1, admin2, admin3, admin4)
             SELECT g.{$id}, g.name, {$coordinates}, g.feature_class, g.feature_code, g.country_id,
                 g.admin_1_id, g.admin_2_id, g.admin_3_id, g.admin_4_id, g.population, g.moddate,
                 g.country, g.admin1, g.admin2, g.admin3, g.admin4
             FROM geo__names g"
        );
    }

    /**
     * Production's geo__names_translations is a general Gedmo translation (object_class, foreign_key) with several
     * names per place and language; INSERT IGNORE in id order keeps the first. Names of places that aren't in
     * location are left out.
     */
    private function copyNames(): void
    {
        if (!$this->tableExists('geo__names_translations')) {
            $this->write('geo__names_translations not found - no names to copy.');

            return;
        }
        $place = $this->columnExists('geo__names_translations', 'foreign_key') ? 'foreign_key' : 'object_id';

        $this->connection->executeStatement(
            "INSERT IGNORE INTO location_translation (locale, field, content, object_id)
             SELECT t.locale, t.field, t.content, t.{$place}
             FROM geo__names_translations t
             JOIN location l ON l.geoname_id = t.{$place}
             ORDER BY t.id"
        );
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

    private function constraintExists(string $tableName, string $constraintName): bool
    {
        return (int) $this->connection->executeQuery(
            "SELECT COUNT(*) FROM information_schema.table_constraints
             WHERE table_schema = DATABASE() AND table_name = ? AND constraint_name = ? AND constraint_type = 'FOREIGN KEY'",
            [$tableName, $constraintName],
        )->fetchOne() > 0;
    }

    private function dropConstraintIfExists(string $tableName, string $constraintName): void
    {
        if ($this->constraintExists($tableName, $constraintName)) {
            $this->connection->executeStatement("ALTER TABLE {$tableName} DROP FOREIGN KEY {$constraintName}");
        }
    }

    private function addConstraintIfMissing(string $tableName, string $constraintName, string $sql): void
    {
        if (!$this->tableExists($tableName) || $this->constraintExists($tableName, $constraintName)) {
            return;
        }

        // With FOREIGN_KEY_CHECKS=0, MariaDB adds the key without copying the table
        $this->connection->executeStatement($sql . ', ALGORITHM=INPLACE, LOCK=NONE');
    }
}
