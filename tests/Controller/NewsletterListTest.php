<?php

namespace App\Tests\Controller;

use App\Entity\Member;
use App\Entity\Newsletter;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * The list of newsletters links each one in the languages it has a text in.
 */
#[Group('integration')]
final class NewsletterListTest extends WebTestCase
{
    public function testTheListLinksTheLanguagesOfANewsletter(): void
    {
        $client = static::createClient();
        $client->disableReboot();
        $entityManager = static::getContainer()->get(EntityManagerInterface::class);
        $newsletter = new Newsletter();
        $newsletter->setName('zztest_list');
        $newsletter->setType(Newsletter::REGULAR_NEWSLETTER);
        $newsletter->setStatus('Triggered');
        $newsletter->setCreatedBy($entityManager->getRepository(Member::class)->findOneBy(['username' => 'bwadmin']));
        $newsletter->setCreated(new DateTime());
        $entityManager->persist($newsletter);
        $entityManager->flush();
        foreach (['en', 'de'] as $locale) {
            $entityManager->getConnection()->executeStatement(
                "INSERT INTO word (code, domain, Sentence, created, donottranslate, Description, TranslationPriority, ShortCode)
                 VALUES ('broadcast_body_zztest_list', 'messages', 'Text', NOW(), 'No', '', 0, ?)",
                [$locale],
            );
        }
        $client->loginUser($entityManager->getRepository(Member::class)->findOneBy(['username' => 'member-2']));

        $crawler = $client->request('GET', '/newsletters/1');

        self::assertResponseIsSuccessful();
        foreach (['en', 'de'] as $locale) {
            self::assertCount(1, $crawler->filter('a[href$="/newsletters/' . $newsletter->getId() . '/' . $locale . '"]'));
        }
    }
}
