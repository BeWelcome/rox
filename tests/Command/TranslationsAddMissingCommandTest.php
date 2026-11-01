<?php

namespace App\Tests\Command;

use Doctrine\DBAL\Connection;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * translations:add:missing adds the texts of translations/missing that the database doesn't have yet.
 */
#[Group('integration')]
class TranslationsAddMissingCommandTest extends KernelTestCase
{
    public function testAddsAMissingTextAsNotArchived(): void
    {
        $connection = $this->connection();
        $connection->executeStatement("DELETE FROM word WHERE code = 'activities.empty.near'");

        self::assertSame(Command::SUCCESS, $this->addMissing());
        self::assertSame(
            // no author: added by the system
            ['ShortCode' => 'en', 'isarchived' => 0, 'domain' => 'messages+intl-icu', 'author_id' => null],
            $connection->fetchAssociative(
                "SELECT ShortCode, isarchived, domain, author_id FROM word WHERE code = 'activities.empty.near'"
            )
        );
    }

    /**
     * translations/missing/translation.yaml gives translation.format.inline with names and "format: plain";
     * translation.in.place.edit is a list: its format is detected from the text.
     */
    public function testAddsTheFormatOfATextGivenWithNames(): void
    {
        $connection = $this->connection();
        $connection->executeStatement("DELETE FROM word WHERE code IN ('translation.format.inline', 'translation.in.place.edit')");

        self::assertSame(Command::SUCCESS, $this->addMissing());
        self::assertSame(
            ['Sentence' => 'Inline HTML: links or bold within a line, no paragraph', 'format' => 'plain'],
            $connection->fetchAssociative("SELECT Sentence, format FROM word WHERE code = 'translation.format.inline'")
        );
        self::assertNull($connection->fetchOne("SELECT format FROM word WHERE code = 'translation.in.place.edit'"));
    }

    private function connection(): Connection
    {
        self::bootKernel();

        return static::getContainer()->get(Connection::class);
    }

    private function addMissing(): int
    {
        $application = new Application(self::$kernel);
        $command = $application->find('translations:add:missing'); // registers the bundles' commands first
        // The command clears the cache after adding texts; in a test that deletes the running test container
        $application->addCommand(new #[AsCommand(name: 'cache:clear')] class extends Command {
            protected function execute(InputInterface $input, OutputInterface $output): int
            {
                return Command::SUCCESS;
            }
        });
        $tester = new CommandTester($command);

        return $tester->execute([]);
    }
}
