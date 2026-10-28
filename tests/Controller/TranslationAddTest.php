<?php

namespace App\Tests\Controller;

use App\Entity\Member;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * A translator adds the first translation of an English text. The page read the text of the new (still empty)
 * translation before it was set, and failed for a code without an English text.
 */
#[Group('integration')]
class TranslationAddTest extends WebTestCase
{
    private KernelBrowser $client;
    private Connection $connection;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->connection = static::getContainer()->get(Connection::class);
        $this->connection->executeStatement(
            "INSERT INTO word (code, ShortCode, domain, Sentence, created, updated, majorupdate, donottranslate, Description, TranslationPriority, isarchived)
             VALUES ('zztest.add', 'en', 'messages', 'Good morning', NOW(), NOW(), NOW(), 'no', 'test', 5, 0)"
        );
        // bwadmin has the Words right for all languages in the fixtures
        $translator = static::getContainer()->get(EntityManagerInterface::class)
            ->getRepository(Member::class)->findOneBy(['username' => 'bwadmin']);
        $this->client->loginUser($translator);
    }

    public function testATranslatorAddsATranslation(): void
    {
        $crawler = $this->client->request('GET', '/admin/translations/add/de/zztest.add');
        $this->assertResponseIsSuccessful();

        $form = $crawler->filter('form[name=edit_translation_form]')->form([
            'edit_translation_form[translatedText]' => 'Guten Morgen',
        ]);
        $this->client->submit($form);

        $this->assertResponseRedirects();
        self::assertSame(
            'Guten Morgen',
            $this->connection->fetchOne("SELECT Sentence FROM word WHERE code = 'zztest.add' AND ShortCode = 'de'")
        );
    }

    public function testACodeWithoutEnglishTextIsNotFound(): void
    {
        $this->client->request('GET', '/admin/translations/add/de/zztest.unknown');

        $this->assertResponseStatusCodeSame(404);
    }
}
