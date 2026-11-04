<?php

namespace App\Tests\Controller\Admin;

use App\Doctrine\MessageStatusType;
use App\Entity\LoginMessage;
use App\Entity\Member;
use App\Entity\Message;
use DateTime;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DomCrawler\Crawler;

/**
 * The volunteer tools as the volunteers use them: the sidebar with the tools their rights open, grouped, and the
 * rights in the header; each tool in its card. member-9 gets the rights of a test here; bwadmin has them all.
 */
#[Group('integration')]
final class VolunteerToolsTest extends WebTestCase
{
    private KernelBrowser $client;
    private EntityManagerInterface $entityManager;
    private Connection $connection;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->entityManager = static::getContainer()->get(EntityManagerInterface::class);
        $this->connection = static::getContainer()->get(Connection::class);
    }

    public function testTheSidebarListsTheToolsOfTheVolunteersRights(): void
    {
        $this->volunteer('Checker');

        // the first of their tools
        $this->client->request('GET', '/admin/tools');
        self::assertResponseRedirects('/admin/tools/topspammer');

        $crawler = $this->client->followRedirect();
        self::assertSame(
            ['/admin/tools/topspammer', '/admin/tools/damagedone', '/admin/tools/countryage'],
            $crawler->filter('.c-vtools__item')->each(static fn (Crawler $item): string => (string) $item->attr('href')),
        );
        // two groups: spam checks, community
        self::assertCount(2, $crawler->filter('.c-vtools__group'));
        self::assertSame('/admin/tools/topspammer', $crawler->filter('.c-vtools__item.is-active')->attr('href'));
        self::assertCount(1, $crawler->filter('.c-vtools__rights .c-tag'));

        foreach (['/admin/tools/findmember', '/admin/tools/login_messages/show', '/admin/tools/uploaded_images/1'] as $other) {
            $this->client->request('GET', $other);
            self::assertResponseStatusCodeSame(403, $other);
        }
        // the Feedback tool is gone
        $this->client->request('GET', '/admin/tools/check/feedback');
        self::assertResponseStatusCodeSame(404);
    }

    public function testFindMemberSearchesUsernamesNamesAndEmailAddresses(): void
    {
        $this->connection->executeStatement("UPDATE member SET name = 'Zztestina Findable' WHERE Username = 'member-3'");
        $this->volunteer('SafetyTeam');

        $crawler = $this->client->request('GET', '/admin/tools/findmember');
        $crawler = $this->client->submit($crawler->filter('form[name="find_user_form"]')->form(['find_user_form[term]' => 'Findable']));

        self::assertResponseIsSuccessful();
        $rows = $crawler->filter('.c-vtools-find');
        self::assertCount(1, $rows);
        self::assertSame('member-3', $rows->filter('.c-vtools-member__name')->text());
        self::assertStringContainsString('Zztestina Findable', $rows->text());
        self::assertSame('/members/member-3', $rows->filter('.c-vtools-member')->attr('href'));

        $crawler = $this->client->submit($crawler->filter('form[name="find_user_form"]')->form(['find_user_form[term]' => 'nobody-zztest-at-all']));
        self::assertCount(0, $crawler->filter('.c-vtools-find'));
        self::assertSelectorExists('.c-vtools__empty');
    }

    public function testChangeUsernameRenamesTheMemberOrSaysWhy(): void
    {
        $this->volunteer('SafetyTeam');
        $crawler = $this->client->request('GET', '/admin/tools/change');

        // an unknown member and a taken name
        $this->client->submit($crawler->filter('form[name="change_username_form"]')->form([
            'change_username_form[oldUsername]' => 'zztest-nobody',
            'change_username_form[newUsername]' => 'member-4',
        ]));
        self::assertResponseStatusCodeSame(422);
        $this->assertOneErrorUnder('oldUsername', 'admin.tools.change_username.error.unknown');
        $this->assertOneErrorUnder('newUsername', 'admin.tools.change_username.error.taken');

        // an unknown member and no new name: an error for each
        $this->client->submit($crawler->filter('form[name="change_username_form"]')->form([
            'change_username_form[oldUsername]' => 'zztest-nobody',
            'change_username_form[newUsername]' => '',
        ]));
        self::assertResponseStatusCodeSame(422);
        $this->assertOneErrorUnder('oldUsername', 'admin.tools.change_username.error.unknown');
        $this->assertOneErrorUnder('newUsername', 'admin.tools.change_username.error.empty');

        // not a username, and the same name twice
        $this->client->submit($crawler->filter('form[name="change_username_form"]')->form([
            'change_username_form[oldUsername]' => 'member-7',
            'change_username_form[newUsername]' => 'member-7',
        ]));
        $this->assertOneErrorUnder('newUsername', 'admin.tools.change_username.error.same');
        $this->client->submit($crawler->filter('form[name="change_username_form"]')->form([
            'change_username_form[oldUsername]' => 'member-7',
            'change_username_form[newUsername]' => 'no spaces allowed',
        ]));
        $this->assertOneErrorUnder('newUsername', 'admin.tools.change_username.error.invalid');
        self::assertSame(0, (int) $this->connection->fetchOne("SELECT COUNT(*) FROM member WHERE Username = 'member-7'") - 1);

        $this->client->submit($crawler->filter('form[name="change_username_form"]')->form([
            'change_username_form[oldUsername]' => 'member-7',
            'change_username_form[newUsername]' => 'zztest-renamed',
        ]));
        self::assertResponseRedirects('/admin/tools/change');
        self::assertSame(1, (int) $this->connection->fetchOne("SELECT COUNT(*) FROM member WHERE Username = 'zztest-renamed'"));
        $this->client->followRedirect();
        self::assertSelectorExists('.c-inline-flash');
    }

    public function testMessagesByMemberListsTheCorrespondentsWithTheDirection(): void
    {
        [$writer, $reader] = $this->members(['member-5', 'member-6']);
        $this->entityManager->persist(new Message()
            ->setSender($writer)
            ->setReceiver($reader)
            ->setMessage('zztest hello')
            ->setStatus(MessageStatusType::SENT)
            ->setDeleteRequest(''));
        $this->entityManager->flush();
        $this->volunteer('SafetyTeam');

        $crawler = $this->client->request('GET', '/admin/tools/messages/member');
        $crawler = $this->client->submit($crawler->filter('form[name="form"]')->form(['form[username]' => 'member-5']));

        self::assertResponseIsSuccessful();
        self::assertSame('member-5', $crawler->filter('.c-vtools-mbm__head .c-vtools-member__name')->text());
        $row = $crawler->filter('.c-vtools-mbm')->reduce(static fn (Crawler $row): bool => str_contains($row->text(), 'member-6'));
        self::assertCount(1, $row);
        // a message, sent (the arrow to the right), not received
        self::assertCount(1, $row->filter('.c-vtools-mbm__kinds .fa-envelope'));
        self::assertCount(1, $row->filter('.fa-arrow-left.is-off'));
        self::assertCount(0, $row->filter('.fa-arrow-right.is-off'));
    }

    public function testAgeByCountrySortsByTheChosenColumn(): void
    {
        $this->volunteer('Checker');

        $crawler = $this->client->request('GET', '/admin/tools/countryage', ['sort' => 'Name', 'dir' => 'asc']);
        self::assertResponseIsSuccessful();
        $names = $crawler->filter('.c-vtools-age__country')->each(static fn (Crawler $name): string => $name->text());
        $sorted = $names;
        sort($sorted, \SORT_LOCALE_STRING);
        self::assertSame($sorted, $names);
        self::assertSame('ascending', $crawler->filter('.c-vtools-sort.is-active')->attr('aria-sort'));

        // by default the most members first
        $crawler = $this->client->request('GET', '/admin/tools/countryage');
        $counts = $crawler->filter('.c-vtools-age:not(.c-vtools-table__row--head) > :nth-child(2)')->each(static fn (Crawler $count): int => (int) preg_replace('/\D/', '', $count->text()));
        $byCount = $counts;
        rsort($byCount);
        self::assertSame($byCount, $counts);
    }

    public function testALoginMessageIsPublishedAndExpiredFromTheList(): void
    {
        $this->volunteer('Admin');

        $crawler = $this->client->request('GET', '/admin/tools/login_messages/show');
        self::assertSame('/admin/tools/login_message/add', $crawler->filter('.c-vtools-bar-head a')->attr('href'));

        $crawler = $this->client->request('GET', '/admin/tools/login_message/add');
        // the sidebar keeps Login messages chosen
        self::assertSame('/admin/tools/login_messages/show', $crawler->filter('.c-vtools__item.is-active')->attr('href'));
        $this->client->submit($crawler->filter('form[name="login_message"]')->form([
            'login_message[message]' => '<p>zztest login message</p>',
            'login_message[expires]' => new DateTime('+1 month')->format('Y-m-d H:i'),
        ]));
        self::assertResponseRedirects('/admin/tools/login_messages/show');

        $crawler = $this->client->followRedirect();
        self::assertSelectorExists('.c-inline-flash');
        $row = $crawler->filter('.c-vtools-login')->reduce(static fn (Crawler $row): bool => str_contains($row->text(), 'zztest login message'));
        self::assertCount(1, $row);
        self::assertCount(1, $row->filter('.c-tag--green'));

        $this->client->submit($row->filter('form')->form());
        self::assertResponseRedirects('/admin/tools/login_messages/show');
        $message = $this->entityManager->getRepository(LoginMessage::class)->findOneBy([], ['id' => 'DESC']);
        $this->entityManager->refresh($message);
        self::assertLessThanOrEqual(new DateTime(), $message->getExpires());
    }

    /**
     * The field's error shows once, under it (not also in its label, as the form theme would), translated.
     */
    private function assertOneErrorUnder(string $field, string $key): void
    {
        $crawler = $this->client->getCrawler();
        $group = $crawler->filter('#change_username_form_' . $field)->closest('.o-form-group');
        self::assertCount(0, $group->filter('label .form-error-message, label .invalid-feedback'), $field);
        $errors = $group->filter('.c-massmail-form__errors li')->each(static fn (Crawler $error): string => $error->text());
        $translator = static::getContainer()->get('translator');
        self::assertSame([$translator->trans($key)], $errors, $field);
    }

    /**
     * member-9 with the right, logged in.
     */
    private function volunteer(string $right): void
    {
        $member = $this->members(['member-9'])[0];
        $this->connection->executeStatement(
            "INSERT INTO rightsvolunteers (IdMember, IdRight, Level, Scope, Comment, created, updated)
             SELECT ?, r.id, 10, '\"All\"', 'test', NOW(), NOW() FROM rights r WHERE r.Name = ?",
            [$member->getId(), $right],
        );
        $this->entityManager->refresh($member);
        $this->client->loginUser($member);
    }

    /**
     * @param list<string> $usernames
     *
     * @return list<Member>
     */
    private function members(array $usernames): array
    {
        return array_map(
            fn (string $username): Member => $this->entityManager->getRepository(Member::class)->findOneBy(['username' => $username]),
            $usernames,
        );
    }
}
