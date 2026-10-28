<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Override;

final class Version20261028120000 extends AbstractMigration
{
    #[Override]
    public function getDescription(): string
    {
        return 'Create group_translation (App\Entity\GroupTranslation) and fill it with the group descriptions: '
            . 'the ones main shows (memberstrads rows of groups.IdDescription, the newest per language) and the '
            . 'ones develop wrote as member_translation rows linked through groups_trads.';
    }

    public function up(Schema $schema): void
    {
        $this->connection->executeStatement('CREATE TABLE IF NOT EXISTS group_translation (id INT AUTO_INCREMENT NOT NULL, locale VARCHAR(8) NOT NULL, field VARCHAR(32) NOT NULL, content LONGTEXT DEFAULT NULL, created_at DATETIME DEFAULT NULL, updated_at DATETIME DEFAULT NULL, object_id INT DEFAULT NULL, translator_id INT DEFAULT NULL, UNIQUE INDEX lookup_unique_idx (locale, object_id, field), INDEX IDX_91D15AAE232D562B (object_id), INDEX IDX_91D15AAE5370E40B (translator_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addConstraintIfMissing('FK_91D15AAE232D562B', 'ALTER TABLE group_translation ADD CONSTRAINT FK_91D15AAE232D562B FOREIGN KEY (object_id) REFERENCES `groups` (id) ON DELETE CASCADE');
        $this->addConstraintIfMissing('FK_91D15AAE5370E40B', 'ALTER TABLE group_translation ADD CONSTRAINT FK_91D15AAE5370E40B FOREIGN KEY (translator_id) REFERENCES member (id) ON DELETE SET NULL');

        if ($this->tableExists('memberstrads') && $this->tableExists('languages')) {
            // As main's MOD_words::mTrad() shows them: the rows of the group's IdDescription, the newest per language
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
                    JOIN memberstrads mt ON mt.TableColumn = 'groups.IdDescription' AND mt.IdTrad = g.IdDescription
                    JOIN languages l ON l.id = mt.IdLanguage
                    WHERE g.IdDescription > 0 AND mt.Sentence IS NOT NULL AND mt.Sentence != ''
                ) d
                LEFT JOIN member m ON m.id = d.IdTranslator
                WHERE d.newest = 1
                SQL);
        }

        if ($this->tableExists('groups_trads') && $this->tableExists('member_translation')) {
            // Written by develop (App\Model\GroupModel, the legacy group settings): member_translation rows
            // without a member, linked through groups_trads. They are newer than main's.
            $this->connection->executeStatement(<<<'SQL'
                INSERT INTO group_translation (object_id, locale, field, content, created_at)
                SELECT gt.group_id, t.locale, 'description', t.content, NOW()
                FROM groups_trads gt
                JOIN `groups` g ON g.id = gt.group_id
                JOIN member_translation t ON t.id = gt.trad_id AND t.object_id IS NULL AND t.field = 'GroupDescription'
                WHERE t.content IS NOT NULL AND t.content != ''
                ON DUPLICATE KEY UPDATE content = VALUES(content), updated_at = NOW()
                SQL);
        }

        // groups.IdDescription, groups_trads and memberstrads stay: main reads and writes the descriptions there.
    }

    #[Override]
    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE IF EXISTS group_translation');
    }

    private function tableExists(string $table): bool
    {
        return (bool) $this->connection->fetchOne(
            'SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = ?',
            [$table],
        );
    }

    private function addConstraintIfMissing(string $constraintName, string $sql): void
    {
        $exists = $this->connection->fetchOne(
            'SELECT COUNT(*) FROM information_schema.table_constraints
             WHERE constraint_schema = DATABASE() AND table_name = ? AND constraint_name = ?',
            ['group_translation', $constraintName],
        );
        if ((int) $exists > 0) {
            return;
        }
        $this->connection->executeStatement($sql);
    }
}
