<?php

namespace App\Tests\Controller;

use App\Entity\Member;
use DateTimeImmutable;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * The translation tool's pages: the texts of a language by view (missing first) with their counts, a row per text
 * with its state, format and the change of its English text; the side panel with previous and next; the statistics
 * with the translator's languages on top; the mockups by feature.
 * The test texts (zztest.tool.*) are found by searching their codes: the fixtures have no translations.
 */
#[Group('integration')]
class TranslationToolPagesTest extends WebTestCase
{
    private KernelBrowser $client;
    private Connection $connection;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->connection = static::getContainer()->get(Connection::class);
        // bwadmin has the Words right for all languages in the fixtures
        $this->client->loginUser($this->member('bwadmin'));

        $this->word('zztest.tool.missing', 'en', 'A <b>new</b> text', created: '-1 day');
        $this->word('zztest.tool.outdated', 'en', 'Stay with hosts for free.', previous: 'Stay with hosts.', changed: '-1 day');
        $this->word('zztest.tool.outdated', 'de', 'Bleib bei Gastgebern.', created: '-3 days');
        $this->word('zztest.tool.done', 'en', 'Close', created: '-5 days');
        $this->word('zztest.tool.done', 'de', 'Schließen', created: '-2 days');
        $this->word('zztest.tool.name', 'en', 'BeWelcome', allowed: 'yes');
        $this->word('zztest.tool.old', 'en', 'Old text', archived: true);
    }

    public function testTheToolOpensWithWhatIsLeftToDo(): void
    {
        // the site in English: English has nothing missing
        $this->client->request('GET', '/admin/translations');
        $this->assertResponseRedirects('/admin/translations/all/en');

        $this->client->loginUser($this->translator('de'));
        $this->client->request('GET', '/admin/translations');
        $this->assertResponseRedirects('/admin/translations/missing/de');
    }

    public function testEveryViewHasItsTexts(): void
    {
        $crawler = $this->client->request('GET', '/admin/translations/missing/de/zztest.tool');

        $this->assertResponseIsSuccessful();
        self::assertSame(
            ['missing' => '1', 'update' => '1', 'all' => '3', 'donottranslate' => '1', 'archived' => '1'],
            array_combine(
                ['missing', 'update', 'all', 'donottranslate', 'archived'],
                $crawler->filter('.p-tr-chip .p-tr-chip__count')->each(static fn (Crawler $count): string => $count->text())
            )
        );
        $missing = $this->row($crawler, 'zztest.tool.missing');
        self::assertSame('A new text', $missing->filter('.p-tr-row__english .p-tr-row__text')->text(), 'without markup');
        self::assertSame($this->trans('translation.tool.format.inline'), $missing->filter('.p-tr-format')->text());
        self::assertStringEndsWith('/admin/translations/add/de/zztest.tool.missing', $missing->filter('.js-tool-open')->attr('href'));

        $crawler = $this->client->request('GET', '/admin/translations/all/de/zztest.tool');
        // in any order
        self::assertEquals(
            ['zztest.tool.outdated' => 'update', 'zztest.tool.missing' => 'missing', 'zztest.tool.done' => 'ok'],
            $this->states($crawler)
        );
        $outdated = $this->row($crawler, 'zztest.tool.outdated');
        self::assertSame(['for free'], $outdated->filter('ins')->each(static fn (Crawler $added): string => trim($added->text())));
        self::assertSame('Bleib bei Gastgebern.', $outdated->filter('.js-translation-text')->text());

        $crawler = $this->client->request('GET', '/admin/translations/donottranslate/de/zztest.tool');
        self::assertSame(['zztest.tool.name' => 'dnt'], $this->states($crawler));
        self::assertCount(0, $crawler->filter('.js-tool-open[href*="zztest"]'), 'nothing to translate');
    }

    public function testASearchWithoutResultsOffersTheViewsWithSome(): void
    {
        $crawler = $this->client->request('GET', '/admin/translations/missing/de/zztest.tool.old');

        $this->assertResponseIsSuccessful();
        self::assertCount(1, $crawler->filter('.p-tr-empty'));
        self::assertStringEndsWith(
            '/admin/translations/archived/de/zztest.tool.old',
            $crawler->filter('.p-tr-empty a.p-tr-button--primary')->attr('href')
        );

        // the search field puts the code into the address
        $this->client->request('GET', '/admin/translations/missing/de?q=zztest.tool');
        $this->assertResponseRedirects('/admin/translations/missing/de/zztest.tool');
    }

    public function testTheSidePanelHasThePreviousAndTheNextText(): void
    {
        $crawler = $this->client->request('GET', '/admin/translations/edit/de/zztest.tool.done?embed=1&position=2&total=3');
        $this->assertResponseIsSuccessful();
        self::assertSame($this->trans('translation.tool.position', ['position' => 2, 'total' => 3]), trim($crawler->filter('.p-tr-steps__position')->text()));
        self::assertCount(0, $crawler->filter('.js-ped-close.p-ped-head__close'), 'previous and next instead of closing');
        self::assertSame($this->trans('translation.tool.save.next'), trim($crawler->filter('form[name=edit_translation_form] button[type=submit]')->text()));

        $crawler = $this->client->request('GET', '/admin/translations/edit/de/zztest.tool.done?embed=1&position=3&total=3');
        self::assertNotSame($this->trans('translation.tool.save.next'), trim($crawler->filter('form[name=edit_translation_form] button[type=submit]')->text()));
        self::assertNotNull($crawler->filter('.js-translation-step[data-step="1"]')->attr('disabled'));
    }

    public function testTheStatisticsPinTheTranslatorsLanguages(): void
    {
        $this->client->loginUser($this->translator('de'));
        $crawler = $this->client->request('GET', '/admin/translate/statistics');

        $this->assertResponseIsSuccessful();
        $sections = $crawler->filter('.p-tr-stats');
        self::assertCount(2, $sections);
        self::assertSame(['de'], $this->locales($sections->eq(0)));
        self::assertSame('en', $this->locales($sections->eq(1))[0], 'the source first');

        $crawler = $this->client->request('GET', '/admin/translate/statistics?sort=name');
        $byName = $this->locales($crawler->filter('.p-tr-stats')->eq(1));
        $sorted = $byName;
        sort($sorted);
        self::assertSame($sorted, $byName);
    }

    public function testTheMockupsShowAFeaturesPreviews(): void
    {
        $crawler = $this->client->request('GET', '/admin/translations/mockups/signups');

        $this->assertResponseIsSuccessful();
        self::assertStringContainsString('Signups', $crawler->filter('.p-tr-features__item.is-active')->text());
        self::assertGreaterThan(0, $crawler->filter('.p-tr-preview')->count());
        self::assertGreaterThan(0, $crawler->filter('.p-tr-preview a[href*="/admin/translate/mockup/signups/"]')->count());

        $this->client->request('GET', '/admin/translations/mockups/nothing');
        $this->assertResponseRedirects('/admin/translations/mockups');
    }

    /**
     * @return array<string, string> code => state of the rows
     */
    private function states(Crawler $crawler): array
    {
        $states = [];
        $crawler->filter('.p-tr-row')->each(static function (Crawler $row) use (&$states): void {
            preg_match('/p-tr-row--(\S+)/', (string) $row->attr('class'), $state);
            $states[(string) $row->attr('data-code')] = $state[1];
        });

        return $states;
    }

    private function row(Crawler $crawler, string $code): Crawler
    {
        $row = $crawler->filter('.p-tr-row[data-code="' . $code . '"]');
        self::assertCount(1, $row, $code);

        return $row;
    }

    /**
     * @return list<string>
     */
    private function locales(Crawler $section): array
    {
        return $section->filter('.p-tr-stat__native')->each(
            static fn (Crawler $native): string => trim(substr(strrchr($native->text(), '·'), \strlen('·')))
        );
    }

    private function translator(string $scope): Member
    {
        $member = $this->member('member-2');
        $this->connection->executeStatement(
            "INSERT INTO rightsvolunteers (IdMember, IdRight, Level, Scope, Comment, created, updated)
             SELECT ?, r.id, 10, ?, 'test', NOW(), NOW() FROM rights r WHERE r.Name = 'Words'",
            [$member->getId(), '"' . $scope . '"']
        );
        static::getContainer()->get(EntityManagerInterface::class)->refresh($member);

        return $member;
    }

    /**
     * @param array<string, int> $parameters
     */
    private function trans(string $id, array $parameters = []): string
    {
        return static::getContainer()->get(TranslatorInterface::class)->trans($id, $parameters);
    }

    private function member(string $username): Member
    {
        $member = static::getContainer()->get(EntityManagerInterface::class)
            ->getRepository(Member::class)->findOneBy(['username' => $username]);
        self::assertInstanceOf(Member::class, $member);

        return $member;
    }

    private function word(
        string $code,
        string $locale,
        string $sentence,
        string $created = '-10 days',
        ?string $previous = null,
        ?string $changed = null,
        string $allowed = 'no',
        bool $archived = false,
    ): void {
        $at = static fn (string $when): string => new DateTimeImmutable($when)->format('Y-m-d H:i:s');
        $this->connection->executeStatement(
            "INSERT INTO word (code, ShortCode, domain, Sentence, created, updated, majorupdate, donottranslate, Description,
                TranslationPriority, isarchived, previous_sentence, sentence_changed)
             VALUES (?, ?, 'messages', ?, ?, ?, NULL, ?, 'test', 5, ?, ?, ?)",
            [$code, $locale, $sentence, $at($created), $at($changed ?? $created), $allowed, $archived ? 1 : 0, $previous, null === $changed ? null : $at($changed)]
        );
    }
}
