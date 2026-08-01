<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260801184852 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE flagsmembers ADD CONSTRAINT FK_1A4013A9EA8330B4 FOREIGN KEY (IdMember) REFERENCES member (id)');
        $this->addSql('ALTER TABLE flagsmembers ADD CONSTRAINT FK_1A4013A9A5A5B032 FOREIGN KEY (IdFlag) REFERENCES flags (id)');
        $this->addSql('CREATE INDEX IDX_1A4013A9EA8330B4 ON flagsmembers (IdMember)');
        $this->addSql('CREATE INDEX IDX_1A4013A9A5A5B032 ON flagsmembers (IdFlag)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE flagsmembers DROP FOREIGN KEY FK_1A4013A9EA8330B4');
        $this->addSql('ALTER TABLE flagsmembers DROP FOREIGN KEY FK_1A4013A9A5A5B032');
        $this->addSql('DROP INDEX IDX_1A4013A9EA8330B4 ON flagsmembers');
        $this->addSql('DROP INDEX IDX_1A4013A9A5A5B032 ON flagsmembers');
    }
}
