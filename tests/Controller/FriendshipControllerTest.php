<?php

namespace App\Tests\Controller;

use App\Entity\Friend;
use App\Entity\Member;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DomCrawler\Crawler;

/**
 * A member asks, the other one accepts on the site; then both profiles show the friendship, and either side can
 * remove it. A pending request can be withdrawn.
 */
#[Group('integration')]
final class FriendshipControllerTest extends WebTestCase
{
    private KernelBrowser $client;
    private EntityManagerInterface $entityManager;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->client->disableReboot();
        $this->entityManager = static::getContainer()->get(EntityManagerInterface::class);
    }

    public function testARequestAcceptedOnTheSiteShowsOnBothProfiles(): void
    {
        $this->ask('member-6', 'member-7');
        self::assertResponseRedirects('/members/member-6/friendships');
        self::assertEmailCount(1);
        self::assertNotNull($this->friendship('member-6', 'member-7'));
        self::assertFalse($this->friendship('member-6', 'member-7')->isConfirmed());

        // pending: on nobody's friends page
        $crawler = $this->client->request('GET', '/members/member-7/friends');
        self::assertStringNotContainsString('member-6', $this->friendsList($crawler));

        // the receiver answers from their own friendships page
        $this->client->loginUser($this->member('member-7'));
        $crawler = $this->client->request('GET', '/members/member-7/friendships');
        self::assertResponseIsSuccessful();
        $form = $crawler->filter('form[action$="/members/member-6/friendship/edit"] button[value="accept"]')->form();
        $this->client->submit($form);
        self::assertResponseRedirects('/members/member-7/friendships');
        self::assertTrue($this->friendship('member-6', 'member-7')->isConfirmed());

        // each side shows the other one
        $crawler = $this->client->request('GET', '/members/member-7/friends');
        self::assertStringContainsString('member-6', $this->friendsList($crawler));
        $crawler = $this->client->request('GET', '/members/member-6/friends');
        self::assertStringContainsString('member-7', $this->friendsList($crawler));

        // and either side can remove it
        $this->client->loginUser($this->member('member-6'));
        $crawler = $this->client->request('GET', '/members/member-6/friendship/edit');
        self::assertResponseRedirects('/members/member-6');
        $crawler = $this->client->request('GET', '/members/member-7/friendship/edit');
        $this->client->submit($crawler->filter('button[value="remove"]')->form());
        self::assertResponseRedirects('/members/member-6/friendships');
        self::assertNull($this->friendship('member-6', 'member-7'));
    }

    public function testTheRequesterWithdrawsButCantAccept(): void
    {
        $this->ask('member-8', 'member-9');

        $crawler = $this->client->request('GET', '/members/member-9/friendship/edit');
        self::assertResponseIsSuccessful();
        self::assertCount(0, $crawler->filter('button[value="accept"]'));

        // made up: changes nothing
        $token = $crawler->filter('input[name="_token"]')->attr('value');
        $this->client->request('POST', '/members/member-9/friendship/edit', ['_token' => $token, 'friendship' => 'accept']);
        self::assertFalse($this->friendship('member-8', 'member-9')->isConfirmed());

        $crawler = $this->client->request('GET', '/members/member-9/friendship/edit');
        $this->client->submit($crawler->filter('button[value="withdraw"]')->form());
        self::assertNull($this->friendship('member-8', 'member-9'));
    }

    public function testAskingTwiceLeadsToTheFriendship(): void
    {
        $this->ask('member-6', 'member-8');

        $this->client->request('GET', '/members/member-8/friendship/add');
        self::assertResponseRedirects('/members/member-8/friendship/edit');

        // the other side asking too finds the request to answer
        $this->client->loginUser($this->member('member-8'));
        $this->client->request('GET', '/members/member-6/friendship/add');
        self::assertResponseRedirects('/members/member-6/friendship/edit');
        $crawler = $this->client->followRedirect();
        self::assertCount(1, $crawler->filter('button[value="accept"]'));
    }

    public function testTheFriendshipsPageIsTheOwnersOnly(): void
    {
        $this->client->loginUser($this->member('member-6'));

        $this->client->request('GET', '/members/member-7/friendships');
        self::assertResponseRedirects('/members/member-7/friends');
    }

    private function ask(string $requester, string $receiver): void
    {
        $this->client->loginUser($this->member($requester));
        $crawler = $this->client->request('GET', '/members/' . $receiver . '/friendship/add');
        self::assertResponseIsSuccessful();
        $this->client->submit($crawler->filter('button[name="add_friendship"]')->form());
    }

    private function friendsList(Crawler $crawler): string
    {
        $list = $crawler->filter('.p-friends-page__grid');

        return $list->count() > 0 ? $list->text() : '';
    }

    private function friendship(string $one, string $other): ?Friend
    {
        $this->entityManager->clear();

        return $this->entityManager->getRepository(Friend::class)->findFriendshipBetween(
            $this->member($one),
            $this->member($other),
        );
    }

    private function member(string $username): Member
    {
        $member = $this->entityManager->getRepository(Member::class)->findOneBy(['username' => $username]);
        self::assertInstanceOf(Member::class, $member);

        return $member;
    }
}
