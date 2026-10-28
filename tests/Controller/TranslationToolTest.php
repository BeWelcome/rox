<?php

namespace App\Tests\Controller;

use App\Entity\Member;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * A translator can find and correct a broken English text. The list showed the English text by formatting it, which
 * failed for a text ICU can't format; the edit page looked it up by a field Word doesn't have (shortCode).
 */
#[Group('integration')]
class TranslationToolTest extends WebTestCase
{
    // ICU refuses to format this: radius is a plural and again a simple argument inside it
    private const string BROKEN = '{radius, plural, =0 {Nothing here.} other {Nothing within {radius} km.}}';
    private const string FIXED = '{radius, plural, =0 {Nothing here.} other {Nothing within # km.}}';

    private KernelBrowser $client;
    private Connection $connection;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->connection = static::getContainer()->get(Connection::class);
        $this->connection->executeStatement(
            "INSERT INTO word (code, ShortCode, domain, Sentence, created, updated, majorupdate, donottranslate, Description, TranslationPriority, isarchived)
             VALUES ('zztest.broken', 'en', 'messages+intl-icu', ?, NOW(), NOW(), NOW(), 'no', 'test', 5, 0)",
            [self::BROKEN]
        );
        // bwadmin has the Words right in the fixtures
        $translator = static::getContainer()->get(EntityManagerInterface::class)
            ->getRepository(Member::class)->findOneBy(['username' => 'bwadmin']);
        $this->client->loginUser($translator);
    }

    public function testTheListShowsABrokenTextAsStored(): void
    {
        $this->client->request('GET', '/admin/translations/all/en/zztest.broken');

        $this->assertResponseIsSuccessful();
        // the list shortens texts to 50 characters
        $this->assertSelectorTextContains('table', '{radius, plural, =0 {Nothing here.}');
    }

    public function testATranslatorCorrectsTheText(): void
    {
        // as translations:add:missing stored them before: not archived meant NULL, not 0
        $this->connection->executeStatement("UPDATE word SET isarchived = NULL WHERE code = 'zztest.broken'");
        $crawler = $this->client->request('GET', '/admin/translations/edit/en/zztest.broken');
        $this->assertResponseIsSuccessful();

        $form = $crawler->filter('form[name=edit_translation_form]')->form([
            'edit_translation_form[translatedText]' => self::FIXED,
        ]);
        $this->client->submit($form);

        $this->assertResponseRedirects();
        self::assertSame(
            self::FIXED,
            $this->connection->fetchOne("SELECT Sentence FROM word WHERE code = 'zztest.broken' AND ShortCode = 'en'")
        );
    }

    public function testTheFaqEditorFindsTheFaqTexts(): void
    {
        $faq = (int) $this->connection->fetchOne('SELECT MIN(id) FROM faq');
        $code = $this->connection->fetchOne('SELECT QandA FROM faq WHERE id = ?', [$faq]);
        foreach (['faqq_', 'faqa_'] as $prefix) {
            $this->connection->executeStatement(
                "INSERT IGNORE INTO word (code, ShortCode, domain, Sentence, created, updated, majorupdate, donottranslate, Description, TranslationPriority, isarchived)
                 VALUES (?, 'en', 'messages', 'FAQ text', NOW(), NOW(), NOW(), 'no', 'test', 5, 0)",
                [$prefix . $code]
            );
        }
        $this->connection->executeStatement(
            "INSERT INTO rightsvolunteers (IdMember, IdRight, Level, Scope, Comment, created, updated)
             SELECT m.id, r.id, 10, '\"All\"', 'test', NOW(), NOW() FROM member m, rights r
             WHERE m.Username = 'bwadmin' AND r.Name = 'Faq'
             AND NOT EXISTS (SELECT 1 FROM rightsvolunteers rv WHERE rv.IdMember = m.id AND rv.IdRight = r.id)"
        );
        $admin = static::getContainer()->get(EntityManagerInterface::class)
            ->getRepository(Member::class)->findOneBy(['username' => 'bwadmin']);
        static::getContainer()->get(EntityManagerInterface::class)->refresh($admin);
        $this->client->loginUser($admin);

        $this->client->request('GET', '/admin/faqs/faq/' . $faq . '/edit');

        $this->assertResponseIsSuccessful();
    }
}
