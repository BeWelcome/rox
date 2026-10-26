<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Override;

/**
 * The message thread query of main uses stored functions (hierarchy_connect_by_parent_eq_prior_id and
 * others, created on production by the old migrations). App\Utilities\ConversationThread walks the thread
 * with a recursive CTE instead (rox#545), so this code doesn't need them.
 *
 * This migration used to create the functions. It now leaves them as they are: main still uses them on
 * production, and deploying this code must not remove anything main relies on. They are dropped when main
 * is replaced by this code.
 *
 * @SuppressWarnings("PHPMD")
 */
final class Version20200307133359 extends AbstractMigration
{
    #[Override]
    public function getDescription(): string
    {
        return 'Keep the stored functions of the old message thread query, which main still uses';
    }

    public function up(Schema $schema): void
    {
        $this->write('Nothing to do: the stored functions stay until main is replaced.');
    }

    #[Override]
    public function down(Schema $schema): void
    {
    }
}
