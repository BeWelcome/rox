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
 * Translation mode: below the page the texts it translated, missing, not translated yet and translated, each with
 * the page that creates, adds or edits it. Those pages open in the side panel (?embed=1): saved there, they tell
 * the page which text changed, and the page has its new texts rendered to show them in place.
 * The test database has no translations: the texts are set in the catalogue, the footer's "termsofuse" is one.
 */
#[Group('integration')]
class InPlaceTranslationsTest extends WebTestCase
{
    private const string CODE = 'termsofuse';

    private KernelBrowser $client;
    private Connection $connection;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->client->disableReboot();
        $this->connection = static::getContainer()->get(Connection::class);
        // bwadmin has the Words right for all languages in the fixtures
        $this->client->loginUser($this->member('bwadmin'));
    }

    public function testTheListIsOnlyShownInTranslationMode(): void
    {
        $crawler = $this->client->request('GET', '/forums');
        self::assertCount(0, $crawler->filter('#translations'));

        $this->client->request('GET', '/admin/translations/mode/on');
        $crawler = $this->client->request('GET', '/forums');
        self::assertCount(1, $crawler->filter('#translations'));
        self::assertCount(1, $crawler->filter('.js-translation-drawer'));

        $this->client->request('GET', '/admin/translations/mode/off');
        self::assertCount(0, $this->client->request('GET', '/forums')->filter('#translations'));
    }

    public function testAMissingTextIsCreated(): void
    {
        $item = $this->item($this->pageInTranslationMode(), 'missing');

        self::assertSame('/admin/translations/create/en/' . self::CODE, $item->filter('.js-translation-open')->attr('href'));
        self::assertSame(self::CODE, $item->filter('.js-in-place-text')->text());
    }

    public function testATranslatedTextIsEditedAndKnowsHowThePageShowsIt(): void
    {
        $this->client->getContainer()->get('translator')->getCatalogue('en')->set(self::CODE, 'Terms of use');

        $item = $this->item($this->pageInTranslationMode(), 'defined');

        self::assertSame('/admin/translations/edit/en/' . self::CODE, $item->filter('.js-translation-open')->attr('href'));
        self::assertSame('Terms of use', $item->filter('.js-in-place-text')->text());
        self::assertSame([['text' => 'Terms of use', 'parameters' => []]], json_decode($item->attr('data-occurrences'), true));
    }

    public function testATextShownInEnglishIsTranslated(): void
    {
        // the German catalogue has its own copy of the English texts
        $this->client->getContainer()->get('translator')->getCatalogue('de')->getFallbackCatalogue()->set(self::CODE, 'Terms of use');

        $item = $this->item($this->pageInTranslationMode('de'), 'fallback');

        self::assertSame('/admin/translations/add/de/' . self::CODE, $item->filter('.js-translation-open')->attr('href'));
        self::assertSame('/admin/translations/edit/de/' . self::CODE, $item->filter('.js-translation-open')->attr('data-edit-url'));
    }

    public function testSavedTextsAreRenderedWithTheParametersOfThePage(): void
    {
        $this->word('zztest.icu', 'en', 'messages+intl-icu', 'Hello {name}');
        $this->word('zztest.icu', 'de', 'messages+intl-icu', 'Hallo {name}');
        $this->word('zztest.plain', 'en', 'messages', 'Hi %name%');

        self::assertSame(['Hallo Ana', 'Hallo Bo'], $this->render('zztest.icu', 'de', [['name' => 'Ana'], ['name' => 'Bo']]));
        // no translation for the language: the English text
        self::assertSame(['Hello Ana'], $this->render('zztest.icu', 'fr', [['name' => 'Ana']]));
        self::assertSame(['Hi Ana'], $this->render('zztest.plain', 'en', [['%name%' => 'Ana']]));

        $this->render('zztest.unknown', 'en', [[]]);
        $this->assertResponseStatusCodeSame(404);

        $this->client->loginUser($this->member('member-2'));
        $this->render('zztest.icu', 'de', [[]]);
        $this->assertResponseStatusCodeSame(403);
    }

    public function testATranslationEditedInThePanelTellsThePage(): void
    {
        $this->word('zztest.edit', 'en', 'messages', 'Good morning');
        $this->word('zztest.edit', 'de', 'messages', 'Guten Morgn');

        $crawler = $this->client->request('GET', '/admin/translations/edit/de/zztest.edit?embed=1');
        $this->assertResponseIsSuccessful();
        self::assertCount(0, $crawler->filter('footer'), 'the page alone, for the panel');
        $this->client->submit($crawler->filter('form[name=edit_translation_form]')->form([
            'edit_translation_form[translatedText]' => 'Guten Morgen',
        ]));

        $this->assertSaved('zztest.edit', 'de');
        self::assertSame('Guten Morgen', $this->sentence('zztest.edit', 'de'));
    }

    public function testATranslationAddedInThePanelTellsThePage(): void
    {
        $this->word('zztest.add', 'en', 'messages', 'Good evening');

        // the edit page of a text without translation sends to the add page, still in the panel
        $this->client->request('GET', '/admin/translations/edit/de/zztest.add?embed=1');
        $this->assertResponseRedirects('/admin/translations/add/de/zztest.add?embed=1');
        $crawler = $this->client->followRedirect();
        $this->client->submit($crawler->filter('form[name=edit_translation_form]')->form([
            'edit_translation_form[translatedText]' => 'Guten Abend',
        ]));

        $this->assertSaved('zztest.add', 'de');
        self::assertSame('Guten Abend', $this->sentence('zztest.add', 'de'));
    }

    public function testATextCreatedInThePanelTellsThePage(): void
    {
        $crawler = $this->client->request('GET', '/admin/translations/create/en/zztest.create?embed=1');
        $this->assertResponseIsSuccessful();
        $form = $crawler->filter('form')->reduce(static fn (Crawler $form): bool => $form->filter('[name$="[englishText]"]')->count() > 0)->form();
        $prefix = substr((string) $crawler->filter('[name$="[englishText]"]')->attr('name'), 0, -\strlen('[englishText]'));
        $this->client->submit($form, [
            $prefix . '[englishText]' => 'A new text',
            $prefix . '[description]' => 'For the test',
            $prefix . '[domain]' => 'messages',
        ]);

        $this->assertSaved('zztest.create', 'en');
        self::assertSame('A new text', $this->sentence('zztest.create', 'en'));
    }

    private function pageInTranslationMode(string $locale = 'en'): Crawler
    {
        $server = ['HTTP_ACCEPT_LANGUAGE' => $locale];
        $this->client->request('GET', '/admin/translations/mode/on', [], [], $server);

        return $this->client->request('GET', '/forums', [], [], $server);
    }

    private function item(Crawler $crawler, string $group): Crawler
    {
        $item = $crawler->filter('.c-in-place__group--' . $group . ' .js-in-place-item[data-code="' . self::CODE . '"]');
        self::assertCount(1, $item, self::CODE . ' is listed as ' . $group);

        return $item;
    }

    private function assertSaved(string $code, string $locale): void
    {
        $this->assertResponseIsSuccessful();
        $content = (string) $this->client->getResponse()->getContent();
        self::assertStringContainsString("type: 'bw-translation-saved'", $content);
        self::assertStringContainsString('code: "' . $code . '"', $content);
        self::assertStringContainsString('locale: "' . $locale . '"', $content);
    }

    /**
     * @param list<array<string, string>> $parameterSets
     *
     * @return list<string>|null
     */
    private function render(string $code, string $locale, array $parameterSets): ?array
    {
        $this->client->jsonRequest('POST', '/admin/translations/render', [
            'code' => $code,
            'locale' => $locale,
            'parameterSets' => $parameterSets,
        ]);

        return json_decode((string) $this->client->getResponse()->getContent(), true)['texts'] ?? null;
    }

    private function word(string $code, string $locale, string $domain, string $sentence): void
    {
        $this->connection->executeStatement(
            "INSERT INTO word (code, ShortCode, domain, Sentence, created, updated, majorupdate, donottranslate, Description, TranslationPriority, isarchived)
             VALUES (?, ?, ?, ?, NOW(), NOW(), '2000-01-01', 'no', 'test', 5, 0)",
            [$code, $locale, $domain, $sentence]
        );
    }

    private function sentence(string $code, string $locale): string|false
    {
        return $this->connection->fetchOne('SELECT Sentence FROM word WHERE code = ? AND ShortCode = ?', [$code, $locale]);
    }

    private function member(string $username): Member
    {
        $member = static::getContainer()->get(EntityManagerInterface::class)
            ->getRepository(Member::class)->findOneBy(['username' => $username]);
        self::assertInstanceOf(Member::class, $member);

        return $member;
    }
}
