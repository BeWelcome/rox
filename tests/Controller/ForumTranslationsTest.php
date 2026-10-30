<?php

namespace App\Tests\Controller;

use App\Entity\ForumPost;
use App\Entity\ForumPostTranslation;
use App\Entity\ForumThread;
use App\Entity\ForumThreadTranslation;
use App\Entity\Member;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * The forum's texts in several languages (forum_post_translation, forum_thread_translation), read by the thread
 * page and by ForumPost::getMessageTranslations(). Uses the fixture thread #1 and its first post. Writing them is
 * ForumWritingTest's.
 */
#[Group('integration')]
class ForumTranslationsTest extends WebTestCase
{
    private const string GERMAN_POST = 'Deutscher Text des ersten Beitrags';
    private const string GERMAN_TITLE = 'Tipps für Gastgeber zum ersten Besuch';

    private KernelBrowser $client;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->client->disableReboot();
        $entityManager = $this->getEntityManager();
        $post = $entityManager->find(ForumPost::class, 1);
        $thread = $entityManager->find(ForumThread::class, 1);
        $post->addTranslation(new ForumPostTranslation('en', 'message', $post->getMessage()));
        $post->addTranslation(new ForumPostTranslation('de', 'message', self::GERMAN_POST));
        $thread->addTranslation(new ForumThreadTranslation('en', 'title', $thread->getTitle()));
        $thread->addTranslation(new ForumThreadTranslation('de', 'title', self::GERMAN_TITLE));
        $entityManager->flush();
        $entityManager->clear();
    }

    public function testThreadPageShowsTheTextInTheMembersLanguage(): void
    {
        $this->client->loginUser($this->member('member-1'));

        $this->client->request('GET', '/rox/in/de');
        $crawler = $this->client->request('GET', '/forums/s1');

        $this->assertResponseIsSuccessful();
        self::assertStringContainsString(self::GERMAN_POST, $crawler->filter('body')->text());
        self::assertStringContainsString(self::GERMAN_TITLE, $crawler->filter('body')->text());
    }

    public function testThreadPageFallsBackToEnglish(): void
    {
        $this->client->loginUser($this->member('member-1'));
        $original = $this->getEntityManager()->find(ForumPost::class, 1)->getMessage();

        $this->client->request('GET', '/rox/in/fr');
        $crawler = $this->client->request('GET', '/forums/s1');

        $this->assertResponseIsSuccessful();
        // No French text: the English one (the German one is only in the list of available translations)
        $text = $crawler->filter('body')->text();
        self::assertStringNotContainsString(self::GERMAN_TITLE, $text);
        self::assertStringContainsString($this->getEntityManager()->find(ForumThread::class, 1)->getTitle(), $text);
        self::assertStringContainsString(mb_substr(strip_tags($original), 0, 30), $text);
    }

    public function testMessageTranslationsOfAPost(): void
    {
        $entityManager = $this->getEntityManager();
        $entityManager->clear();

        $translations = $entityManager->find(ForumPost::class, 1)->getMessageTranslations();

        self::assertSame(['en', 'de'], array_keys($translations));
        self::assertSame(self::GERMAN_POST, $translations['de']['message']);
        self::assertSame('de', $translations['de']['language']->getShortCode());
        self::assertSame(self::GERMAN_TITLE, $entityManager->find(ForumThread::class, 1)->getTitles()['de']);
    }

    private function member(string $username): Member
    {
        $member = $this->getEntityManager()->getRepository(Member::class)->findOneBy(['username' => $username]);
        self::assertInstanceOf(Member::class, $member);

        return $member;
    }

    private function getEntityManager(): EntityManagerInterface
    {
        return static::getContainer()->get(EntityManagerInterface::class);
    }
}
