<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Override;

/**
 * develop's table of donations, `donation` (App\Entity\Donation), filled from main's `donations` (App\Entity\Donations),
 * the ids kept. A donor or country that no longer exists is left empty; the donor's name stays.
 *
 * `donations` stays: main reads and writes it.
 */
final class Version20261111100000 extends AbstractMigration
{
    #[Override]
    public function getDescription(): string
    {
        return 'Create donation and copy main\'s donations into it.';
    }

    public function up(Schema $schema): void
    {
        $this->connection->executeStatement('CREATE TABLE IF NOT EXISTS donation (id INT AUTO_INCREMENT NOT NULL, email VARCHAR(255) NOT NULL, status_private VARCHAR(20) NOT NULL, created DATETIME NOT NULL, amount NUMERIC(10, 2) NOT NULL, money VARCHAR(10) NOT NULL, name_given VARCHAR(255) NOT NULL, reference_paypal VARCHAR(255) NOT NULL, member_comment LONGTEXT NOT NULL, system_comment LONGTEXT NOT NULL, donor_id INT DEFAULT NULL, country_id INT DEFAULT NULL, INDEX donation_created_idx (created), INDEX IDX_31E581A03DD7B7A7 (donor_id), INDEX IDX_31E581A0F92F3E70 (country_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addForeignKey('FK_31E581A03DD7B7A7', 'FOREIGN KEY (donor_id) REFERENCES member (id) ON DELETE SET NULL');
        $this->addForeignKey('FK_31E581A0F92F3E70', 'FOREIGN KEY (country_id) REFERENCES location (geoname_id)');

        if (!$this->tableExists('donations')) {
            return;
        }
        $this->connection->executeStatement(<<<'SQL'
            INSERT IGNORE INTO donation (id, email, status_private, created, amount, money, name_given, reference_paypal,
                member_comment, system_comment, donor_id, country_id)
            SELECT d.id, COALESCE(d.Email, ''), COALESCE(d.StatusPrivate, 'showamountonly'), d.created, d.Amount,
                COALESCE(d.Money, ''), LEFT(COALESCE(d.namegiven, ''), 255), LEFT(COALESCE(d.referencepaypal, ''), 255),
                COALESCE(d.membercomment, ''), COALESCE(d.SystemComment, ''), m.id, l.geoname_id
            FROM donations d
            LEFT JOIN member m ON m.id = d.IdMember
            LEFT JOIN location l ON l.geoname_id = d.IdCountry
            SQL);
    }

    #[Override]
    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE IF EXISTS donation');
    }

    private function tableExists(string $table): bool
    {
        return (bool) $this->connection->fetchOne(
            'SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = ?',
            [$table],
        );
    }

    /**
     * Adds the foreign key unless it exists (the test database's schema comes from the entities).
     */
    private function addForeignKey(string $name, string $definition): void
    {
        $exists = $this->connection->fetchOne(
            'SELECT COUNT(*) FROM information_schema.referential_constraints
             WHERE constraint_schema = DATABASE() AND table_name = ? AND constraint_name = ?',
            ['donation', $name],
        );
        if (!$exists) {
            $this->connection->executeStatement(\sprintf('ALTER TABLE donation ADD CONSTRAINT %s %s', $name, $definition));
        }
    }
}
