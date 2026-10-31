<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Override;

/**
 * develop's `feedback` (App\Entity\Feedback), filled from main's `feedbacks`, ids kept:
 * - snake_case column names;
 * - the language is the ShortCode of develop's `language` (main stores languages.id in IdLanguage);
 * - authors missing from `member` and categories that don't exist are cleared.
 *
 * `feedbacks` stays: main reads and writes it.
 */
final class Version20261031100000 extends AbstractMigration
{
    private const array FOREIGN_KEYS = [
        'FK_D2294458F675F31B' => 'FOREIGN KEY (author_id) REFERENCES member (id)',
        'FK_D2294458D4DB71B5' => 'FOREIGN KEY (language) REFERENCES language (ShortCode)',
        'FK_D229445812469DE2' => 'FOREIGN KEY (category_id) REFERENCES feedbackcategories (id)',
    ];

    #[Override]
    public function getDescription(): string
    {
        return 'Create feedback and copy main\'s feedbacks into it (snake_case columns, language by ShortCode).';
    }

    #[Override]
    public function isTransactional(): bool
    {
        // CREATE TABLE commits implicitly.
        return false;
    }

    public function up(Schema $schema): void
    {
        $this->connection->executeStatement('CREATE TABLE IF NOT EXISTS feedback (updated DATETIME NOT NULL, created DATETIME NOT NULL, discussion TEXT NOT NULL, id INT AUTO_INCREMENT NOT NULL, author_id INT DEFAULT NULL, language VARCHAR(16) NOT NULL, category_id INT DEFAULT NULL, INDEX IDX_D2294458F675F31B (author_id), INDEX IDX_D2294458D4DB71B5 (language), INDEX IDX_D229445812469DE2 (category_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');

        if ($this->columnExists('feedbacks', 'id') && !$this->connection->fetchOne('SELECT 1 FROM feedback LIMIT 1')) {
            $this->copyFeedbacks();
        }

        foreach (self::FOREIGN_KEYS as $name => $definition) {
            if (!$this->foreignKeyExists('feedback', $name)) {
                $this->connection->executeStatement(\sprintf('ALTER TABLE feedback ADD CONSTRAINT %s %s', $name, $definition));
            }
        }
    }

    #[Override]
    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE IF EXISTS feedback');
    }

    private function copyFeedbacks(): void
    {
        // The short codes are compared in binary: main's and develop's tables use different collations.
        // main's table has IdLanguage (languages.id); a database built from develop's entities has ShortCode.
        if ($this->columnExists('feedbacks', 'IdLanguage')) {
            $languageJoin = 'LEFT JOIN languages ol ON ol.id = f.IdLanguage LEFT JOIN language l ON CONVERT(l.ShortCode USING utf8mb4) COLLATE utf8mb4_bin = CONVERT(ol.ShortCode USING utf8mb4) COLLATE utf8mb4_bin';
        } else {
            $languageJoin = 'LEFT JOIN language l ON CONVERT(l.ShortCode USING utf8mb4) COLLATE utf8mb4_bin = CONVERT(f.ShortCode USING utf8mb4) COLLATE utf8mb4_bin';
        }

        // Zero dates (from main's old rows) are compared with a real date rather than with '0000-00-00', which
        // strict SQL modes refuse.
        $this->connection->executeStatement(<<<SQL
            INSERT IGNORE INTO feedback (id, created, updated, discussion, author_id, language, category_id)
            SELECT
                f.id,
                IF(f.created > '1970-01-02', f.created, IF(f.updated > '1970-01-02', f.updated, NOW())),
                IF(f.updated > '1970-01-02', f.updated, IF(f.created > '1970-01-02', f.created, NOW())),
                f.Discussion,
                m.id,
                COALESCE(l.ShortCode, 'en'),
                c.id
            FROM feedbacks f
            {$languageJoin}
            LEFT JOIN member m ON m.id = f.IdMember
            LEFT JOIN feedbackcategories c ON c.id = f.IdFeedbackCategory
            SQL);
    }

    private function columnExists(string $table, string $column): bool
    {
        return (bool) $this->connection->fetchOne(
            'SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = ? AND column_name = ?',
            [$table, $column],
        );
    }

    private function foreignKeyExists(string $table, string $name): bool
    {
        return (bool) $this->connection->fetchOne(
            'SELECT COUNT(*) FROM information_schema.referential_constraints
             WHERE constraint_schema = DATABASE() AND table_name = ? AND constraint_name = ?',
            [$table, $name],
        );
    }
}
