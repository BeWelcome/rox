<?php

namespace App\Tests\Controller;

use App\Entity\Member;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Members retired by data:retention (renamed retired_<id>) are shown as a former member, without a link to their
 * profile: in the forum and in conversations. Sign-ups can't take such a name.
 */
#[Group('integration')]
class FormerMemberDisplayTest extends WebTestCase
{
    private KernelBrowser $client;

    protected function setUp(): void
    {
        $this->client = static::createClient();
    }

    public function testAPostOfARetiredMemberShowsAFormerMember(): void
    {
        // thread 1 of the fixtures has posts of member-1 and member-5
        $this->retire('member-5');
        $this->client->loginUser($this->member('member-2'));

        $crawler = $this->client->request('GET', '/forums/s1');

        self::assertResponseIsSuccessful();
        $this->assertFormerMemberWithoutLink($crawler, '.p-thread-post__name');
        self::assertCount(1, $crawler->filter('a.p-thread-post__name[href$="/members/member-1"]'), 'the other author lost the link');
    }

    public function testAConversationWithARetiredMemberShowsAFormerMember(): void
    {
        // conversation 1 of the fixtures: member-1 and member-2
        $this->retire('member-1');
        $this->client->loginUser($this->member('member-2'));

        $crawler = $this->client->request('GET', '/conversation/1');

        self::assertResponseIsSuccessful();
        $this->assertFormerMemberWithoutLink($crawler, '.c-inbox-head__name');
    }

    public function testASignUpCantTakeTheNameOfARetiredMember(): void
    {
        $crawler = $this->client->request('GET', '/signup');
        $form = $crawler->filter('form[name=signup_form]')->form();
        $form['signup_form[username]'] = 'retired_99999';
        $form['signup_form[email]'] = 'retired_99999@example.org';
        $form['signup_form[password]'] = 'StrongPassword123!';
        $form['signup_form[terms_privacy]'] = '1';

        $this->client->submit($form);

        self::assertFalse($this->client->getResponse()->isRedirect(), 'the sign-up went through');
        self::assertNull(static::getContainer()->get(EntityManagerInterface::class)
            ->getRepository(Member::class)->findOneBy(['username' => 'retired_99999']));
    }

    private function assertFormerMemberWithoutLink(Crawler $crawler, string $nameSelector): void
    {
        $former = static::getContainer()->get(TranslatorInterface::class)->trans('forum.former.member');
        $names = $crawler->filter($nameSelector)->each(static fn (Crawler $name): string => trim($name->text()));
        self::assertContains($former, $names);
        self::assertCount(0, $crawler->filter('a[href*="/members/retired_"]'), 'a link to the former member\'s profile');
        self::assertStringNotContainsString('retired_', $crawler->filter('main')->text(), 'the retired name shows');
    }

    /**
     * As data:retention leaves a member: renamed retired_<id>.
     */
    private function retire(string $username): void
    {
        static::getContainer()->get(Connection::class)->executeStatement(
            "UPDATE member SET Username = CONCAT('retired_', id), Status = 'AskToLeave' WHERE Username = ?",
            [$username],
        );
    }

    private function member(string $username): Member
    {
        $member = static::getContainer()->get(EntityManagerInterface::class)
            ->getRepository(Member::class)->findOneBy(['username' => $username]);
        self::assertInstanceOf(Member::class, $member);

        return $member;
    }
}
