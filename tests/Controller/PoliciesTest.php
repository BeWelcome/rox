<?php

namespace App\Tests\Controller;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * The policies (terms of use, privacy policy, data rights): one layout with the tabs, the "On this page" list read
 * from the sections, the other language and the two other documents. English and French share the section ids.
 */
#[Group('integration')]
final class PoliciesTest extends WebTestCase
{
    private const array SECTIONS = [
        'terms' => ['introduction', 'membership', 'registration', 'privacy', 'conduct', 'content', 'cancellation', 'liability', 'litigation'],
        'privacy' => ['introduction', 'cookies', 'use-of-information', 'safety', 'sharing', 'account', 'security', 'other-sites', 'contact', 'changes'],
        'datarights' => ['your-rights', 'access', 'rectification', 'deletion', 'restrict-processing', 'portability', 'withdraw-consent', 'marketing', 'complain', 'inheritance'],
    ];

    private const array PATHS = ['terms' => '/terms/', 'privacy' => '/privacy/', 'datarights' => '/datarights/'];

    private KernelBrowser $client;

    protected function setUp(): void
    {
        $this->client = static::createClient();
    }

    /**
     * @return iterable<string, array{string, string}>
     */
    public static function documents(): iterable
    {
        foreach (array_keys(self::SECTIONS) as $policy) {
            foreach (['en', 'fr'] as $locale) {
                yield $policy . ' ' . $locale => [$policy, $locale];
            }
        }
    }

    #[DataProvider('documents')]
    public function testADocumentHasItsSectionsTabsAndOtherLanguage(string $policy, string $locale): void
    {
        $crawler = $this->client->request('GET', self::PATHS[$policy] . $locale);

        $this->assertResponseIsSuccessful();
        // the list of the page follows the sections, which have the same ids in both languages
        $sections = $crawler->filter('.p-policies__sections > section')->each(static fn ($section): string => (string) $section->attr('id'));
        self::assertSame(self::SECTIONS[$policy], $sections);
        $toc = $crawler->filter('.p-policies__toc nav a')->each(static fn ($link): string => (string) $link->attr('href'));
        self::assertSame(array_map(static fn (string $id): string => '#' . $id, self::SECTIONS[$policy]), $toc);
        // every section but the introduction has its heading, which is the list's label
        self::assertSame(
            $crawler->filter('.p-policies__sections > section > h2')->each(static fn ($heading): string => trim($heading->text())),
            \array_slice($crawler->filter('.p-policies__toc nav a')->each(static fn ($link): string => trim($link->text())), 'introduction' === self::SECTIONS[$policy][0] ? 1 : 0),
        );

        self::assertSame(self::PATHS[$policy] . $locale, $crawler->filter('.p-policies__tab.is-active')->attr('href'));
        self::assertCount(3, $crawler->filter('.p-policies__tab'));
        self::assertCount(2, $crawler->filter('.p-policies__tile'));
        $other = 'en' === $locale ? 'fr' : 'en';
        self::assertSame([self::PATHS[$policy] . $other], $crawler->filter('.js-policies-version')->each(static fn ($link): string => (string) $link->attr('href')));
        self::assertCount('fr' === $locale ? 1 : 0, $crawler->filter('.p-policies__authoritative'));
    }

    public function testTheDatesAreShownWhereTheDocumentHasOne(): void
    {
        $crawler = $this->client->request('GET', '/privacy/en');
        self::assertStringContainsString('9 July 2026', $crawler->filter('.p-policies__meta')->text());

        $crawler = $this->client->request('GET', '/terms/fr');
        self::assertStringContainsString('30 avril 2026', $crawler->filter('.p-policies__meta')->text());
    }

    public function testATranslatedDocumentHasTheDisclaimerAndBothLanguages(): void
    {
        $crawler = $this->client->request('GET', '/datarights/es');

        $this->assertResponseIsSuccessful();
        // the disclaimer (its text, with the link to the English version, comes from the translations)
        self::assertCount(1, $crawler->filter('.p-policies__card .alert-notice'));
        self::assertSame(['/datarights/en', '/datarights/fr'], $crawler->filter('.js-policies-version')->each(static fn ($link): string => (string) $link->attr('href')));
        self::assertSame('/datarights/es', $crawler->filter('.p-policies__tab.is-active')->attr('href'));
    }
}
