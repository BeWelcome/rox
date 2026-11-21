<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Override;

/**
 * An address keeps the country and the region of its place (country_code, region_code, copies of location.country_id
 * and location.admin_1_id), with an index.
 *
 * The places pages ask for the members of a country and of its regions. Only location knew both, so every such
 * query read all addresses and looked up the place of each one to see whether it was in the country: 6 seconds
 * for the regions of one country, 10 for its page, whatever the country. With the codes on the address the index
 * answers it (it holds active and the member too: without them the database still preferred reading all addresses).
 *
 * address is develop's table (main's is addresses). Each step checks first: the test database's schema comes from
 * the entities, so there the columns and the index are there already.
 */
final class Version20261121140000 extends AbstractMigration
{
    #[Override]
    public function getDescription(): string
    {
        return 'Keep the country and region of its place on the address, with an index.';
    }

    public function up(Schema $schema): void
    {
        $this->connection->executeStatement('ALTER TABLE address ADD COLUMN IF NOT EXISTS country_code VARCHAR(2) DEFAULT NULL, ADD COLUMN IF NOT EXISTS region_code VARCHAR(32) DEFAULT NULL');
        $this->connection->executeStatement(<<<'SQL'
            UPDATE address a JOIN location g ON g.geoname_id = a.location
            SET a.country_code = g.country_id, a.region_code = LEFT(g.admin_1_id, 32)
            WHERE NOT (a.country_code <=> g.country_id AND a.region_code <=> LEFT(g.admin_1_id, 32))
            SQL);
        $this->connection->executeStatement('CREATE INDEX IF NOT EXISTS address_country_region_idx ON address (country_code, region_code, active, member_id)');
    }

    #[Override]
    public function down(Schema $schema): void
    {
        $this->connection->executeStatement('DROP INDEX IF EXISTS address_country_region_idx ON address');
        $this->connection->executeStatement('ALTER TABLE address DROP COLUMN IF EXISTS country_code, DROP COLUMN IF EXISTS region_code');
    }
}
