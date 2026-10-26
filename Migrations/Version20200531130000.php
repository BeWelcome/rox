<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Override;

/**
 * This migration rebuilt geo__names_translations in the layout of LocationTranslation.
 *
 * main uses geo__names and geo__names_translations as they are, and deploying this code must not change anything main relies on. The places
 * have tables of their own now, location and location_translation (Version20200531140000).
 */
final class Version20200531130000 extends AbstractMigration
{
    #[Override]
    public function getDescription(): string
    {
        return 'Keep geo__names_translations, which main uses (LocationTranslation maps location_translation now)';
    }

    public function up(Schema $schema): void
    {
        $this->write('Nothing to do: geo__names and geo__names_translations stay as main uses them; see Version20200531140000.');
    }

    #[Override]
    public function down(Schema $schema): void
    {
    }
}
