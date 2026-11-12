<?php

namespace App\Tests\Controller;

use App\Entity\Member;
use App\Entity\TeamCoordinator;
use App\Model\About\VolunteerTeams;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DomCrawler\Crawler;

/**
 * The About pages: the menu with the page marked, the page's head with its title, then its card(s); Get active with
 * the teams' coordinators, who the admins set in the volunteer tools.
 */
#[Group('integration')]
final class AboutPagesTest extends WebTestCase
{
    /**
     * @return iterable<string, array{string, string}>
     */
    public static function pages(): iterable
    {
        yield 'about' => ['/about', '/about'];
        yield 'faq' => ['/faq', '/faq'];
        yield 'contact us' => ['/about/feedback', '/about/feedback'];
        yield 'the people' => ['/about/thepeople', '/about/thepeople'];
        yield 'get active' => ['/about/getactive', '/about/getactive'];
        yield 'press' => ['/press-information', '/press-information'];
        yield 'comment guidelines' => ['/about/commentguidelines', '/about/commentguidelines'];
        yield 'statistics' => ['/about/statistics', '/about/statistics'];
        yield 'credits' => ['/about/credits', '/about/credits'];
        yield 'imprint' => ['/impressum', '/impressum'];
    }

    #[DataProvider('pages')]
    public function testThePageHasTheAboutLayout(string $uri, string $menu): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', $uri);

        self::assertResponseIsSuccessful();
        self::assertCount(1, $crawler->filter('h1'));
        self::assertCount(1, $crawler->filter('main.p-about .p-about__head h1.p-about__title'));
        self::assertSame($menu, $crawler->filter('.p-about__nav .p-about__link.is-active[aria-current="page"]')->attr('href'));
        // a visitor gets the front page's top bar, not the login form
        self::assertCount(1, $crawler->filter('header.bw-visitor-top .fp-top__join'));
        self::assertCount(0, $crawler->filter('input[name="_password"]'));
        // no Bootstrap grid or cards left
        self::assertCount(0, $crawler->filter('.p-about__main .row, .p-about__main .card, .p-about__main .carousel'));
    }

    public function testTheMenuIsTheSameOnEveryPage(): void
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/faq');

        $links = $crawler->filter('.p-about__nav .p-about__link')->each(static fn (Crawler $link): string => (string) $link->attr('href'));
        self::assertSame(
            ['/about', '/faq', '/about/feedback', '/about/thepeople', '/about/getactive', '/press-information', '/bod', '/bv', '/terms/en', '/about/commentguidelines', '/about/statistics', '/about/credits', '/impressum'],
            $links,
        );
        self::assertCount(2, $crawler->filter('.p-about__nav .p-about__external'));
        // the FAQ's categories are pills in the page
        self::assertGreaterThan(1, $crawler->filter('.p-about__head .p-about__pills .c-pill')->count());
        self::assertCount(1, $crawler->filter('.p-about__head .c-pill.is-active[aria-current="page"]'));
    }

    public function testThePoliciesHaveTheirContentsAsTheMenu(): void
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/terms/en');

        self::assertResponseIsSuccessful();
        self::assertSame('/about', $crawler->filter('.p-policies__nav a.p-policies__back')->attr('href'));
        self::assertCount(1, $crawler->filter('.p-policies__nav details.js-policies-toc summary.is-active'));
        self::assertGreaterThan(3, $crawler->filter('.p-policies__nav .p-policies__toc nav a[data-section]')->count());
        self::assertCount(3, $crawler->filter('.p-about__head .p-policies__tab'));
        self::assertCount(0, $crawler->filter('.p-about__head h1'));
        self::assertCount(1, $crawler->filter('.p-policies__card h1'));
    }

    public function testGetActiveShowsTheTeamsWithTheirCoordinators(): void
    {
        $client = static::createClient();
        $entityManager = static::getContainer()->get(EntityManagerInterface::class);
        static::getContainer()->get(Connection::class)->executeStatement('DELETE FROM team_coordinator');
        $entityManager->persist(new TeamCoordinator('dev', $this->member('member-3')));
        $entityManager->flush();

        $crawler = $client->request('GET', '/about/getactive');

        $tiles = $crawler->filter('.p-getactive__tile');
        self::assertCount(\count(VolunteerTeams::TEAMS), $tiles);
        self::assertSame(
            array_map(static fn (array $team): string => $team['group'], array_values(VolunteerTeams::TEAMS)),
            $tiles->each(static fn (Crawler $tile): string => (string) $tile->attr('data-group')),
        );
        self::assertCount(4, $crawler->filter('.p-about__pills .c-pill[data-group]'));
        $dev = $tiles->first();
        self::assertSame('member-3', $dev->filter('.p-getactive__name')->text());
        self::assertSame('/new/message/member-3', $dev->filter('.p-getactive__contact a.c-pill')->attr('href'));
        // the tags are the links of the translation, split at |
        self::assertGreaterThan(0, $dev->filter('.p-getactive__tags .c-tag')->count());
        self::assertCount(1, $crawler->filter('.p-getactive__contact'));
    }

    public function testTheAdminsSetTheCoordinators(): void
    {
        $client = static::createClient();
        $client->disableReboot();
        $client->loginUser($this->member('bwadmin'));

        $crawler = $client->request('GET', '/admin/tools/coordinators');
        self::assertResponseIsSuccessful();
        self::assertCount(\count(VolunteerTeams::TEAMS), $crawler->filter('.c-vtools-coordinators__row input[data-member-suggest]'));

        // an unknown username saves nothing
        $client->submit($crawler->selectButton('save')->form(), ['team_coordinators[dev]' => 'zztest-nobody', 'team_coordinators[pr]' => 'member-4']);
        self::assertResponseStatusCodeSame(422);
        self::assertNotSame('member-4', $this->coordinator('pr'));

        $crawler = $client->request('GET', '/admin/tools/coordinators');
        $client->submit($crawler->selectButton('save')->form(), ['team_coordinators[dev]' => '', 'team_coordinators[pr]' => 'member-4']);
        self::assertResponseRedirects('/admin/tools/coordinators');
        self::assertNull($this->coordinator('dev'));
        self::assertSame('member-4', $this->coordinator('pr'));

        $client->loginUser($this->member('member-2'));
        $client->request('GET', '/admin/tools/coordinators');
        self::assertResponseStatusCodeSame(403);
    }

    private function coordinator(string $team): ?string
    {
        $username = static::getContainer()->get(Connection::class)->fetchOne(
            'SELECT m.Username FROM team_coordinator t JOIN member m ON m.id = t.member_id WHERE t.team = ?',
            [$team],
        );

        return false === $username ? null : (string) $username;
    }

    private function member(string $username): Member
    {
        return static::getContainer()->get(EntityManagerInterface::class)->getRepository(Member::class)->findOneBy(['username' => $username]);
    }
}
