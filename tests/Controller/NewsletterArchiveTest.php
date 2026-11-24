<?php

namespace App\Tests\Controller;

use App\Entity\Member;
use App\Entity\Newsletter;
use App\Model\NewsletterArchiveModel;
use App\Repository\NewsletterRepository;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * The newsletter archive and a single newsletter: the languages, the redirect for a language a newsletter has no text
 * in, the older and newer newsletters and the way back to the archive page. The newsletters of a test are dated in
 * the future, so they are the newest.
 */
#[Group('integration')]
final class NewsletterArchiveTest extends WebTestCase
{
    private KernelBrowser $client;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->client->disableReboot();
    }

    public function testTheArchiveGroupsByYearAndOpensTheMembersLocaleOrTheFirstLanguage(): void
    {
        $withoutEnglish = $this->newsletter('zztest_a', '2101-03-05', ['de', 'fr']);
        $withEnglish = $this->newsletter('zztest_b', '2100-07-01', ['en', 'de']);
        $this->newsletter('zztest_c', '2100-01-01', []);

        $crawler = $this->client->request('GET', '/newsletters/1');

        self::assertResponseIsSuccessful();
        $published = $this->entityManager()->getRepository(Newsletter::class);
        \assert($published instanceof NewsletterRepository);
        $all = $published->findAllPublished();
        self::assertSame(
            $this->trans('newsletters.count', ['count' => \count($all), 'year' => (int) end($all)->getCreated()->format('Y')]),
            $crawler->filter('.c-newsletters__count')->text(),
        );
        self::assertSame(['2101', '2100'], \array_slice($crawler->filter('.c-newsletters__year-title')->each(static fn ($h) => $h->text()), 0, 2));

        $cards = $crawler->filter('.c-newsletter-card');
        // without English the title opens the first language, in the database's order
        self::assertSame('/newsletters/' . $withoutEnglish->getId() . '/de', $cards->eq(0)->filter('.c-newsletter-card__title a')->attr('href'));
        self::assertCount(0, $cards->eq(0)->filter('.c-newsletter-lang.is-active'));
        self::assertSame('/newsletters/' . $withEnglish->getId() . '/en', $cards->eq(1)->filter('.c-newsletter-card__title a')->attr('href'));
        self::assertSame(
            ['/newsletters/' . $withEnglish->getId() . '/de', '/newsletters/' . $withEnglish->getId() . '/en'],
            $cards->eq(1)->filter('.c-newsletter-lang')->each(static fn ($a) => $a->attr('href')),
        );
        self::assertSame('/newsletters/' . $withEnglish->getId() . '/en', $cards->eq(1)->filter('.c-newsletter-lang.is-active')->attr('href'));
        self::assertStringEndsWith($this->trans('newsletters.languages', ['count' => 2]), $cards->eq(1)->filter('.c-newsletter-card__meta')->text());
        self::assertStringEndsWith($this->trans('newsletters.languages', ['count' => 0]), $cards->eq(2)->filter('.c-newsletter-card__meta')->text());
        self::assertCount(0, $cards->eq(2)->filter('.c-newsletter-lang'));
    }

    public function testALanguageWithoutTextLeadsToEnglishOrTheFirstLanguage(): void
    {
        $withEnglish = $this->newsletter('zztest_a', '2100-03-01', ['de', 'en']);
        $withoutEnglish = $this->newsletter('zztest_b', '2100-02-01', ['fr', 'de']);
        $withoutText = $this->newsletter('zztest_c', '2100-01-01', []);

        $this->client->request('GET', '/newsletters/' . $withEnglish->getId() . '/fr');
        self::assertResponseRedirects('/newsletters/' . $withEnglish->getId() . '/en');

        $this->client->request('GET', '/newsletters/' . $withoutEnglish->getId() . '/en');
        self::assertResponseRedirects('/newsletters/' . $withoutEnglish->getId() . '/de');

        $this->client->request('GET', '/newsletters/' . $withoutText->getId() . '/en');
        self::assertResponseIsSuccessful();
    }

    public function testANewsletterLinksItsLanguagesTheOlderAndNewerAndItsArchivePage(): void
    {
        // eleven newer ones put the one read on the second page
        $newsletters = [];
        for ($day = 12; $day >= 1; --$day) {
            $newsletters[] = $this->newsletter('zztest_' . $day, \sprintf('2100-01-%02d', $day), match ($day) {
                2 => ['de', 'fr', 'en'],
                1 => ['de'],
                default => ['en'],
            });
        }
        [$newer, $read, $older] = \array_slice($newsletters, 9, 3);

        $crawler = $this->client->request('GET', '/newsletters/' . $read->getId() . '/de');

        self::assertResponseIsSuccessful();
        self::assertSame('/newsletters/2', $crawler->filter('.c-cnews__back')->attr('href'));
        self::assertSame(
            $this->trans('newsletter.reading', ['language' => $this->trans('lang_de'), 'others' => 2]),
            $crawler->filter('.c-newsletter__reading')->text(),
        );
        self::assertSame('3', $crawler->filter('.c-newsletter__box-count')->text());
        self::assertSame('/newsletters/' . $read->getId() . '/de', $crawler->filter('.c-newsletter-lang.is-active')->attr('href'));
        // the older one has the language read, the newer one not: the member's locale then
        self::assertSame('/newsletters/' . $older->getId() . '/de', $crawler->filter('.c-newsletter__nav--older')->attr('href'));
        self::assertSame('/newsletters/' . $newer->getId() . '/en', $crawler->filter('.c-newsletter__nav--newer')->attr('href'));

        $crawler = $this->client->request('GET', '/newsletters/' . $newsletters[0]->getId() . '/en');

        self::assertSame('/newsletters', $crawler->filter('.c-cnews__back')->attr('href'));
        self::assertCount(0, $crawler->filter('.c-newsletter__nav--newer'));
        self::assertSame(
            $this->trans('newsletter.reading', ['language' => $this->trans('lang_en'), 'others' => 0]),
            $crawler->filter('.c-newsletter__reading')->text(),
        );
    }

    public function testADraftIsNoPublicPage(): void
    {
        $draft = $this->newsletter('zztest_draft', '2100-01-01', ['en', 'de'], 'Created');

        $this->client->request('GET', '/newsletters/' . $draft->getId() . '/en');
        self::assertResponseStatusCodeSame(404);

        $this->client->loginUser($this->member('member-2'));
        $this->client->request('GET', '/newsletters/' . $draft->getId() . '/en');
        self::assertResponseStatusCodeSame(404);
        $crawler = $this->client->request('GET', '/newsletters/1');
        self::assertCount(0, $crawler->filter('a[href^="/newsletters/' . $draft->getId() . '/"]'));
        self::assertCount(0, $crawler->filter('.c-newsletters__drafts'));
    }

    public function testTheArchiveListsTheDraftsForATranslatorInTheirLanguage(): void
    {
        $translated = $this->newsletter('zztest_draft_a', '2100-02-01', ['en', 'de', 'fr'], 'Created');
        $untranslated = $this->newsletter('zztest_draft_b', '2100-01-01', ['en'], 'Created');
        $sent = $this->newsletter('zztest_sent', '2100-03-01', ['en', 'de']);
        $this->grant('member-3', 'Words', '"de";"it";');
        $this->client->loginUser($this->member('member-3'));

        // the site in German, a language the member translates: the drafts open in it, with or without a text
        $drafts = $this->client->request('GET', '/newsletters/1', server: ['HTTP_ACCEPT_LANGUAGE' => 'de'])
            ->filter('.c-newsletters__drafts');
        $titles = $drafts->filter('.c-newsletter-card__title a')->each(static fn ($a) => (string) $a->attr('href'));
        $newer = array_search('/newsletters/' . $translated->getId() . '/de', $titles, true);
        $older = array_search('/newsletters/' . $untranslated->getId() . '/de', $titles, true);
        self::assertIsInt($newer, 'the draft with a German text opens in German');
        self::assertIsInt($older, 'the draft without a German text opens in German too');
        self::assertLessThan($older, $newer, 'the newest draft comes first');
        self::assertCount(0, $drafts->filter('a[href^="/newsletters/' . $sent->getId() . '/"]'));
        // only the languages they read it in are offered
        self::assertSame(
            ['/newsletters/' . $translated->getId() . '/de'],
            $drafts->filter('.c-newsletter-lang[href^="/newsletters/' . $translated->getId() . '/"]')
                ->each(static fn ($a) => $a->attr('href')),
        );

        // the site in a language they don't translate: the first language they do, of the draft's, else of the site's
        $this->client->getCookieJar()->clear();
        $this->client->loginUser($this->member('member-3'));
        $titles = $this->client->request('GET', '/newsletters/1', server: ['HTTP_ACCEPT_LANGUAGE' => 'fr'])
            ->filter('.c-newsletters__drafts .c-newsletter-card__title a')
            ->each(static fn ($a) => (string) $a->attr('href'));
        self::assertContains('/newsletters/' . $translated->getId() . '/de', $titles);
        self::assertCount(1, preg_grep('~^/newsletters/' . $untranslated->getId() . '/(de|it)$~', $titles) ?: []);

        // the drafts are on the first page only
        for ($day = 1; $day <= NewsletterArchiveModel::PER_PAGE; ++$day) {
            $this->newsletter('zztest_sent_' . $day, \sprintf('2100-04-%02d', $day), ['en']);
        }
        $crawler = $this->client->request('GET', '/newsletters/2');
        self::assertResponseIsSuccessful();
        self::assertCount(0, $crawler->filter('.c-newsletters__drafts'));
    }

    public function testTheArchiveListsTheDraftsForTheMassMailVolunteers(): void
    {
        $draft = $this->newsletter('zztest_draft', '2100-01-01', ['en', 'de'], 'Created');
        $this->grant('member-3', 'MassMail', '"All";');
        $this->client->loginUser($this->member('member-3'));

        $drafts = $this->client->request('GET', '/newsletters/1')->filter('.c-newsletters__drafts');

        self::assertCount(1, $drafts->filter('.c-newsletter-card__title a[href^="/newsletters/' . $draft->getId() . '/"]'));
        self::assertCount(2, $drafts->filter('.c-newsletter-lang[href^="/newsletters/' . $draft->getId() . '/"]'));
    }

    public function testATranslatorReadsADraftInTheLanguagesTheyTranslate(): void
    {
        $draft = $this->newsletter('zztest_draft', '2100-01-01', ['en', 'de', 'fr'], 'Created');
        $this->grant('member-3', 'Words', '"de";"it";');
        $this->client->loginUser($this->member('member-3'));

        $crawler = $this->client->request('GET', '/newsletters/' . $draft->getId() . '/de');
        self::assertResponseIsSuccessful();
        self::assertSame(
            ['/newsletters/' . $draft->getId() . '/de'],
            $crawler->filter('.c-newsletter-lang')->each(static fn ($a) => $a->attr('href')),
        );

        self::assertCount(1, $crawler->filter('.c-newsletter__reading--draft'));

        // a language of theirs the draft has no text in yet: the page, no way round to another language
        $this->client->request('GET', '/newsletters/' . $draft->getId() . '/it');
        self::assertResponseIsSuccessful();

        foreach (['en', 'fr'] as $language) {
            $this->client->request('GET', '/newsletters/' . $draft->getId() . '/' . $language);
            self::assertResponseStatusCodeSame(404, 'a language the member does not translate: ' . $language);
        }
    }

    public function testTheMassMailVolunteersReadADraftInEveryLanguage(): void
    {
        $draft = $this->newsletter('zztest_draft', '2100-01-01', ['en', 'de'], 'Created');
        $this->grant('member-3', 'MassMail', '"All";');
        $this->client->loginUser($this->member('member-3'));

        $crawler = $this->client->request('GET', '/newsletters/' . $draft->getId() . '/de');
        self::assertResponseIsSuccessful();
        self::assertCount(2, $crawler->filter('.c-newsletter-lang'));

        $this->client->request('GET', '/newsletters/' . $draft->getId() . '/fr');
        self::assertResponseRedirects('/newsletters/' . $draft->getId() . '/en');
    }

    public function testAMassMailStoppedWhileItWasSentStaysOpen(): void
    {
        $stopped = $this->newsletter('zztest_stopped', '2100-01-01', ['en'], 'Created');
        $this->entityManager()->getConnection()->executeStatement(
            "INSERT INTO newsletter_recipient (newsletter_id, receiver_id, status, updated)
             SELECT ?, id, 'Sent', NOW() FROM member WHERE Username = 'member-2'",
            [$stopped->getId()],
        );

        // the mail that left links to the page
        $crawler = $this->client->request('GET', '/newsletters/' . $stopped->getId() . '/en');
        self::assertResponseIsSuccessful();
        self::assertCount(0, $crawler->filter('.c-newsletter__reading--draft'));
    }

    /**
     * A regular newsletter with a text in each of the languages, in that order: sent, unless the status says not.
     *
     * @param list<string> $languages
     */
    private function newsletter(string $name, string $created, array $languages, string $status = 'Triggered'): Newsletter
    {
        $entityManager = $this->entityManager();
        $newsletter = new Newsletter();
        $newsletter->setName($name);
        $newsletter->setType(Newsletter::REGULAR_NEWSLETTER);
        $newsletter->setStatus($status);
        $newsletter->setCreatedBy($entityManager->getRepository(Member::class)->findOneBy(['username' => 'bwadmin']));
        $newsletter->setCreated(new DateTime($created));
        $entityManager->persist($newsletter);
        $entityManager->flush();
        foreach ($languages as $language) {
            $entityManager->getConnection()->executeStatement(
                "INSERT INTO word (code, domain, Sentence, created, donottranslate, Description, TranslationPriority, ShortCode)
                 VALUES (?, 'messages', 'Text', NOW(), 'No', '', 0, ?)",
                ['broadcast_body_' . $name, $language],
            );
        }

        return $newsletter;
    }

    /**
     * The member, read anew: with the rights given since.
     */
    private function member(string $username): Member
    {
        $this->entityManager()->clear();

        return $this->entityManager()->getRepository(Member::class)->findOneBy(['username' => $username]);
    }

    private function grant(string $username, string $right, string $scope): void
    {
        $this->entityManager()->getConnection()->executeStatement(
            "INSERT INTO rightsvolunteers (Level, Scope, Comment, updated, created, IdMember, IdRight)
             SELECT 10, ?, 'zztest', NOW(), NOW(), m.id, r.id FROM member m INNER JOIN rights r ON r.Name = ?
             WHERE m.Username = ?",
            [$scope, $right, $username],
        );
    }

    private function entityManager(): EntityManagerInterface
    {
        return static::getContainer()->get(EntityManagerInterface::class);
    }

    /**
     * A text as the page shows it: the test database may have no translations (CI), then the key.
     *
     * @param array<string, int|string> $parameters
     */
    private function trans(string $id, array $parameters = []): string
    {
        return static::getContainer()->get(TranslatorInterface::class)->trans($id, $parameters);
    }
}
