<?php

namespace App\Tests\Controller\Admin;

use App\Doctrine\InFolderType;
use App\Doctrine\MessageStatusType;
use App\Doctrine\SpamInfoType;
use App\Entity\Member;
use App\Entity\MemberFlag;
use App\Entity\Message;
use App\Entity\SignupPattern;
use App\Model\ShadowBanModel;
use App\Model\SignupModel;
use App\Service\MessageSender;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * The safety team's sign-up patterns and the shadow ban: an account signing up with a watched text is flagged; what a
 * flagged member sends looks sent to them, reaches nobody (no notification, not in the receiver's inbox) and waits
 * for a volunteer, who releases a message, or the member with all their messages. bwadmin is in the Safety
 * Team; member-2 plays the spammer, member-5 the receiver.
 */
#[Group('integration')]
final class ShadowBanTest extends WebTestCase
{
    private KernelBrowser $client;

    protected function setUp(): void
    {
        $this->client = static::createClient();
    }

    public function testAnAccountSigningUpWithAWatchedTextIsFlagged(): void
    {
        $volunteer = $this->member('bwadmin');
        static::getContainer()->get(ShadowBanModel::class)->addPattern('zzspammy', SignupPattern::FIELD_BOTH, 'test', $volunteer);

        $member = static::getContainer()->get(SignupModel::class)->createAccount([
            'username' => 'zzSpammy42',
            'email' => 'zzspammy42@example.org',
            'password' => 'a password of the test',
            'locale' => 'en',
        ]);

        $flag = $this->em()->getRepository(MemberFlag::class)->findOneBy(['member' => $member]);
        self::assertInstanceOf(MemberFlag::class, $flag);
        self::assertSame('username zzSpammy42', $flag->getMatched());
        self::assertTrue(static::getContainer()->get(ShadowBanModel::class)->isShadowBanned($member));
    }

    public function testWhatAShadowBannedMemberSendsReachesNobody(): void
    {
        $this->flag('member-2');
        $message = $this->send('member-2', 'member-5', null, 'zztest held subject', 'zztest held text');

        self::assertSame(MessageStatusType::CHECK, $message->getStatus());
        self::assertSame(InFolderType::SPAM, $message->getFolder());
        self::assertStringContainsString(SpamInfoType::SHADOW_BAN, $message->getSpamInfo());
        self::assertEmailCount(0);

        // the sender sees it as sent
        $this->client->loginUser($this->member('member-2'));
        $crawler = $this->client->request('GET', '/conversation/' . $message->getId());
        self::assertResponseIsSuccessful();
        self::assertStringContainsString('zztest held text', (string) $this->client->getResponse()->getContent());
        $this->client->request('GET', '/conversations/');
        self::assertStringContainsString('zztest held subject', (string) $this->client->getResponse()->getContent());

        // the receiver doesn't: not in the inbox, no notification
        $this->client->loginUser($this->member('member-5'));
        $this->client->request('GET', '/conversations/');
        self::assertStringNotContainsString('zztest held subject', (string) $this->client->getResponse()->getContent());
    }

    public function testAVolunteerReleasesAMessageAndThenTheMember(): void
    {
        $flag = $this->flag('member-2');
        $released = $this->send('member-2', 'member-5', null, 'zztest one', 'zztest first held');
        $later = $this->send('member-2', 'member-5', null, 'zztest two', 'zztest second held');
        $this->client->loginUser($this->member('bwadmin'));

        $crawler = $this->client->request('GET', '/admin/spam/flagged');
        self::assertResponseIsSuccessful();
        self::assertCount(1, $crawler->filter('#flag-' . $this->member('member-2')->getId()));
        $this->post('/admin/spam/flagged/message/' . $released->getId() . '/release');

        self::assertSame([MessageStatusType::CHECKED, InFolderType::NORMAL], $this->state($released));
        self::assertSame([MessageStatusType::CHECK, InFolderType::SPAM], $this->state($later));
        self::assertEmailCount(1);

        $this->post('/admin/spam/flagged/' . $flag->getId() . '/release');

        self::assertSame([MessageStatusType::CHECKED, InFolderType::NORMAL], $this->state($later));
        self::assertFalse(static::getContainer()->get(ShadowBanModel::class)->isShadowBanned($this->member('member-2')));
        // what they send now goes through
        $after = $this->send('member-2', 'member-5', null, 'zztest three', 'zztest free');
        self::assertSame(InFolderType::NORMAL, $after->getFolder());
    }

    public function testAPatternIsAddedAndTheRecentAccountsItMatchesAreFlagged(): void
    {
        // member-6 signed up today (the pattern looks at the last days' accounts)
        $this->em()->getConnection()->executeStatement("UPDATE member SET created = NOW() WHERE Username = 'member-6'");
        $this->client->loginUser($this->member('bwadmin'));
        $crawler = $this->client->request('GET', '/admin/spam/patterns');
        $form = $crawler->filter('form.c-shadow__add')->form(['pattern' => 'member-6', 'field' => 'username']);
        $this->client->submit($form);
        self::assertTrue($this->client->getResponse()->isRedirect());

        $crawler = $this->client->followRedirect();
        $pattern = $this->em()->getRepository(SignupPattern::class)->findOneBy(['pattern' => 'member-6']);
        self::assertInstanceOf(SignupPattern::class, $pattern);
        self::assertCount(1, $crawler->filter('input[name="members[]"][value="' . $this->member('member-6')->getId() . '"]'));
        $this->client->submit($crawler->filter('form.c-shadow__matches')->form());

        self::assertNotNull($this->em()->getRepository(MemberFlag::class)->findOneBy(['member' => $this->member('member-6')]));
    }

    public function testTheSafetyTeamShadowBansANewMemberInTheNewMembersTool(): void
    {
        // member-6 signed up today: a new member
        $this->em()->getConnection()->executeStatement("UPDATE member SET created = NOW() WHERE Username = 'member-6'");
        $this->client->loginUser($this->member('bwadmin'));
        $crawler = $this->client->request('GET', '/admin/newmembers');
        self::assertResponseIsSuccessful();
        $button = $crawler->filter('form[action="/admin/newmembers/member-6/shadow"]');
        self::assertCount(1, $button, 'the new members tool has no Shadow ban for member-6');

        $this->client->submit($button->form());

        self::assertTrue($this->client->getResponse()->isRedirect());
        self::assertTrue(static::getContainer()->get(ShadowBanModel::class)->isShadowBanned($this->member('member-6')));
        $crawler = $this->client->request('GET', '/admin/newmembers');
        self::assertCount(0, $crawler->filter('form[action="/admin/newmembers/member-6/shadow"]'));
        self::assertCount(1, $crawler->filter('a.c-newmembers__shadowed[href$="#flag-' . $this->member('member-6')->getId() . '"]'));
        // not oneself
        self::assertCount(0, $crawler->filter('form[action="/admin/newmembers/bwadmin/shadow"]'));
    }

    public function testAPatternNeedsThreeCharactersAndTheRight(): void
    {
        $this->client->loginUser($this->member('bwadmin'));
        $crawler = $this->client->request('GET', '/admin/spam/patterns');
        $this->client->submit($crawler->filter('form.c-shadow__add')->form(['pattern' => 'a*b']));
        self::assertResponseStatusCodeSame(422);

        $this->client->loginUser($this->member('member-6'));
        $this->client->request('GET', '/admin/spam/flagged');
        self::assertResponseStatusCodeSame(403);
        $this->client->request('GET', '/admin/spam/patterns');
        self::assertResponseStatusCodeSame(403);
    }

    /**
     * The entity manager of the kernel now: the client starts a new one for its requests.
     */
    private function em(): EntityManagerInterface
    {
        return static::getContainer()->get(EntityManagerInterface::class);
    }

    private function member(string $username): Member
    {
        return $this->em()->getRepository(Member::class)->findOneBy(['username' => $username]);
    }

    private function flag(string $username): MemberFlag
    {
        $flag = new MemberFlag($this->member($username), null, 'username ' . $username);
        $this->em()->persist($flag);
        $this->em()->flush();

        return $flag;
    }

    private function send(string $from, string $to, ?Message $parent, string $subject, string $text): Message
    {
        $message = static::getContainer()->get(MessageSender::class)
            ->send($this->member($from), $this->member($to), $parent, $subject, '<p>' . $text . '</p>');
        // sending built the mail's Twig without a member logged in (MemberTwigExtension keeps the first): the pages
        // after it get a kernel of their own
        $this->client->getKernel()->shutdown();
        $this->client->getKernel()->boot();

        return $this->em()->find(Message::class, $message->getId());
    }

    private function post(string $url): void
    {
        $token = $this->client->request('GET', '/admin/spam/flagged')->filter('main form input[name="_token"]')->first()->attr('value');
        $this->client->request('POST', $url, ['_token' => $token]);
        self::assertTrue($this->client->getResponse()->isRedirect(), 'the POST answered ' . $this->client->getResponse()->getStatusCode());
    }

    /**
     * @return array{string, string}
     */
    private function state(Message $message): array
    {
        $this->em()->clear();
        $stored = $this->em()->find(Message::class, $message->getId());

        return [$stored->getStatus(), $stored->getFolder()];
    }
}
