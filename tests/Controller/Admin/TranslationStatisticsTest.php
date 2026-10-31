<?php

namespace App\Tests\Controller\Admin;

use App\Entity\Member;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * The translation statistics show who changed each language last: the system (author 0, which has no member row), a
 * member, or nobody known (a member that doesn't exist any more).
 */
#[Group('integration')]
class TranslationStatisticsTest extends WebTestCase
{
    public function testTheLastChangesOfTheSystemAndOfMissingMembersAreShown(): void
    {
        $client = static::createClient();
        $connection = static::getContainer()->get(Connection::class);
        $admin = static::getContainer()->get(EntityManagerInterface::class)
            ->getRepository(Member::class)->findOneBy(['username' => 'bwadmin']);

        // word.author_id references member; author 0 and deleted members are only there in copied data
        $connection->executeStatement('SET FOREIGN_KEY_CHECKS=0');
        foreach ([['en', $admin->getId()], ['de', 0], ['fr', 999999]] as [$locale, $authorId]) {
            $connection->executeStatement(
                "INSERT INTO word (code, domain, Sentence, created, updated, donottranslate, Description,
                    TranslationPriority, isarchived, ShortCode, author_id)
                 VALUES ('zztest_statistics', 'messages', 'Text', NOW(), '2099-01-01', 'no', '', 5, 0, ?, ?)",
                [$locale, $authorId]
            );
        }
        $connection->executeStatement('SET FOREIGN_KEY_CHECKS=1');

        // bwadmin has the Words right in the fixtures
        $client->loginUser($admin);
        $crawler = $client->request('GET', '/admin/translate/statistics');

        $this->assertResponseIsSuccessful();
        $translator = static::getContainer()->get(TranslatorInterface::class);
        self::assertStringContainsString($admin->getUsername(), $this->row($crawler, 'en')->text());
        self::assertStringContainsString($translator->trans('system'), $this->row($crawler, 'de')->text());
        self::assertStringContainsString($translator->trans('translation.unknown.member'), $this->row($crawler, 'fr')->text());
    }

    private function row(Crawler $crawler, string $locale): Crawler
    {
        return $crawler->filter('tr')->reduce(
            static fn (Crawler $row): bool => str_contains($row->text(), '(' . $locale . ')')
        );
    }
}
