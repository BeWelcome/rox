<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Override;

/**
 * The message thread query used stored functions (created on production by the old migrations);
 * App\Utilities\ConversationThread walks the thread with a recursive CTE instead (rox#545).
 *
 * This migration used to create the functions. It now drops them where they exist, so converting
 * the production database removes them; a database that ran the old version keeps unused functions.
 *
 * @SuppressWarnings("PHPMD")
 */
final class Version20200307133359 extends AbstractMigration
{
    private const array FUNCTIONS = [
        'hierarchy_connect_by_parent_eq_prior_id',
        'hierarchy_connect_by_parent_eq_prior_id_with_level',
        'hierarchy_sys_connect_by_path',
    ];

    #[Override]
    public function getDescription(): string
    {
        return 'Drop the stored functions of the old message thread query';
    }

    public function up(Schema $schema): void
    {
        foreach (self::FUNCTIONS as $function) {
            $this->addSql('DROP FUNCTION IF EXISTS ' . $function);
        }
    }

    #[Override]
    public function down(Schema $schema): void
    {
        // Nothing to restore: no code uses the functions any more.
    }
}
