<?php

namespace App\Tests\Controller;

use App\Entity\Member;
use App\Entity\Newsletter;
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

    /**
     * A sent regular newsletter with a text in each of the languages, in that order.
     *
     * @param list<string> $languages
     */
    private function newsletter(string $name, string $created, array $languages): Newsletter
    {
        $entityManager = $this->entityManager();
        $newsletter = new Newsletter();
        $newsletter->setName($name);
        $newsletter->setType(Newsletter::REGULAR_NEWSLETTER);
        $newsletter->setStatus('Triggered');
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
