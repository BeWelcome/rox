<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Override;

/**
 * A group membership has one switch for the group's forum mails: notifications_enabled. accept_mass_mail goes.
 *
 * On main the forum notifier reads IacceptMassMailFromThisGroup, which the move to group_membership copied into
 * accept_mass_mail, while joining a group stores the member's choice in notificationsEnabled only (rox#591).
 * develop's notifier reads notifications_enabled, so accept_mass_mail was a switch on the member settings page that
 * changed nothing, and the support team's tool turned off only the memberships that had it on. A membership with
 * accept_mass_mail on gets the forum mails on main: it keeps them here, so notifications_enabled is turned on for it
 * first.
 *
 * group_membership is develop's table (main's is membersgroups). The test database's schema comes from the entities,
 * so there the column isn't there.
 */
final class Version20261125100000 extends AbstractMigration
{
    #[Override]
    public function getDescription(): string
    {
        return 'Drop group_membership.accept_mass_mail; memberships with it on keep their forum mails.';
    }

    #[Override]
    public function isTransactional(): bool
    {
        // ALTER TABLE commits implicitly.
        return false;
    }

    public function up(Schema $schema): void
    {
        if (!$this->columnExists()) {
            return;
        }
        $this->connection->executeStatement(
            'UPDATE group_membership SET notifications_enabled = 1 WHERE accept_mass_mail = 1 AND notifications_enabled = 0',
        );
        $this->connection->executeStatement('ALTER TABLE group_membership DROP COLUMN accept_mass_mail');
    }

    #[Override]
    public function down(Schema $schema): void
    {
        if ($this->columnExists()) {
            return;
        }
        // which memberships had it on before is not kept: back, it follows the forum mails
        $this->connection->executeStatement(
            'ALTER TABLE group_membership ADD COLUMN accept_mass_mail TINYINT(1) DEFAULT 0 NOT NULL AFTER comment',
        );
        $this->connection->executeStatement('UPDATE group_membership SET accept_mass_mail = notifications_enabled');
    }

    private function columnExists(): bool
    {
        return (bool) $this->connection->fetchOne(
            "SELECT COUNT(*) FROM information_schema.columns
             WHERE table_schema = DATABASE() AND table_name = 'group_membership' AND column_name = 'accept_mass_mail'",
        );
    }
}
