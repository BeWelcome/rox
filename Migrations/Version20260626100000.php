<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Override;

final class Version20260626100000 extends AbstractMigration
{
    #[Override]
    public function getDescription(): string
    {
        return 'Add subtrip read state and trip notification preference';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE IF NOT EXISTS member_subtrips_read (id INT AUTO_INCREMENT NOT NULL, member_id INT NOT NULL, subtrip_id INT NOT NULL, created DATETIME NOT NULL, INDEX IDX_B1EC0277597D3FE (member_id), INDEX IDX_B1EC027F2BD7DD7 (subtrip_id), UNIQUE INDEX member_subtrip_read_unique (member_id, subtrip_id), CONSTRAINT FK_B1EC0277597D3FE FOREIGN KEY (member_id) REFERENCES member (id) ON DELETE CASCADE, CONSTRAINT FK_B1EC027F2BD7DD7 FOREIGN KEY (subtrip_id) REFERENCES sub_trips (id) ON DELETE CASCADE, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql("INSERT INTO preferences (position, codeName, codeDescription, Description, created, DefaultValue, PossibleValues, Status) SELECT 65, 'TripsNotifications', 'trips.notifications', 'How often the member wants notifications for trips in their area', NOW(), 'No', 'No;Immediately;Daily;Weekly;Monthly', 'Normal' WHERE NOT EXISTS (SELECT 1 FROM preferences WHERE codeName = 'TripsNotifications')");
        $this->addSql("UPDATE preferences SET PossibleValues = 'No;Immediately;Daily;Weekly;Monthly' WHERE codeName = 'TripsNotifications'");
        $this->addSql("UPDATE memberspreferences mp INNER JOIN preferences p ON p.id = mp.IdPreference SET mp.Value = 'Immediately' WHERE p.codeName = 'TripsNotifications' AND mp.Value = 'Yes'");
    }

    #[Override]
    public function down(Schema $schema): void
    {
        $this->addSql("DELETE mp FROM memberspreferences mp INNER JOIN preferences p ON p.id = mp.IdPreference WHERE p.codeName = 'TripsNotifications'");
        $this->addSql("DELETE FROM preferences WHERE codeName = 'TripsNotifications'");
        $this->addSql('DROP TABLE IF EXISTS member_subtrips_read');
    }
}
