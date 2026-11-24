<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Override;

/**
 * The safety team's sign-up patterns (signup_pattern, App\Entity\SignupPattern) and the members they flagged and shadow
 * banned (member_flag, App\Entity\MemberFlag). New tables of develop's; main has none.
 *
 * Each step checks first: the test database's schema comes from the entities, so there the tables are there already.
 */
final class Version20261123110000 extends AbstractMigration
{
    #[Override]
    public function getDescription(): string
    {
        return 'Create signup_pattern and member_flag: the safety team flags and shadow bans new accounts by patterns.';
    }

    public function up(Schema $schema): void
    {
        $this->connection->executeStatement('CREATE TABLE IF NOT EXISTS signup_pattern (pattern VARCHAR(100) NOT NULL, field VARCHAR(8) NOT NULL, note VARCHAR(255) DEFAULT NULL, active TINYINT NOT NULL, created DATETIME NOT NULL, id INT AUTO_INCREMENT NOT NULL, created_by_id INT DEFAULT NULL, INDEX IDX_C2AE48C5B03A8386 (created_by_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->connection->executeStatement('CREATE TABLE IF NOT EXISTS member_flag (matched VARCHAR(255) NOT NULL, created DATETIME NOT NULL, released DATETIME DEFAULT NULL, id INT AUTO_INCREMENT NOT NULL, member_id INT NOT NULL, pattern_id INT DEFAULT NULL, released_by_id INT DEFAULT NULL, INDEX member_flag_released_idx (released), UNIQUE INDEX member_flag_member (member_id), INDEX IDX_2D7CC7C6F734A20F (pattern_id), INDEX IDX_2D7CC7C6445947DD (released_by_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addForeignKey('signup_pattern', 'FK_C2AE48C5B03A8386', 'FOREIGN KEY (created_by_id) REFERENCES member (id) ON DELETE SET NULL');
        $this->addForeignKey('member_flag', 'FK_2D7CC7C67597D3FE', 'FOREIGN KEY (member_id) REFERENCES member (id) ON DELETE CASCADE');
        $this->addForeignKey('member_flag', 'FK_2D7CC7C6F734A20F', 'FOREIGN KEY (pattern_id) REFERENCES signup_pattern (id) ON DELETE SET NULL');
        $this->addForeignKey('member_flag', 'FK_2D7CC7C6445947DD', 'FOREIGN KEY (released_by_id) REFERENCES member (id) ON DELETE SET NULL');
    }

    #[Override]
    public function down(Schema $schema): void
    {
        $this->connection->executeStatement('DROP TABLE IF EXISTS member_flag');
        $this->connection->executeStatement('DROP TABLE IF EXISTS signup_pattern');
    }

    private function addForeignKey(string $table, string $name, string $definition): void
    {
        $exists = $this->connection->fetchOne(
            'SELECT COUNT(*) FROM information_schema.referential_constraints
             WHERE constraint_schema = DATABASE() AND table_name = ? AND constraint_name = ?',
            [$table, $name],
        );
        if (!$exists) {
            $this->connection->executeStatement(\sprintf('ALTER TABLE %s ADD CONSTRAINT %s %s', $table, $name, $definition));
        }
    }
}
