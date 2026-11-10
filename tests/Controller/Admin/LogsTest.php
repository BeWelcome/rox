<?php

namespace App\Tests\Controller\Admin;

use App\Entity\Log;
use App\Entity\Member;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * The logs: the newest first in a table, filtered by type chips and member; the group logs without filters.
 */
#[Group('integration')]
final class LogsTest extends WebTestCase
{
    private KernelBrowser $client;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->client->disableReboot();
        $this->client->loginUser($this->member('bwadmin'));
        $this->log('Login', 'zztest logged in', $this->member('member-2'), '+2 days');
        $this->log('Group', 'zztest joined a group', $this->member('member-3'), '+1 day');
        $this->log('Signup', 'zztest system entry', null, '+3 days');
    }

    public function testTheLogsAreFilteredByTypeAndMember(): void
    {
        $crawler = $this->client->request('GET', '/admin/logs');

        self::assertResponseIsSuccessful();
        self::assertGreaterThan(1, $crawler->filter('.c-logs__chip input[name="log[types][]"]')->count());
        self::assertCount(0, $crawler->filter('.c-cadmin__clear'));
        // newest first: the entry without a member is the system's
        $first = $crawler->filter('.c-logs__row')->eq(1);
        self::assertSame('zztest system entry', $first->filter('.c-logs__message')->text());
        self::assertSame($this->trans('admin.logs.system'), $first->filter('.c-logs__system')->text());

        $crawler = $this->client->request('GET', '/admin/logs?' . http_build_query(['log' => ['types' => ['Group', 'Login']]]));
        self::assertSame(['zztest logged in', 'zztest joined a group'], \array_slice($this->messages($crawler), 0, 2));
        // several types can be chosen; they apply with Show logs, not on each click
        self::assertSame(['Group', 'Login'], $crawler->filter('.c-logs__chip input:checked')->each(static fn (Crawler $box) => $box->attr('value')));
        self::assertCount(0, $crawler->filter('.c-logs__chip input[onchange]'));
        self::assertCount(1, $crawler->filter('form.c-logs__filters button[type="submit"].c-logs__show'));
        self::assertCount(1, $crawler->filter('.c-cadmin__clear[href="/admin/logs"]'));

        $crawler = $this->client->request('GET', '/admin/logs?' . http_build_query(['log' => ['types' => ['Login'], 'username' => 'member-2']]));
        self::assertSame('zztest logged in', $this->messages($crawler)[0]);
        self::assertSame('member-2', $crawler->filter('.c-logs__person')->first()->text());
        self::assertSame('member-2', $crawler->filter('input[name="log[username]"]')->attr('value'));
        // the member filter suggests members; picking one filters by them
        self::assertCount(1, $crawler->filter('input#c-logs-member[name="log[username]"][data-member-suggest="submit"][aria-controls="c-logs-member_list"]'));
        self::assertCount(1, $crawler->filter('ul#c-logs-member_list.c-member-suggest'));
        foreach ($crawler->filter('.c-logs__row .c-tag')->each(static fn (Crawler $tag) => $tag->text()) as $type) {
            self::assertSame('Login', $type);
        }
    }

    public function testNothingFoundSaysSo(): void
    {
        $crawler = $this->client->request('GET', '/admin/logs?' . http_build_query(['log' => ['types' => ['Login'], 'username' => 'member-6']]));

        self::assertResponseIsSuccessful();
        self::assertSame($this->trans('admin.logs.empty.title'), $crawler->filter('.c-cnews-empty__title')->text());
    }

    public function testTheGroupLogsHaveNoFilters(): void
    {
        $crawler = $this->client->request('GET', '/admin/logs/groups');

        self::assertResponseIsSuccessful();
        self::assertCount(0, $crawler->filter('.c-logs__filters'));
        self::assertContains('zztest joined a group', $this->messages($crawler));
        self::assertNotContains('zztest logged in', $this->messages($crawler));
    }

    /**
     * @return list<string>
     */
    private function messages(Crawler $crawler): array
    {
        return $crawler->filter('.c-logs__row .c-logs__message')->each(static fn (Crawler $message) => $message->text());
    }

    private function log(string $type, string $message, ?Member $member, string $when): void
    {
        $entityManager = static::getContainer()->get(EntityManagerInterface::class);
        $log = new Log();
        $log->setType($type);
        $log->setLogMessage($message);
        $log->setMember($member);
        $entityManager->persist($log);
        $entityManager->flush();
        // the entity sets created when it's saved
        $entityManager->getConnection()->executeStatement('UPDATE logs SET created = ? WHERE id = ?', [new DateTime($when)->format('Y-m-d H:i:s'), $log->getId()]);
    }

    private function member(string $username): Member
    {
        return static::getContainer()->get(EntityManagerInterface::class)->getRepository(Member::class)->findOneBy(['username' => $username]);
    }

    /**
     * A text as the page shows it: the test database may have no translations (CI), then the key.
     *
     * @param array<string, int|string> $parameters
     */
    private function trans(string $id, array $parameters = []): string
    {
        return static::getContainer()->get(TranslatorInterface::class)->trans($id, $parameters);
    }
}
