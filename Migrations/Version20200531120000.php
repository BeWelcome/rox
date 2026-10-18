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
            . 'and geo__countries.country) is dropped and recreated against the new name. Runs before '
            . 'Version20200601000000, which adds address.location -> geo__names (geoname_id).';
    }

    public function up(Schema $schema): void
    {
        if (!$this->columnExists('geo__names', 'geonameId')) {
            $this->write('geo__names.geonameId not found (already renamed or table absent) - skipping.');

            return;
        }

        $this->addSql('ALTER TABLE geo__names DROP FOREIGN KEY FK_D3DA9CA23A3235B3');
        $this->addSql('ALTER TABLE geo__names DROP FOREIGN KEY FK_D3DA9CA2A33B6409');
        $this->addSql('ALTER TABLE geo__names DROP FOREIGN KEY FK_D3DA9CA2D43C549F');
        $this->addSql('ALTER TABLE geo__names DROP FOREIGN KEY FK_D3DA9CA24A58C13C');
        $this->addSql('ALTER TABLE geo__names DROP FOREIGN KEY FK_D3DA9CA25373C966');
        $this->addSql('ALTER TABLE geo__countries DROP FOREIGN KEY FK_6A7A43615373C966');

        $this->addSql('ALTER TABLE geo__names CHANGE geonameId geoname_id INT NOT NULL');

        $this->addSql('ALTER TABLE geo__names ADD CONSTRAINT FK_D3DA9CA23A3235B3 FOREIGN KEY (admin1) REFERENCES geo__names (geoname_id)');
        $this->addSql('ALTER TABLE geo__names ADD CONSTRAINT FK_D3DA9CA2A33B6409 FOREIGN KEY (admin2) REFERENCES geo__names (geoname_id)');
        $this->addSql('ALTER TABLE geo__names ADD CONSTRAINT FK_D3DA9CA2D43C549F FOREIGN KEY (admin3) REFERENCES geo__names (geoname_id)');
        $this->addSql('ALTER TABLE geo__names ADD CONSTRAINT FK_D3DA9CA24A58C13C FOREIGN KEY (admin4) REFERENCES geo__names (geoname_id)');
        $this->addSql('ALTER TABLE geo__names ADD CONSTRAINT FK_D3DA9CA25373C966 FOREIGN KEY (country) REFERENCES geo__names (geoname_id)');
        $this->addSql('ALTER TABLE geo__countries ADD CONSTRAINT FK_6A7A43615373C966 FOREIGN KEY (country) REFERENCES geo__names (geoname_id)');
    }

    #[Override]
    public function down(Schema $schema): void
    {
        if (!$this->columnExists('geo__names', 'geoname_id')) {
            $this->write('geo__names.geoname_id not found - skipping.');

            return;
        }

        $this->addSql('ALTER TABLE geo__names DROP FOREIGN KEY FK_D3DA9CA23A3235B3');
        $this->addSql('ALTER TABLE geo__names DROP FOREIGN KEY FK_D3DA9CA2A33B6409');
        $this->addSql('ALTER TABLE geo__names DROP FOREIGN KEY FK_D3DA9CA2D43C549F');
        $this->addSql('ALTER TABLE geo__names DROP FOREIGN KEY FK_D3DA9CA24A58C13C');
        $this->addSql('ALTER TABLE geo__names DROP FOREIGN KEY FK_D3DA9CA25373C966');
        $this->addSql('ALTER TABLE geo__countries DROP FOREIGN KEY FK_6A7A43615373C966');

        $this->addSql('ALTER TABLE geo__names CHANGE geoname_id geonameId INT NOT NULL');

        $this->addSql('ALTER TABLE geo__names ADD CONSTRAINT FK_D3DA9CA23A3235B3 FOREIGN KEY (admin1) REFERENCES geo__names (geonameId)');
        $this->addSql('ALTER TABLE geo__names ADD CONSTRAINT FK_D3DA9CA2A33B6409 FOREIGN KEY (admin2) REFERENCES geo__names (geonameId)');
        $this->addSql('ALTER TABLE geo__names ADD CONSTRAINT FK_D3DA9CA2D43C549F FOREIGN KEY (admin3) REFERENCES geo__names (geonameId)');
        $this->addSql('ALTER TABLE geo__names ADD CONSTRAINT FK_D3DA9CA24A58C13C FOREIGN KEY (admin4) REFERENCES geo__names (geonameId)');
        $this->addSql('ALTER TABLE geo__names ADD CONSTRAINT FK_D3DA9CA25373C966 FOREIGN KEY (country) REFERENCES geo__names (geonameId)');
        $this->addSql('ALTER TABLE geo__countries ADD CONSTRAINT FK_6A7A43615373C966 FOREIGN KEY (country) REFERENCES geo__names (geonameId)');
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
