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
 * The format of a text (set on the English one, else detected from it) decides how it is written and stored: HTML
 * and inline HTML with the rich text editor, inline HTML without the paragraph the editor adds, plain text in a plain
 * field. Translations follow the English text.
 */
#[Group('integration')]
class TranslationFormatToolTest extends WebTestCase
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
    }

    public function testATranslationOfAnInlineTextLosesTheEditorsParagraph(): void
    {
        // detected: a link, no paragraph
        $this->word('zztest.inline', 'en', 'Read the <a href="/rules">rules</a> first.');

        $crawler = $this->client->request('GET', '/admin/translations/add/de/zztest.inline');
        self::assertSame('textarea', $this->editorType($crawler), 'written with the rich text editor');
        $this->save($crawler, '<p>Lies zuerst die <a href="/rules">Regeln</a>.</p>');

        self::assertSame('Lies zuerst die <a href="/rules">Regeln</a>.', $this->sentence('zztest.inline', 'de'));
    }

    public function testATranslationOfAPlainTextIsWrittenInAPlainField(): void
    {
        $this->word('zztest.plain', 'en', 'Search words');

        $crawler = $this->client->request('GET', '/admin/translations/add/de/zztest.plain');
        self::assertNull($this->editorType($crawler));
        $this->save($crawler, 'Suchbegriffe');

        self::assertSame('Suchbegriffe', $this->sentence('zztest.plain', 'de'));
    }

    public function testTheFormatSetOnTheEnglishTextIsFollowed(): void
    {
        // looks plain, but is used within the page's HTML
        $this->word('zztest.set', 'en', 'Welcome', 'inline');

        $crawler = $this->client->request('GET', '/admin/translations/add/de/zztest.set');
        self::assertNotNull($this->editorType($crawler));
        $this->save($crawler, '<p>Willkommen</p>');

        self::assertSame('Willkommen', $this->sentence('zztest.set', 'de'));
    }

    public function testTheFormatIsSetOnTheEnglishText(): void
    {
        $this->word('zztest.english', 'en', 'Welcome');

        $crawler = $this->client->request('GET', '/admin/translations/edit/en/zztest.english');
        $this->assertResponseIsSuccessful();
        // not set: detected
        self::assertSame('', $this->selectedFormat($crawler));
        $this->save($crawler, 'Welcome!', ['edit_translation_form[format]' => 'html']);

        self::assertSame(['Sentence' => 'Welcome!', 'format' => 'html'], $this->connection->fetchAssociative(
            "SELECT Sentence, format FROM word WHERE code = 'zztest.english' AND ShortCode = 'en'"
        ));

        // back to detected
        $crawler = $this->client->request('GET', '/admin/translations/edit/en/zztest.english');
        self::assertSame('html', $this->selectedFormat($crawler));
        $this->save($crawler, '<p>Welcome!</p>', ['edit_translation_form[format]' => '']);
        self::assertNull($this->connection->fetchOne("SELECT format FROM word WHERE code = 'zztest.english' AND ShortCode = 'en'"));
    }

    public function testACreatedTextGetsItsFormat(): void
    {
        $crawler = $this->client->request('GET', '/admin/translations/create/en/zztest.created');
        $this->assertResponseIsSuccessful();
        $form = $crawler->filter('form')->reduce(static fn (Crawler $form): bool => $form->filter('[name$="[englishText]"]')->count() > 0)->form();
        $prefix = substr((string) $crawler->filter('[name$="[englishText]"]')->attr('name'), 0, -\strlen('[englishText]'));
        $this->client->submit($form, [
            $prefix . '[englishText]' => 'Read <b>this</b>',
            $prefix . '[description]' => 'For the test',
            $prefix . '[domain]' => 'messages',
            $prefix . '[format]' => 'inline',
        ]);

        $this->assertResponseRedirects();
        self::assertSame(['Sentence' => 'Read <b>this</b>', 'format' => 'inline'], $this->connection->fetchAssociative(
            "SELECT Sentence, format FROM word WHERE code = 'zztest.created' AND ShortCode = 'en'"
        ));
    }

    /**
     * @param array<string, string> $more
     */
    private function save(Crawler $crawler, string $text, array $more = []): void
    {
        $this->assertResponseIsSuccessful();
        $this->client->submit($crawler->filter('form[name=edit_translation_form]')->form(
            ['edit_translation_form[translatedText]' => $text] + $more
        ));
        $this->assertResponseRedirects();
    }

    /**
     * The format chosen on the English text's page, '' for detected.
     */
    private function selectedFormat(Crawler $crawler): string
    {
        $selected = $crawler->filter('select[name="edit_translation_form[format]"] option[selected]');

        return $selected->count() > 0 ? (string) $selected->attr('value') : '';
    }

    private function editorType(Crawler $crawler): ?string
    {
        return $crawler->filter('[name="edit_translation_form[translatedText]"]')->attr('data-editor-type');
    }

    private function word(string $code, string $locale, string $sentence, ?string $format = null): void
    {
        $this->connection->executeStatement(
            "INSERT INTO word (code, ShortCode, domain, Sentence, created, updated, majorupdate, donottranslate, Description, TranslationPriority, isarchived, format)
             VALUES (?, ?, 'messages', ?, NOW(), NOW(), '2000-01-01', 'no', 'test', 5, 0, ?)",
            [$code, $locale, $sentence, $format]
        );
    }

    private function sentence(string $code, string $locale): string|false
    {
        return $this->connection->fetchOne('SELECT Sentence FROM word WHERE code = ? AND ShortCode = ?', [$code, $locale]);
    }
}
