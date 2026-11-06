<?php

namespace App\Tests\Controller\Admin;

use App\Entity\Member;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DomCrawler\Crawler;

/**
 * The group admin team's pages: approval, archival, unarchival and rename. bwadmin has the Group right at level 10;
 * the test turns "Vegan Travelers" into a group waiting for approval.
 */
#[Group('integration')]
final class GroupAdminTest extends WebTestCase
{
    private const string WAITING = 'Vegan Travelers';
    private const string HIKING = 'Hiking Enthusiasts';

    private KernelBrowser $client;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->connection()->executeStatement('UPDATE `group` SET approved = 0 WHERE name = ?', [self::WAITING]);
        $this->client->loginUser($this->member('bwadmin'));
    }

    public function testAWaitingGroupIsApproved(): void
    {
        $crawler = $this->client->request('GET', '/admin/groups/approval');

        $this->assertResponseIsSuccessful();
        $card = $this->card($crawler, self::WAITING);
        self::assertCount(1, $card->filter('form[action$="/discuss"]'));
        self::assertSame('1', trim($crawler->filter('.p-hero__tab--active .p-hero__tab-count')->text()));

        $this->client->request('POST', $card->filter('form[action$="/approve"]')->attr('action'), ['_token' => $this->token($crawler)]);

        $this->assertResponseRedirects('/admin/groups/approval');
        self::assertSame(1, $this->approved(self::WAITING));
        self::assertEmailCount(1);
        $crawler = $this->client->followRedirect();
        self::assertSelectorExists('.c-cnews-empty');
        self::assertCount(0, $crawler->filter('.p-hero__tab--active .p-hero__tab-count'));
    }

    public function testAGroupIsDiscussedAndDismissed(): void
    {
        $crawler = $this->client->request('GET', '/admin/groups/approval');
        $token = $this->token($crawler);
        $card = $this->card($crawler, self::WAITING);

        $this->client->request('POST', $card->filter('form[action$="/discuss"]')->attr('action'), ['_token' => $token]);
        self::assertSame(3, $this->approved(self::WAITING));
        $crawler = $this->client->request('GET', '/admin/groups/approval');
        $card = $this->card($crawler, self::WAITING);
        self::assertCount(1, $card->filter('.c-tag--orange'));
        self::assertCount(0, $card->filter('form[action$="/discuss"]'));

        $this->client->request('POST', $card->filter('form[action$="/dismiss"]')->attr('action'), ['_token' => $token]);
        self::assertSame(2, $this->approved(self::WAITING));
    }

    public function testTheActionsArePostsWithTheToken(): void
    {
        $id = $this->id(self::WAITING);

        $this->client->request('GET', '/admin/groups/' . $id . '/approve');
        $this->assertResponseStatusCodeSame(405);
        $this->client->request('POST', '/admin/groups/' . $id . '/approve', ['_token' => 'wrong']);
        $this->assertResponseStatusCodeSame(403);
        self::assertSame(0, $this->approved(self::WAITING));
    }

    public function testAGroupIsArchivedAndUnarchived(): void
    {
        $crawler = $this->client->request('GET', '/admin/groups/archival?q=Hiking');
        $this->assertResponseIsSuccessful();
        self::assertCount(1, $crawler->filter('.p-gadmin__archive-row'));
        $id = $this->id(self::HIKING);

        $this->client->request('POST', '/admin/groups/' . $id . '/archive', ['_token' => $this->token($crawler)]);
        // back to the page with the search
        $this->assertResponseRedirects('/admin/groups/archival?q=Hiking');
        self::assertSame('[Archived] ' . self::HIKING, $this->groupName($id));

        $crawler = $this->client->request('GET', '/admin/groups/archival?q=Hiking');
        self::assertCount(0, $crawler->filter('.p-gadmin__archive-row'));
        $crawler = $this->client->request('GET', '/admin/groups/unarchival');
        $row = $crawler->filter('.p-gadmin__archive-row')->reduce(static fn (Crawler $row): bool => str_contains($row->text(), self::HIKING));
        self::assertCount(1, $row);

        $this->client->request('POST', '/admin/groups/' . $id . '/unarchive', ['_token' => $this->token($crawler)]);
        $this->assertResponseRedirects('/admin/groups/unarchival');
        self::assertSame(self::HIKING, $this->groupName($id));
    }

    public function testAGroupIsRenamed(): void
    {
        $id = $this->id(self::HIKING);
        $crawler = $this->client->request('GET', '/admin/groups/rename');
        $this->assertResponseIsSuccessful();

        $this->client->submit($crawler->filter('form[name="form"]')->form([
            'form[group]' => (string) $id,
            'form[new_name]' => 'Zz test hikers',
        ]));

        $this->assertResponseRedirects('/admin/groups/rename');
        self::assertSame('Zz test hikers', $this->groupName($id));
    }

    public function testOthersDontSeeThePages(): void
    {
        $this->client->loginUser($this->member('member-6'));

        foreach (['/admin/groups/approval', '/admin/groups/archival', '/admin/groups/unarchival', '/admin/groups/rename'] as $page) {
            $this->client->request('GET', $page);
            $this->assertResponseStatusCodeSame(403);
        }
    }

    private function card(Crawler $crawler, string $name): Crawler
    {
        $card = $crawler->filter('.p-gadmin-request')->reduce(static fn (Crawler $card): bool => str_contains($card->text(), $name));
        self::assertCount(1, $card);

        return $card;
    }

    private function token(Crawler $crawler): string
    {
        return (string) $crawler->filter('main form[method="post"] input[name="_token"]')->first()->attr('value');
    }

    private function id(string $name): int
    {
        return (int) $this->connection()->fetchOne('SELECT id FROM `group` WHERE name = ?', [$name]);
    }

    private function groupName(int $id): string
    {
        return (string) $this->connection()->fetchOne('SELECT name FROM `group` WHERE id = ?', [$id]);
    }

    private function approved(string $name): int
    {
        return (int) $this->connection()->fetchOne('SELECT approved FROM `group` WHERE name = ?', [$name]);
    }

    private function member(string $username): Member
    {
        $member = static::getContainer()->get(EntityManagerInterface::class)
            ->getRepository(Member::class)->findOneBy(['username' => $username]);
        self::assertInstanceOf(Member::class, $member);

        return $member;
    }

    private function connection(): Connection
    {
        return static::getContainer()->get(Connection::class);
    }
}
