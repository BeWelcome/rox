<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Override;

/**
 * develop's activities in tables of their own, named in the singular: `activity` (App\Entity\Activity) and
 * `activity_attendee` (App\Entity\ActivityAttendee), filled from main's `activities` and `activitiesattendees`, which
 * stay, the ids kept. An activity now knows when it was created (created, unknown for main's), which the scheduled
 * activity notifications need; the notifications sent are kept in member_activity_notification_sent. Members choose
 * how often they get them in the preference ActivitiesNotifications (never by default), like the trip notifications.
 *
 * main's tables have no foreign keys, so not everything can come along: an activity whose creator or place (location)
 * is gone and an answer whose activity or member is gone stay in main's tables only. A member answered some activities
 * more than once; of those answers the organizer's, else the latest, comes along (one answer per member and activity).
 *
 * Each step checks first: the test database's schema comes from the entities, so there the new tables are there
 * already (and empty).
 */
final class Version20261115100000 extends AbstractMigration
{
    #[Override]
    public function getDescription(): string
    {
        return 'Create activity and activity_attendee from main\'s activities and activitiesattendees, add the activity notifications.';
    }

    public function up(Schema $schema): void
    {
        $this->connection->executeStatement('CREATE TABLE IF NOT EXISTS activity (starts DATETIME NOT NULL, ends DATETIME NOT NULL, address VARCHAR(320) DEFAULT NULL, title VARCHAR(80) NOT NULL, description MEDIUMTEXT NOT NULL, status SMALLINT NOT NULL, online SMALLINT DEFAULT NULL, created DATETIME DEFAULT NULL, id INT AUTO_INCREMENT NOT NULL, creator_id INT NOT NULL, location_id INT NOT NULL, INDEX activity_created_idx (created), INDEX IDX_AC74095A61220EA6 (creator_id), INDEX IDX_AC74095A64D218E (location_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addForeignKey('activity', 'FK_AC74095A61220EA6', 'FOREIGN KEY (creator_id) REFERENCES member (id)');
        $this->addForeignKey('activity', 'FK_AC74095A64D218E', 'FOREIGN KEY (location_id) REFERENCES location (geoname_id)');

        $this->connection->executeStatement('CREATE TABLE IF NOT EXISTS activity_attendee (organizer SMALLINT NOT NULL, status SMALLINT NOT NULL, comment VARCHAR(80) NOT NULL, id INT AUTO_INCREMENT NOT NULL, activity_id INT NOT NULL, attendee_id INT NOT NULL, UNIQUE INDEX activity_attendee_unique (activity_id, attendee_id), INDEX IDX_B15A656981C06096 (activity_id), INDEX IDX_B15A6569BCFD782A (attendee_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addForeignKey('activity_attendee', 'FK_B15A656981C06096', 'FOREIGN KEY (activity_id) REFERENCES activity (id) ON DELETE CASCADE');
        $this->addForeignKey('activity_attendee', 'FK_B15A6569BCFD782A', 'FOREIGN KEY (attendee_id) REFERENCES member (id)');

        $this->connection->executeStatement('CREATE TABLE IF NOT EXISTS member_activity_notification_sent (created DATETIME NOT NULL, id INT AUTO_INCREMENT NOT NULL, member_id INT NOT NULL, activity_id INT NOT NULL, UNIQUE INDEX member_activity_notification_sent_unique (member_id, activity_id), INDEX IDX_7304290A7597D3FE (member_id), INDEX IDX_7304290A81C06096 (activity_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addForeignKey('member_activity_notification_sent', 'FK_7304290A7597D3FE', 'FOREIGN KEY (member_id) REFERENCES member (id) ON DELETE CASCADE');
        $this->addForeignKey('member_activity_notification_sent', 'FK_7304290A81C06096', 'FOREIGN KEY (activity_id) REFERENCES activity (id) ON DELETE CASCADE');

        if ($this->tableExists('activities')) {
            $this->connection->executeStatement(<<<'SQL'
                INSERT IGNORE INTO activity (id, creator_id, starts, ends, location_id, address, title, description, status,
                    online, created)
                SELECT a.id, a.creator, a.dateTimeStart, a.dateTimeEnd, a.locationId, a.address, a.title,
                    COALESCE(a.description, ''), a.status, a.public, NULL
                FROM activities a
                INNER JOIN member m ON m.id = a.creator
                INNER JOIN location l ON l.geoname_id = a.locationId
                SQL);
        }
        if ($this->tableExists('activitiesattendees')) {
            // INSERT IGNORE keeps the first answer of a member: the organizer's, else the latest
            $this->connection->executeStatement(<<<'SQL'
                INSERT IGNORE INTO activity_attendee (id, activity_id, attendee_id, organizer, status, comment)
                SELECT t.id, t.activityId, t.attendeeId, t.organizer, t.status, t.comment
                FROM activitiesattendees t
                INNER JOIN activity a ON a.id = t.activityId
                INNER JOIN member m ON m.id = t.attendeeId
                ORDER BY t.organizer DESC, t.id DESC
                SQL);
        }

        $this->connection->executeStatement(<<<'SQL'
            INSERT INTO preferences (position, codeName, codeDescription, Description, created, DefaultValue, PossibleValues, Status)
            SELECT 66, 'ActivitiesNotifications', 'activities.notifications', 'How often the member wants notifications for activities in their area', NOW(), 'Never', 'Never;Immediately;Daily;Weekly;Monthly', 'Normal'
            WHERE NOT EXISTS (SELECT 1 FROM preferences WHERE codeName = 'ActivitiesNotifications')
            SQL);
    }

    /**
     * Back to main's tables. What was written to activity and activity_attendee since is lost.
     */
    #[Override]
    public function down(Schema $schema): void
    {
        $this->connection->executeStatement("DELETE mp FROM memberspreferences mp INNER JOIN preferences p ON p.id = mp.IdPreference WHERE p.codeName = 'ActivitiesNotifications'");
        $this->connection->executeStatement("DELETE FROM preferences WHERE codeName = 'ActivitiesNotifications'");
        $this->connection->executeStatement('DROP TABLE IF EXISTS member_activity_notification_sent');
        $this->connection->executeStatement('DROP TABLE IF EXISTS activity_attendee');
        $this->connection->executeStatement('DROP TABLE IF EXISTS activity');
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
