<?php

namespace App\Tests\Controller;

use App\Doctrine\MemberStatusType;
use App\Repository\MemberRepository;
use Doctrine\DBAL\Connection;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * A member who is banned, suspended or taken out while they are logged in: their next request ends the login and
 * leads to the login page. (The page ran without a member and failed with a server error.)
 */
#[Group('integration')]
final class MemberNoLongerWelcomeTest extends WebTestCase
{
    private KernelBrowser $client;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->client->loginUser(static::getContainer()->get(MemberRepository::class)->loadUserByIdentifier('member-2'));
    }

    /**
     * @return iterable<string, array{string, string}>
     */
    public static function requests(): iterable
    {
        yield 'banned, a page for members' => [MemberStatusType::BANNED, '/conversations/'];
        yield 'suspended, a page for members' => [MemberStatusType::SUSPENDED, '/conversations/'];
        yield 'taken out, a page for members' => [MemberStatusType::TAKEN_OUT, '/conversations/'];
        yield 'asked to leave, the home page' => [MemberStatusType::ASKED_TO_LEAVE, '/'];
        yield 'banned, a widget of the home page' => [MemberStatusType::BANNED, '/widget/threads'];
        yield 'banned, a page for everybody' => [MemberStatusType::BANNED, '/about'];
    }

    #[DataProvider('requests')]
    public function testTheNextRequestEndsTheLoginAndLeadsToTheLoginPage(string $status, string $path): void
    {
        $this->client->request('GET', '/conversations/');
        self::assertResponseIsSuccessful();

        static::getContainer()->get(Connection::class)
            ->executeStatement("UPDATE member SET Status = ? WHERE Username = 'member-2'", [$status]);

        $this->client->request('GET', $path);
        self::assertResponseRedirects('/login');

        // nobody is logged in any more: a page for members asks for the login, as it does a visitor
        $this->client->request('GET', '/conversations/');
        self::assertResponseRedirects();
        self::assertStringEndsWith('/login', (string) $this->client->getResponse()->headers->get('Location'));
        $this->client->request('GET', '/about');
        self::assertResponseIsSuccessful();
    }

    public function testAMemberInGoodStandingGoesOn(): void
    {
        $this->client->request('GET', '/conversations/');
        self::assertResponseIsSuccessful();
        $this->client->request('GET', '/conversations/');
        self::assertResponseIsSuccessful();
    }
}
