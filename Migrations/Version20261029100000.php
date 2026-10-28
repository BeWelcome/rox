<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Override;

/**
 * develop's group tables, filled from main's:
 * - `group` (App\Entity\Group) from `groups` (App\Entity\Groups), ids kept;
 * - `group_translation` (App\Entity\GroupTranslation): the descriptions, from memberstrads;
 * - `group_membership` (App\Entity\GroupMembership) from `membersgroups`, the comment as text.
 *
 * `groups`, `membersgroups`, `groups_trads` and `memberstrads` stay: main reads and writes them.
 */
final class Version20261029100000 extends AbstractMigration
{
    #[Override]
    public function getDescription(): string
    {
        return 'Create group, group_translation and group_membership and copy main\'s groups, their descriptions '
            . 'and the memberships into them; point groups_related, poll_group and polls_list_allowed_groups at group.';
    }

    public function up(Schema $schema): void
    {
        $this->connection->executeStatement('SET FOREIGN_KEY_CHECKS=0');

        $this->connection->executeStatement("CREATE TABLE IF NOT EXISTS `group` (name VARCHAR(100) NOT NULL, type VARCHAR(14) NOT NULL, created DATETIME NOT NULL, picture VARCHAR(255) DEFAULT '' NOT NULL, members_only TINYINT DEFAULT 0 NOT NULL, approved SMALLINT DEFAULT 0 NOT NULL, id INT AUTO_INCREMENT NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4");
        $this->connection->executeStatement('CREATE TABLE IF NOT EXISTS group_translation (id INT AUTO_INCREMENT NOT NULL, locale VARCHAR(8) NOT NULL, field VARCHAR(32) NOT NULL, content LONGTEXT DEFAULT NULL, created_at DATETIME DEFAULT NULL, updated_at DATETIME DEFAULT NULL, object_id INT DEFAULT NULL, translator_id INT DEFAULT NULL, UNIQUE INDEX lookup_unique_idx (locale, object_id, field), INDEX IDX_91D15AAE232D562B (object_id), INDEX IDX_91D15AAE5370E40B (translator_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->connection->executeStatement('CREATE TABLE IF NOT EXISTS group_membership (id INT AUTO_INCREMENT NOT NULL, status VARCHAR(10) NOT NULL, comment LONGTEXT DEFAULT NULL, accept_mass_mail TINYINT DEFAULT 0 NOT NULL, notifications_enabled TINYINT DEFAULT 1 NOT NULL, created DATETIME NOT NULL, updated DATETIME NOT NULL, member_id INT NOT NULL, group_id INT NOT NULL, UNIQUE INDEX group_membership_unique (member_id, group_id), INDEX IDX_5132B3377597D3FE (member_id), INDEX IDX_5132B337FE54D947 (group_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');

        $this->addForeignKey('group_translation', 'FK_91D15AAE232D562B', 'FOREIGN KEY (object_id) REFERENCES `group` (id) ON DELETE CASCADE');
        $this->addForeignKey('group_translation', 'FK_91D15AAE5370E40B', 'FOREIGN KEY (translator_id) REFERENCES member (id) ON DELETE SET NULL');
        $this->addForeignKey('group_membership', 'FK_5132B3377597D3FE', 'FOREIGN KEY (member_id) REFERENCES member (id) ON DELETE CASCADE');
        $this->addForeignKey('group_membership', 'FK_5132B337FE54D947', 'FOREIGN KEY (group_id) REFERENCES `group` (id) ON DELETE CASCADE');

        // The tables develop shares with main point at `group` now (the ids are the same).
        $this->addForeignKey('groups_related', 'FK_79EAAC25FE54D947', 'FOREIGN KEY (group_id) REFERENCES `group` (id) ON DELETE CASCADE');
        $this->addForeignKey('groups_related', 'FK_79EAAC254162C001', 'FOREIGN KEY (related_id) REFERENCES `group` (id) ON DELETE CASCADE');
        $this->addForeignKey('poll_group', 'FK_B713904FE54D947', 'FOREIGN KEY (group_id) REFERENCES `group` (id) ON DELETE CASCADE');
        $this->addForeignKey('polls_list_allowed_groups', 'FK_B72F84B5B93E44', 'FOREIGN KEY (IdGroup) REFERENCES `group` (id)');

        if ($this->tableExists('groups')) {
            $this->copyGroups();
        }
        if ($this->tableExists('memberstrads') && $this->tableExists('languages')) {
            $this->copyDescriptionsOfMain();
        }
        if ($this->tableExists('groups_trads') && $this->tableExists('member_translation')) {
            $this->copyDescriptionsOfDevelop();
        }
        if ($this->tableExists('membersgroups')) {
            $this->copyMemberships();
        }

        $this->connection->executeStatement('SET FOREIGN_KEY_CHECKS=1');
    }

    #[Override]
    public function down(Schema $schema): void
    {
        $this->addSql('SET FOREIGN_KEY_CHECKS=0');
        $this->addSql('DROP TABLE IF EXISTS group_membership');
        $this->addSql('DROP TABLE IF EXISTS group_translation');
        $this->addSql('DROP TABLE IF EXISTS `group`');
        $this->addSql('SET FOREIGN_KEY_CHECKS=1');
    }

    private function copyGroups(): void
    {
        // VisiblePosts 'no': the group's posts are for its members only ('yes' and '' for all members)
        $this->connection->executeStatement(<<<'SQL'
            INSERT IGNORE INTO `group` (id, name, type, created, picture, members_only, approved)
            SELECT g.id, g.Name, g.Type, g.created, COALESCE(g.Picture, ''), g.VisiblePosts = 'no', COALESCE(g.approved, 0)
            FROM `groups` g
            SQL);
    }

    /**
     * The descriptions as main's MOD_words::mTrad() shows them: the memberstrads rows of the group's IdDescription,
     * the newest per language.
     */
    private function copyDescriptionsOfMain(): void
    {
        $this->connection->executeStatement(<<<'SQL'
            INSERT IGNORE INTO group_translation (object_id, locale, field, content, translator_id, created_at, updated_at)
            SELECT d.group_id, d.locale, 'description', d.content, m.id,
                NULLIF(d.created, '0000-00-00 00:00:00'),
                IF(d.updated > d.created, d.updated, NULL)
            FROM (
                SELECT g.id AS group_id, l.ShortCode AS locale, mt.Sentence AS content, mt.IdTranslator,
                    mt.created, mt.updated,
                    ROW_NUMBER() OVER (PARTITION BY g.id, l.ShortCode ORDER BY mt.updated DESC, mt.id DESC) AS newest
                FROM `groups` g
                JOIN `group` ng ON ng.id = g.id
                JOIN memberstrads mt ON mt.TableColumn = 'groups.IdDescription' AND mt.IdTrad = g.IdDescription
                JOIN languages l ON l.id = mt.IdLanguage
                WHERE g.IdDescription > 0 AND mt.Sentence IS NOT NULL AND mt.Sentence != ''
            ) d
            LEFT JOIN member m ON m.id = d.IdTranslator
            WHERE d.newest = 1
            SQL);
    }

    /**
     * Written by develop before this migration (App\Model\GroupModel, the legacy group settings): member_translation
     * rows without a member, linked through groups_trads. They are newer than main's.
     */
    private function copyDescriptionsOfDevelop(): void
    {
        $this->connection->executeStatement(<<<'SQL'
            INSERT INTO group_translation (object_id, locale, field, content, created_at)
            SELECT gt.group_id, t.locale, 'description', t.content, NOW()
            FROM groups_trads gt
            JOIN `group` g ON g.id = gt.group_id
            JOIN member_translation t ON t.id = gt.trad_id AND t.object_id IS NULL AND t.field = 'GroupDescription'
            WHERE t.content IS NOT NULL AND t.content != ''
            ON DUPLICATE KEY UPDATE content = VALUES(content), updated_at = NOW()
            SQL);
    }

    /**
     * The memberships of existing groups and members. The comment is one text: main's newest memberstrads row of
     * membersgroups.Comment, else the member_translation row develop wrote for it.
     */
    private function copyMemberships(): void
    {
        $mainComments = $this->tableExists('memberstrads')
            ? <<<'SQL'
                LEFT JOIN (
                    SELECT IdTrad, Sentence,
                        ROW_NUMBER() OVER (PARTITION BY IdTrad ORDER BY updated DESC, id DESC) AS newest
                    FROM memberstrads
                    -- main's code saved the id of the text as text in about 4000 rows: no comment
                    WHERE TableColumn = 'membersgroups.Comment' AND Sentence IS NOT NULL AND Sentence != ''
                        AND Sentence != CAST(IdTrad AS CHAR)
                ) mc ON mc.IdTrad = mg.Comment AND mc.newest = 1
                SQL
            : 'LEFT JOIN (SELECT NULL AS IdTrad, NULL AS Sentence) mc ON FALSE';
        $developComments = $this->tableExists('member_translation')
            ? "LEFT JOIN member_translation dc ON dc.id = mg.Comment AND dc.object_id IS NULL AND dc.field IN ('GroupMembership', 'GroupComment')"
            : 'LEFT JOIN (SELECT NULL AS content) dc ON FALSE';

        $this->connection->executeStatement(<<<SQL
            INSERT IGNORE INTO group_membership
                (member_id, group_id, status, comment, accept_mass_mail, notifications_enabled, created, updated)
            SELECT mg.IdMember, mg.IdGroup, mg.Status, NULLIF(COALESCE(mc.Sentence, dc.content), ''),
                mg.IacceptMassMailFromThisGroup = 'yes', mg.notificationsEnabled = 1,
                COALESCE(NULLIF(mg.created, '0000-00-00 00:00:00'), mg.updated), mg.updated
            FROM membersgroups mg
            JOIN `group` g ON g.id = mg.IdGroup
            JOIN member m ON m.id = mg.IdMember
            {$mainComments}
            {$developComments}
            SQL);
    }

    private function tableExists(string $table): bool
    {
        return (bool) $this->connection->fetchOne(
            'SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = ?',
            [$table],
        );
    }

    /**
     * Adds the foreign key; one of the same name pointing elsewhere (at `groups`) is dropped first.
     */
    private function addForeignKey(string $table, string $name, string $definition): void
    {
        if (!$this->tableExists($table)) {
            return;
        }
        $references = $this->connection->fetchOne(
            'SELECT referenced_table_name FROM information_schema.referential_constraints
             WHERE constraint_schema = DATABASE() AND table_name = ? AND constraint_name = ?',
            [$table, $name],
        );
        $wanted = str_contains($definition, 'REFERENCES member ') ? 'member' : 'group';
        if ($references === $wanted) {
            return;
        }
        if (false !== $references) {
            $this->connection->executeStatement(\sprintf('ALTER TABLE `%s` DROP FOREIGN KEY `%s`', $table, $name));
        }
        $this->connection->executeStatement(\sprintf('ALTER TABLE `%s` ADD CONSTRAINT `%s` %s', $table, $name, $definition));
    }
}
