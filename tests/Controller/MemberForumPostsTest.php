<?php

namespace App\Tests\Controller;

use App\Entity\ForumPost;
use App\Entity\Member;
use App\Entity\Preference;
use App\Repository\ForumPostRepository;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DomCrawler\Crawler;

/**
 * A member's forum posts on their profile: a card per post with its thread, linked to the post, and its text. The
 * page read the town of the author from a field members don't have, which failed with strict variables (dev) and
 * showed nothing in production.
 */
#[Group('integration')]
class MemberForumPostsTest extends WebTestCase
{
    private KernelBrowser $client;
    private EntityManagerInterface $entityManager;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->entityManager = static::getContainer()->get(EntityManagerInterface::class);
    }

    public function testTheMemberSeesTheirPostsAsCards(): void
    {
        $member = $this->member('member-1');
        $posts = $this->posts($member);
        self::assertNotEmpty($posts);
        $this->client->loginUser($member);

        $crawler = $this->client->request('GET', '/members/member-1/posts');

        $this->assertResponseIsSuccessful();
        self::assertCount(\count($posts), $crawler->filter('.p-forum__list .p-member-post'));
        foreach ($posts as $post) {
            $card = $crawler->filter('#post' . $post->getId());
            self::assertCount(1, $card);
            self::assertStringEndsWith('#post' . $post->getId(), (string) $card->filter('.p-member-post__head a.c-home-item__title')->attr('href'));
            self::assertNotSame('', trim($card->filter('.p-member-post__text')->text()));
        }
    }

    public function testTheSearchFindsThePostsWithTheWords(): void
    {
        $member = $this->member('member-1');
        $posts = $this->posts($member);
        $word = $this->wordOnlyIn($posts[0], $posts);
        $this->client->loginUser($member);

        $crawler = $this->client->request('GET', '/members/member-1/posts');
        $this->client->submit($crawler->filter('form.p-forum__search')->form(), ['form[q]' => $word]);
        $this->assertResponseRedirects();
        $crawler = $this->client->followRedirect();

        $this->assertResponseIsSuccessful();
        self::assertSame(['post' . $posts[0]->getId()], $crawler->filter('.p-member-post')->each(
            static fn (Crawler $card): string => (string) $card->attr('id')
        ));
    }

    public function testOthersAreSentToTheProfileWhenThePostsAreHidden(): void
    {
        $member = $this->member('member-1');
        $connection = static::getContainer()->get(Connection::class);
        $preferenceId = $connection->fetchOne('SELECT id FROM preferences WHERE codeName = ?', [Preference::SHOW_FORUMS_POSTS]);
        $connection->executeStatement('DELETE FROM memberspreferences WHERE IdMember = ? AND IdPreference = ?', [$member->getId(), $preferenceId]);
        $connection->executeStatement(
            "INSERT INTO memberspreferences (IdMember, IdPreference, Value, created, updated) VALUES (?, ?, 'No', NOW(), NOW())",
            [$member->getId(), $preferenceId]
        );
        $this->client->loginUser($this->member('member-2'));

        $this->client->request('GET', '/members/member-1/posts');

        $this->assertResponseRedirects('/members/member-1');
    }

    /**
     * @return list<ForumPost> newest first, as the page lists them
     */
    private function posts(Member $member): array
    {
        /** @var ForumPostRepository $repository */
        $repository = $this->entityManager->getRepository(ForumPost::class);

        return array_values(iterator_to_array($repository->getForumPostsByMember($member, '', 1, 100)->getCurrentPageResults()));
    }

    /**
     * @param list<ForumPost> $posts
     */
    private function wordOnlyIn(ForumPost $post, array $posts): string
    {
        foreach (preg_split('/\W+/u', strip_tags((string) $post->getMessage()), -1, \PREG_SPLIT_NO_EMPTY) as $word) {
            if (mb_strlen($word) < 6) {
                continue;
            }
            $elsewhere = array_filter($posts, static fn (ForumPost $other): bool => $other !== $post
                && false !== mb_stripos((string) $other->getMessage(), $word));
            if ([] === $elsewhere) {
                return $word;
            }
        }
        self::fail('No word only in post ' . $post->getId());
    }

    private function member(string $username): Member
    {
        $member = $this->entityManager->getRepository(Member::class)->findOneBy(['username' => $username]);
        self::assertInstanceOf(Member::class, $member);

        return $member;
    }
}
