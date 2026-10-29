<?php

namespace App\Tests\Controller;

use App\Entity\Member;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * The RSS feeds of the forum are discontinued: their URLs don't exist any more.
 */
#[Group('integration')]
class RssFeedRemovedTest extends WebTestCase
{
    /**
     * @return iterable<string, array{string}>
     */
    public static function feedUrls(): iterable
    {
        yield 'forum threads' => ['/rss/forumthreads'];
        yield 'overview' => ['/rss'];
        yield 'alias' => ['/feed'];
        yield 'thread' => ['/rss/thread/1'];
    }

    #[DataProvider('feedUrls')]
    public function testFeedIsGone(string $url): void
    {
        $client = static::createClient();
        $member = static::getContainer()->get(EntityManagerInterface::class)
            ->getRepository(Member::class)->findOneBy(['username' => 'member-2']);
        self::assertInstanceOf(Member::class, $member);
        $client->loginUser($member);

        $client->request('GET', $url);

        self::assertResponseStatusCodeSame(404);
    }
}
