<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Override;

/**
 * develop's trips in tables of its own, named in the singular: `trip` (App\Entity\Trip) and `leg` (App\Entity\Leg),
 * filled from main's `trips` and `sub_trips` (App\Entity\Trips and App\Entity\Subtrips, which stay), the ids kept.
 *
 * What pointed to `sub_trips` points to `leg` from now on:
 * - the invitations (request.invite_for_leg), whose ids stay valid as the legs keep theirs;
 * - the legs a member hid and the trip notifications sent, whose tables are renamed after the leg as well:
 *   member_subtrip_hidden to member_leg_hidden, member_subtrip_notification_sent to member_leg_notification_sent
 *   (develop's own tables), their column subtrip_id to leg_id.
 *
 * main's tables have no foreign keys, so not everything can come along: a trip whose member is gone, a leg whose trip
 * or place (location) is gone, and a trip left without legs stay in main's tables only; an invitation of a leg that
 * doesn't exist loses its leg (the new foreign key needs one that exists), and so do hidden legs and notifications.
 *
 * A leg's host (invited_by_id) is no longer unique: a host can host many legs. A leg with invitations that is taken off
 * its trip is kept, marked removed (removed), so the invitations still show what they were about.
 *
 * Each step checks first: the test database's schema comes from the entities, so there the new tables are there
 * already (and empty) and the old side tables are not.
 */
final class Version20261114100000 extends AbstractMigration
{
    private const array SIDE_TABLES = [
        'member_subtrip_hidden' => ['member_leg_hidden', 'E3C72E68'],
        'member_subtrip_notification_sent' => ['member_leg_notification_sent', '34A944FF'],
    ];

    #[Override]
    public function getDescription(): string
    {
        return 'Create trip and leg from main\'s trips and sub_trips, point invitations, hidden legs and trip notifications to leg.';
    }

    public function up(Schema $schema): void
    {
        $this->createTrip();
        $this->createLeg();

        if ($this->tableExists('trips')) {
            $this->connection->executeStatement(<<<'SQL'
                INSERT IGNORE INTO trip (id, summary, description, count_of_travellers, invitation_radius, created, updated,
                    deleted, additional_info, creator_id)
                SELECT t.id, t.summary, t.description, t.countOfTravellers, t.invitation_radius, t.created, t.updated,
                    t.deleted, t.additionalInfo, t.created_by
                FROM trips t
                INNER JOIN member m ON m.id = t.created_by
                SQL);
        }
        if ($this->tableExists('sub_trips')) {
            $this->connection->executeStatement(<<<'SQL'
                INSERT IGNORE INTO leg (id, trip_id, location_id, arrival, departure, options, invited_by_id)
                SELECT s.id, s.trip_id, s.location, s.arrival, s.departure, s.options, m.id
                FROM sub_trips s
                INNER JOIN trip t ON t.id = s.trip_id
                INNER JOIN location l ON l.geoname_id = s.location
                LEFT JOIN member m ON m.id = s.invited_by
                SQL);
        }
        // a trip needs a leg (Trip::isExpired()): trips whose legs are all gone stay in trips only
        if ($this->tableExists('trips')) {
            $this->connection->executeStatement('DELETE t FROM trip t INNER JOIN trips o ON o.id = t.id WHERE NOT EXISTS (SELECT 1 FROM leg l WHERE l.trip_id = t.id)');
        }

        // the invitations: their legs (none is lost: every leg of sub_trips has its trip and place)
        if ('leg' !== $this->foreignKeyTarget('request', 'FK_3B978F9F9B262DC')) {
            if (null !== $this->foreignKeyTarget('request', 'FK_3B978F9F9B262DC')) {
                $this->connection->executeStatement('ALTER TABLE request DROP FOREIGN KEY FK_3B978F9F9B262DC');
            }
            $this->connection->executeStatement('UPDATE request r LEFT JOIN leg l ON l.id = r.invite_for_leg SET r.invite_for_leg = NULL WHERE r.invite_for_leg IS NOT NULL AND l.id IS NULL');
            $this->connection->executeStatement('ALTER TABLE request ADD CONSTRAINT FK_3B978F9F9B262DC FOREIGN KEY (invite_for_leg) REFERENCES leg (id)');
        }

        foreach (self::SIDE_TABLES as $old => [$new, $hash]) {
            $this->connection->executeStatement(\sprintf(
                'CREATE TABLE IF NOT EXISTS %1$s (created DATETIME NOT NULL, id INT AUTO_INCREMENT NOT NULL, member_id INT NOT NULL, leg_id INT NOT NULL, UNIQUE INDEX %1$s_unique (member_id, leg_id), INDEX IDX_%2$s7597D3FE (member_id), INDEX IDX_%2$sA0D74527 (leg_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4',
                $new,
                $hash,
            ));
            $this->addForeignKey($new, "FK_{$hash}7597D3FE", 'FOREIGN KEY (member_id) REFERENCES member (id) ON DELETE CASCADE');
            $this->addForeignKey($new, "FK_{$hash}A0D74527", 'FOREIGN KEY (leg_id) REFERENCES leg (id) ON DELETE CASCADE');
            if ($this->tableExists($old)) {
                $this->connection->executeStatement(\sprintf(
                    'INSERT IGNORE INTO %s (id, created, member_id, leg_id) SELECT o.id, o.created, o.member_id, o.subtrip_id FROM %s o INNER JOIN leg l ON l.id = o.subtrip_id',
                    $new,
                    $old,
                ));
                $this->connection->executeStatement(\sprintf('DROP TABLE %s', $old));
            }
        }
    }

    /**
     * Back to main's tables. What was written to trip and leg since is lost; invitations of legs that aren't in
     * sub_trips lose their leg.
     */
    #[Override]
    public function down(Schema $schema): void
    {
        foreach (self::SIDE_TABLES as $old => [$new, $hash]) {
            if (!$this->tableExists($new)) {
                continue;
            }
            $this->connection->executeStatement(\sprintf(
                'CREATE TABLE IF NOT EXISTS %1$s (created DATETIME NOT NULL, id INT AUTO_INCREMENT NOT NULL, member_id INT NOT NULL, subtrip_id INT NOT NULL, UNIQUE INDEX %1$s_unique (member_id, subtrip_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4',
                $old,
            ));
            $this->connection->executeStatement(\sprintf(
                'INSERT IGNORE INTO %s (id, created, member_id, subtrip_id) SELECT n.id, n.created, n.member_id, n.leg_id FROM %s n INNER JOIN sub_trips s ON s.id = n.leg_id',
                $old,
                $new,
            ));
            $this->connection->executeStatement(\sprintf('ALTER TABLE %1$s ADD CONSTRAINT FK_%1$s_member FOREIGN KEY (member_id) REFERENCES member (id) ON DELETE CASCADE, ADD CONSTRAINT FK_%1$s_leg FOREIGN KEY (subtrip_id) REFERENCES sub_trips (id) ON DELETE CASCADE', $old));
            $this->connection->executeStatement(\sprintf('DROP TABLE %s', $new));
        }

        if ('leg' === $this->foreignKeyTarget('request', 'FK_3B978F9F9B262DC')) {
            $this->connection->executeStatement('ALTER TABLE request DROP FOREIGN KEY FK_3B978F9F9B262DC');
            $this->connection->executeStatement('UPDATE request r LEFT JOIN sub_trips s ON s.id = r.invite_for_leg SET r.invite_for_leg = NULL WHERE r.invite_for_leg IS NOT NULL AND s.id IS NULL');
            $this->connection->executeStatement('ALTER TABLE request ADD CONSTRAINT FK_3B978F9F9B262DC FOREIGN KEY (invite_for_leg) REFERENCES sub_trips (id)');
        }

        $this->connection->executeStatement('DROP TABLE IF EXISTS leg');
        $this->connection->executeStatement('DROP TABLE IF EXISTS trip');
    }

    private function createTrip(): void
    {
        $this->connection->executeStatement('CREATE TABLE IF NOT EXISTS trip (summary VARCHAR(150) NOT NULL, description VARCHAR(4096) NOT NULL, count_of_travellers INT NOT NULL, invitation_radius INT NOT NULL, created DATETIME NOT NULL, updated DATETIME DEFAULT NULL, deleted DATETIME DEFAULT NULL, additional_info VARCHAR(13) NOT NULL, id INT AUTO_INCREMENT NOT NULL, creator_id INT NOT NULL, INDEX IDX_7656F53B61220EA6 (creator_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addForeignKey('trip', 'FK_7656F53B61220EA6', 'FOREIGN KEY (creator_id) REFERENCES member (id)');
    }

    private function createLeg(): void
    {
        $this->connection->executeStatement('CREATE TABLE IF NOT EXISTS leg (arrival DATE NOT NULL, departure DATE NOT NULL, options VARCHAR(34) DEFAULT NULL, removed DATETIME DEFAULT NULL, id INT AUTO_INCREMENT NOT NULL, location_id INT NOT NULL, invited_by_id INT DEFAULT NULL, trip_id INT NOT NULL, INDEX IDX_75D0804F64D218E (location_id), INDEX IDX_75D0804FA7B4A7E3 (invited_by_id), INDEX IDX_75D0804FA5BC2E0E (trip_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        // removed: a leg with invitations, taken off its trip (App\Entity\Trip::removeLeg())
        $this->connection->executeStatement('ALTER TABLE leg ADD COLUMN IF NOT EXISTS removed DATETIME DEFAULT NULL AFTER options');
        $this->addForeignKey('leg', 'FK_75D0804F64D218E', 'FOREIGN KEY (location_id) REFERENCES location (geoname_id)');
        $this->addForeignKey('leg', 'FK_75D0804FA7B4A7E3', 'FOREIGN KEY (invited_by_id) REFERENCES member (id)');
        $this->addForeignKey('leg', 'FK_75D0804FA5BC2E0E', 'FOREIGN KEY (trip_id) REFERENCES trip (id)');
    }

    private function tableExists(string $table): bool
    {
        return (bool) $this->connection->fetchOne(
            'SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = ?',
            [$table],
        );
    }

    /**
     * @return string|null the table the foreign key references, null without it
     */
    private function foreignKeyTarget(string $table, string $name): ?string
    {
        $target = $this->connection->fetchOne(
            'SELECT referenced_table_name FROM information_schema.referential_constraints
             WHERE constraint_schema = DATABASE() AND table_name = ? AND constraint_name = ?',
            [$table, $name],
        );

        return false === $target ? null : (string) $target;
    }

    /**
     * Adds the foreign key unless it exists (the test database's schema comes from the entities).
     */
    private function addForeignKey(string $table, string $name, string $definition): void
    {
        if (null === $this->foreignKeyTarget($table, $name)) {
            $this->connection->executeStatement(\sprintf('ALTER TABLE %s ADD CONSTRAINT %s %s', $table, $name, $definition));
        }
    }
}
