<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Override;

final class Version20261019120000 extends AbstractMigration
{
    #[Override]
    public function getDescription(): string
    {
        return 'Replace geo__names.latitude/longitude with a spatial coordinates POINT column (x = longitude, y = latitude, '
            . 'SRID 0 like the points Doctrine writes and the spatial queries use) to match the Location entity mapping.';
    }

    #[Override]
    public function isTransactional(): bool
    {
        // MariaDB commits implicitly on DDL.
        return false;
    }

    public function up(Schema $schema): void
    {
        $this->skipIf(!$this->columnExists('geo__names', 'latitude'), 'geo__names.latitude not found - nothing to migrate.');

        if (!$this->columnExists('geo__names', 'coordinates')) {
            $this->addSql('ALTER TABLE geo__names ADD coordinates POINT DEFAULT NULL AFTER name');
        }

        // Point() creates SRID 0. MariaDB refuses to compare geometries with different SRIDs.
        $this->addSql('UPDATE geo__names SET coordinates = Point(COALESCE(longitude, 0), COALESCE(latitude, 0))');

        // One ALTER so the 5M+ row table is only rebuilt once. A spatial index requires a NOT NULL column.
        $this->addSql('ALTER TABLE geo__names
            MODIFY coordinates POINT NOT NULL,
            DROP INDEX geonames_idx_latitude,
            DROP INDEX geonames_idx_longitude,
            DROP COLUMN latitude,
            DROP COLUMN longitude,
            ADD SPATIAL INDEX geonames_idx_spatial_coordinates (coordinates)');
    }

    #[Override]
    public function down(Schema $schema): void
    {
        $this->skipIf(!$this->columnExists('geo__names', 'coordinates'), 'geo__names.coordinates not found - nothing to revert.');

        $this->addSql('ALTER TABLE geo__names
            ADD latitude NUMERIC(10, 7) DEFAULT NULL AFTER name,
            ADD longitude NUMERIC(10, 7) DEFAULT NULL AFTER latitude');

        $this->addSql('UPDATE geo__names SET latitude = ST_Y(coordinates), longitude = ST_X(coordinates)');

        $this->addSql('ALTER TABLE geo__names
            DROP INDEX geonames_idx_spatial_coordinates,
            DROP COLUMN coordinates,
            ADD INDEX geonames_idx_latitude (latitude),
            ADD INDEX geonames_idx_longitude (longitude)');
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
