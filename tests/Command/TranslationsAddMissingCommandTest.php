<?php

namespace App\Tests\Command;

use Doctrine\DBAL\Connection;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * translations:add:missing adds the texts of translations/missing that the database doesn't have yet.
 */
#[Group('integration')]
class TranslationsAddMissingCommandTest extends KernelTestCase
{
    public function testAddsAMissingTextAsNotArchived(): void
    {
        $kernel = self::bootKernel();
        $connection = static::getContainer()->get(Connection::class);
        $connection->executeStatement("DELETE FROM word WHERE code = 'activities.empty.near'");

        $tester = new CommandTester(new Application($kernel)->find('translations:add:missing'));

        self::assertSame(Command::SUCCESS, $tester->execute([]));
        self::assertSame(
            ['ShortCode' => 'en', 'isarchived' => 0, 'domain' => 'messages+intl-icu'],
            $connection->fetchAssociative(
                "SELECT ShortCode, isarchived, domain FROM word WHERE code = 'activities.empty.near'"
            )
        );
    }
}
