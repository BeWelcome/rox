<?php

namespace App\Tests\Controller;

use App\Entity\ForumPost;
use App\Entity\ForumThread;
use App\Entity\Member;
use App\Service\ForumSearch;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * The forum search, now in src. The search index is replaced by a stub that finds given posts: the test is about
 * what the pages do with the hits.
 */
#[Group('integration')]
final class ForumSearchTest extends WebTestCase
{
    private KernelBrowser $client;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->client->disableReboot();
    }

    public function testTheHitsAreThePostsTheMemberMayRead(): void
    {
        $agora = $this->thread('Tips for first-time hosts?');
        $hiking = $this->thread('Weekend hiking meetup - who is in?');
        $this->entityManager()->getConnection()->executeStatement(
            'UPDATE `group` SET members_only = 1 WHERE id = ?',
            [$hiking->getGroup()->getId()],
        );
        $member = $this->member('member-2');
        $this->entityManager()->getConnection()->executeStatement(
            'DELETE FROM group_membership WHERE group_id = ? AND member_id = ?',
            [$hiking->getGroup()->getId(), $member->getId()],
        );
        $this->findPosts([$agora->getFirstPost(), $hiking->getFirstPost()]);
        $this->client->loginUser($member);

        $crawler = $this->client->request('GET', '/forums/search/hosts');

        self::assertResponseIsSuccessful();
        $results = $crawler->filter('#search-results')->text();
        self::assertStringContainsString('Tips for first-time hosts?', $results);
        self::assertStringNotContainsString('Weekend hiking meetup', $results, 'the group\'s posts are for its members');
    }

    public function testTheSearchFormGoesToTheResults(): void
    {
        $this->findPosts([]);
        $this->client->loginUser($this->member('member-2'));

        $this->client->request('GET', '/forums/search?q=hosts');
        self::assertResponseRedirects('/forums/search/hosts');

        $crawler = $this->client->request('GET', '/forums/search/hosts');
        self::assertResponseIsSuccessful();
        self::assertCount(0, $crawler->filter('#search-results'));
    }

    public function testAGroupsSearchStaysInTheGroup(): void
    {
        $hiking = $this->thread('Weekend hiking meetup - who is in?');
        $search = $this->findPosts([$hiking->getFirstPost()]);
        $this->client->loginUser($this->member('member-2'));

        $crawler = $this->client->request('GET', '/group/' . $hiking->getGroup()->getId() . '/search?q=meetup');

        self::assertResponseIsSuccessful();
        self::assertStringContainsString('Weekend hiking meetup', $crawler->filter('#search-results')->text());
        self::assertSame($hiking->getGroup()->getId(), $search->groupId);
    }

    public function testTagUrlsGoToTheForums(): void
    {
        $this->client->loginUser($this->member('member-2'));

        $this->client->request('GET', '/forums/t12-travel');

        self::assertResponseRedirects('/forums', 301);

        // not a tag: the translate page answers
        $this->client->request('GET', '/forums/translate/m14');
        self::assertFalse($this->client->getResponse()->isRedirect());
    }

    /**
     * @param list<ForumPost> $posts
     */
    private function findPosts(array $posts): object
    {
        $stub = new class(array_map(static fn (ForumPost $post): int => (int) $post->getId(), $posts)) extends ForumSearch {
            public ?int $groupId = null;

            /** @param list<int> $postIds */
            public function __construct(private readonly array $postIds)
            {
            }

            public function findPostIds(string $phrase, bool $withDeleted, ?int $groupId = null): array
            {
                $this->groupId = $groupId;

                return $this->postIds;
            }
        };
        static::getContainer()->set(ForumSearch::class, $stub);

        return $stub;
    }

    private function thread(string $title): ForumThread
    {
        $thread = $this->entityManager()->getRepository(ForumThread::class)->findOneBy(['title' => $title]);
        self::assertInstanceOf(ForumThread::class, $thread);

        return $thread;
    }

    private function member(string $username): Member
    {
        $member = $this->entityManager()->getRepository(Member::class)->findOneBy(['username' => $username]);
        self::assertInstanceOf(Member::class, $member);

        return $member;
    }

    private function entityManager(): EntityManagerInterface
    {
        return static::getContainer()->get(EntityManagerInterface::class);
    }
}
