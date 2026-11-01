<?php

namespace App\Tests\Controller;

use App\Entity\Member;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DomCrawler\Crawler;

/**
 * A changed English text keeps the text it had; a translation written before the change shows what changed, word by
 * word, on its page and in the side panel, until it is saved again.
 */
#[Group('integration')]
class TranslationChangeTest extends WebTestCase
{
    private KernelBrowser $client;
    private Connection $connection;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->connection = static::getContainer()->get(Connection::class);
        // bwadmin has the Words right for all languages in the fixtures
        $member = static::getContainer()->get(EntityManagerInterface::class)
            ->getRepository(Member::class)->findOneBy(['username' => 'bwadmin']);
        $this->client->loginUser($member);
        $this->word('en', 'Stay with hosts.');
        $this->word('de', 'Bleib bei Gastgebern.');
    }

    public function testATranslationShowsTheChangeOfTheEnglishTextUntilItIsSaved(): void
    {
        // nothing changed yet
        self::assertCount(0, $this->translationPage()->filter('.p-tr-diff'));

        $this->save('en', 'Stay with hosts for free.');
        self::assertSame('Stay with hosts.', $this->connection->fetchOne(
            "SELECT previous_sentence FROM word WHERE code = 'zztest.change' AND ShortCode = 'en'"
        ));

        foreach (['', '?embed=1'] as $query) {
            $diff = $this->translationPage($query)->filter('.p-tr-diff');
            self::assertCount(1, $diff, 'the change is shown' . $query);
            self::assertSame(['for free'], $diff->filter('ins')->each(static fn (Crawler $added): string => trim($added->text())));
            self::assertCount(0, $diff->filter('del'));
            self::assertStringContainsString('Stay with hosts for free.', $diff->filter('.p-tr-diff__text')->text());
        }

        $this->save('de', 'Bleib kostenlos bei Gastgebern.');
        self::assertCount(0, $this->translationPage()->filter('.p-tr-diff'), 'the translation follows the change now');
    }

    private function translationPage(string $query = ''): Crawler
    {
        $crawler = $this->client->request('GET', '/admin/translations/edit/de/zztest.change' . $query);
        $this->assertResponseIsSuccessful();

        return $crawler;
    }

    private function save(string $locale, string $text): void
    {
        $crawler = $this->client->request('GET', '/admin/translations/edit/' . $locale . '/zztest.change');
        $this->client->submit($crawler->filter('form[name=edit_translation_form]')->form([
            'edit_translation_form[translatedText]' => $text,
        ]));
        $this->assertResponseRedirects();
    }

    private function word(string $locale, string $sentence): void
    {
        $this->connection->executeStatement(
            "INSERT INTO word (code, ShortCode, domain, Sentence, created, updated, majorupdate, donottranslate, Description, TranslationPriority, isarchived)
             VALUES ('zztest.change', ?, 'messages', ?, NOW() - INTERVAL 2 DAY, NOW() - INTERVAL 2 DAY, '2000-01-01', 'no', 'test', 5, 0)",
            [$locale, $sentence]
        );
    }
}
