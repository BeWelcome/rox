<?php

namespace App\Tests\Controller;

use App\Entity\ForumPost;
use App\Entity\ForumPostTranslation;
use App\Entity\ForumThread;
use App\Entity\ForumThreadTranslation;
use App\Entity\Member;
use DAMA\DoctrineTestBundle\PHPUnit\SkipDatabaseRollback;
use Doctrine\ORM\EntityManagerInterface;
use Forums;
use MOD_words;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use ReflectionProperty;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * The forum's texts in several languages (forum_post_translation, forum_thread_translation), read by the
 * legacy thread page and by ForumPost::getMessageTranslations(). Uses the fixture thread #1 and its first post.
 *
 * The legacy page reads with its own database connection, so the test data is committed (no rollback by the
 * DAMA bundle) and removed in tearDown(). The legacy code keeps state in static singletons for its one
 * request per process, so each test runs in a process of its own.
 *
 * @infection-ignore-all
 */
#[Group('integration')]
#[SkipDatabaseRollback]
#[RunTestsInSeparateProcesses]
class ForumTranslationsTest extends WebTestCase
{
    private const string GERMAN_POST = 'Deutscher Text des ersten Beitrags';
    private const string GERMAN_TITLE = 'Tipps für Gastgeber zum ersten Besuch';

    private KernelBrowser $client;
    private string $originalWorkingDirectory;

    protected function setUp(): void
    {
        // The legacy pages find their files relative to public/ (SCRIPT_BASE), as AvatarControllerTest does
        $this->originalWorkingDirectory = (string) getcwd();
        chdir(__DIR__ . '/../../public');
        $this->client = static::createClient();
        $entityManager = $this->getEntityManager();
        $post = $entityManager->find(ForumPost::class, 1);
        $thread = $entityManager->find(ForumThread::class, 1);
        $post->addTranslation(new ForumPostTranslation('en', 'message', $post->getMessage()));
        $post->addTranslation(new ForumPostTranslation('de', 'message', self::GERMAN_POST));
        $thread->addTranslation(new ForumThreadTranslation('en', 'title', $thread->getTitle()));
        $thread->addTranslation(new ForumThreadTranslation('de', 'title', self::GERMAN_TITLE));
        $entityManager->flush();
    }

    protected function tearDown(): void
    {
        $connection = $this->getEntityManager()->getConnection();
        $connection->executeStatement('DELETE FROM forum_post_translation WHERE object_id = 1');
        $connection->executeStatement('DELETE FROM forum_thread_translation WHERE object_id = 1');
        chdir($this->originalWorkingDirectory);

        parent::tearDown();
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

    /*
     * The write path. The legacy forms end their request with exit() after the redirect (PPHP::PExit()), so
     * they can't be submitted within PHPUnit. These tests call what the form handlers call, after a legacy page
     * has set up the legacy environment and session for the logged-in member.
     */

    public function testAddingATranslationStoresItWithTheTranslator(): void
    {
        $member = $this->member('member-1');
        $this->client->loginUser($member);
        // any legacy page sets up the legacy session (the forum isn't legacy any more)
        $this->legacyRequest('/forums/member/member-1');

        // As the moderator's "add translated post" and a new post or reply do
        $result = new MOD_words()->InsertInFTrad('Texte français du premier message', 'forum_post.IdContent', 1, $member->getId(), 'fr');

        self::assertSame(1, $result);
        $row = $this->translationRow('forum_post_translation', 'fr');
        self::assertSame('Texte français du premier message', $row['content']);
        self::assertSame('message', $row['field']);
        self::assertSame($member->getId(), (int) $row['translator_id']);
        self::assertNotNull($row['created_at']);
        self::assertNull($row['updated_at']);
        self::assertSame(1, (int) $this->getEntityManager()->getConnection()->fetchOne('SELECT IdContent FROM forum_post WHERE id = 1'));
    }

    public function testAnEmptyTextIsNotStored(): void
    {
        $this->client->loginUser($this->member('member-1'));
        // any legacy page sets up the legacy session (the forum isn't legacy any more)
        $this->legacyRequest('/forums/member/member-1');

        $result = new MOD_words()->InsertInFTrad('', 'forum_thread.IdTitle', 1, 0, 'fr');

        self::assertSame(0, $result);
        self::assertFalse($this->translationRow('forum_thread_translation', 'fr'));
    }

    public function testEditingAPostInALanguageUpdatesThatLanguageOnly(): void
    {
        $member = $this->member('member-1');
        $this->client->loginUser($member);
        // any legacy page sets up the legacy session (the forum isn't legacy any more)
        $this->legacyRequest('/forums/member/member-1');
        $english = $this->translationRow('forum_post_translation', 'en');
        $german = $this->translationRow('forum_post_translation', 'de');

        // The edit and translate forms post the chosen language as IdLanguage (editPost() -> ReplaceInFTrad())
        $_POST['IdLanguage'] = 'de';
        try {
            new MOD_words()->ReplaceInFTrad('Geänderter deutscher Text', 'forum_post.IdContent', 1);
        } finally {
            unset($_POST['IdLanguage']);
        }

        $updated = $this->translationRow('forum_post_translation', 'de');
        self::assertSame($german['id'], $updated['id']);
        self::assertSame('Geänderter deutscher Text', $updated['content']);
        self::assertNotNull($updated['updated_at']);
        self::assertSame($member->getId(), (int) $updated['translator_id']);
        self::assertSame($english, $this->translationRow('forum_post_translation', 'en'));
    }

    public function testSavingTheSameTextAgainDoesNotMarkItAsEdited(): void
    {
        $this->client->loginUser($this->member('member-1'));
        // any legacy page sets up the legacy session (the forum isn't legacy any more)
        $this->legacyRequest('/forums/member/member-1');

        new MOD_words()->InsertInFTrad(self::GERMAN_TITLE, 'forum_thread.IdTitle', 1, 0, 'de');

        $row = $this->translationRow('forum_thread_translation', 'de');
        self::assertSame(self::GERMAN_TITLE, $row['content']);
        self::assertNull($row['updated_at']);
        self::assertNull($row['translator_id']);
    }

    public function testModeratorCorrectsATranslation(): void
    {
        $member = $this->member('member-1');
        $this->client->loginUser($member);
        // any legacy page sets up the legacy session (the forum isn't legacy any more)
        $this->legacyRequest('/forums/member/member-1');
        $german = $this->translationRow('forum_thread_translation', 'de');

        // The moderator's full edit form posts IdForumTrads as "thread-<id>" (ModeratorEditPostProcess())
        new Forums()->DofTradUpdate('thread-' . $german['id'], 'Tipps für neue Gastgeber', 'de');

        $updated = $this->translationRow('forum_thread_translation', 'de');
        self::assertSame('Tipps für neue Gastgeber', $updated['content']);
        self::assertSame('de', $updated['locale']);
        self::assertNotNull($updated['updated_at']);
        self::assertSame($member->getId(), (int) $updated['translator_id']);
    }

    /**
     * The row of thread or post #1 in a language, read with the test's own connection.
     *
     * @return array<string, mixed>|false
     */
    private function translationRow(string $table, string $locale): array|false
    {
        return $this->getEntityManager()->getConnection()->fetchAssociative(
            'SELECT * FROM ' . $table . ' WHERE object_id = 1 AND locale = ?',
            [$locale],
        );
    }

    /**
     * Requests a legacy page. Under PHPUnit the legacy request handling takes the command line as request
     * (PRequest) and caches it; this gives it the URL for one request, as a web server would.
     */
    private function legacyRequest(string $url): \Symfony\Component\DomCrawler\Crawler
    {
        $server = $_SERVER;
        $_SERVER['argc'] = 0;
        $_SERVER['REQUEST_URI'] = $url;
        $_SERVER['SCRIPT_NAME'] = '/index.php';
        // ... and the parsed request, in case an earlier request of this process cached it
        foreach ([['PRequest', '_instance'], ['RequestRouter', '_request_args']] as [$class, $property]) {
            if (class_exists($class, false)) {
                new ReflectionProperty($class, $property)->setValue(null, null);
            }
        }
        try {
            $this->client->followRedirects();

            return $this->client->request('GET', $url);
        } finally {
            $_SERVER = $server;
        }
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
