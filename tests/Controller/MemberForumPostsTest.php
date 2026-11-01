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
 * A member's forum posts on their profile: a card per post with its thread, linked to the post, and its text, only
 * those the viewer may read in the forum. The page read the town of the author from a field members don't have,
 * which failed with strict variables (dev) and showed nothing in production; it listed deleted posts and those for a
 * group's members or moderators only to everybody.
 * In the fixtures member-1 wrote posts 1 and 3 in the agora and 5 in Hiking Enthusiasts (members: member-1 to 5).
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
        $this->showPostsToOthers('No');
        $this->client->loginUser($this->member('member-2'));

        $this->client->request('GET', '/members/member-1/posts');

        $this->assertResponseRedirects('/members/member-1');
    }

    public function testOthersSeeOnlyThePostsTheyMayReadInTheForum(): void
    {
        $this->showPostsToOthers('Yes');
        $this->setPost(3, 'PostDeleted', 'Deleted');
        $this->setPost(5, 'PostVisibility', 'GroupOnly');

        // not in Hiking Enthusiasts
        self::assertSame([1], $this->shownTo('member-6'));
        self::assertSame(1, $this->tabCount());
        // in it
        self::assertSame([5, 1], $this->shownTo('member-2'));
        self::assertSame(2, $this->tabCount());
    }

    public function testThePostsOfAMembersOnlyGroupAreForItsMembers(): void
    {
        $this->showPostsToOthers('Yes');
        static::getContainer()->get(Connection::class)
            ->executeStatement("UPDATE `group` SET members_only = 1 WHERE name = 'Hiking Enthusiasts'");

        self::assertSame([3, 1], $this->shownTo('member-6'));
        self::assertSame([5, 3, 1], $this->shownTo('member-2'));
    }

    public function testPostsForModeratorsAreForModeratorsOnly(): void
    {
        $this->showPostsToOthers('Yes');
        $this->setPost(1, 'PostVisibility', 'ModeratorOnly');
        $this->setPost(3, 'PostDeleted', 'Deleted');

        self::assertSame([5], $this->shownTo('member-2'));
        // the author too reads them in the forum as the others do
        self::assertSame([5], $this->shownTo('member-1'));
        // bwadmin is a forum moderator in the fixtures: all of them, the deleted one struck through
        self::assertSame([5, 3, 1], $this->shownTo('bwadmin'));
        self::assertSame(3, $this->tabCount());
        self::assertCount(1, $this->client->getCrawler()->filter('#post3.is-deleted s'));
    }

    /**
     * @return list<int> the ids of the posts on member-1's posts page, as the viewer sees it
     */
    private function shownTo(string $viewer): array
    {
        $this->client->loginUser($this->member($viewer));
        $crawler = $this->client->request('GET', '/members/member-1/posts');
        $this->assertResponseIsSuccessful();

        return $crawler->filter('.p-member-post')->each(
            static fn (Crawler $card): int => (int) substr((string) $card->attr('id'), \strlen('post'))
        );
    }

    /**
     * The number in the profile's "Forum posts" tab.
     */
    private function tabCount(): int
    {
        $tab = $this->client->getCrawler()->filter('a[href$="/members/member-1/posts"]')->reduce(
            static fn (Crawler $link): bool => 1 === preg_match('/\d/', $link->text())
        );
        self::assertGreaterThan(0, $tab->count());
        preg_match('/(\d+)\s*$/', $tab->first()->text(), $matches);

        return (int) ($matches[1] ?? -1);
    }

    private function showPostsToOthers(string $value): void
    {
        $member = $this->member('member-1');
        $connection = static::getContainer()->get(Connection::class);
        $preferenceId = $connection->fetchOne('SELECT id FROM preferences WHERE codeName = ?', [Preference::SHOW_FORUMS_POSTS]);
        $connection->executeStatement('DELETE FROM memberspreferences WHERE IdMember = ? AND IdPreference = ?', [$member->getId(), $preferenceId]);
        $connection->executeStatement(
            'INSERT INTO memberspreferences (IdMember, IdPreference, Value, created, updated) VALUES (?, ?, ?, NOW(), NOW())',
            [$member->getId(), $preferenceId, $value]
        );
    }

    private function setPost(int $id, string $column, string $value): void
    {
        static::getContainer()->get(Connection::class)
            ->executeStatement(\sprintf('UPDATE forum_post SET %s = ? WHERE id = ?', $column), [$value, $id]);
    }

    /**
     * @return list<ForumPost> newest first, as the page lists them
     */
    private function posts(Member $member): array
    {
        /** @var ForumPostRepository $repository */
        $repository = $this->entityManager->getRepository(ForumPost::class);

        return array_values(iterator_to_array(
            $repository->getForumPostsByMember($member, $member, false, '', 1, 100)->getCurrentPageResults()
        ));
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
