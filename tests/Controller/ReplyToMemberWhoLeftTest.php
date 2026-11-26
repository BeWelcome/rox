<?php

namespace App\Tests\Controller;

use App\Doctrine\InFolderType;
use App\Doctrine\MessageStatusType;
use App\Entity\HostingRequest;
use App\Entity\Member;
use App\Entity\Message;
use App\Entity\Subject;
use DateTime;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * A conversation whose other member left (retired by data:retention too), was banned or suspended can be read, not
 * answered (ConversationVoter::CONVERSATION_REPLY): no reply box, no answer to a request, in the inbox and in the
 * drawer, and a reply sent anyway is not saved.
 */
#[Group('integration')]
class ReplyToMemberWhoLeftTest extends WebTestCase
{
    private KernelBrowser $client;

    protected function setUp(): void
    {
        $this->client = static::createClient();
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function goneStatuses(): iterable
    {
        yield 'asked to leave' => ['AskToLeave'];
        yield 'banned' => ['Banned'];
        yield 'suspended' => ['SuspendedBeta'];
    }

    #[DataProvider('goneStatuses')]
    public function testAConversationWithAMemberWhoIsGoneHasNoReplyBox(string $status): void
    {
        // conversation 1 of the fixtures: member-1 and member-2
        $this->setMemberStatus('member-1', $status);
        $this->client->loginUser($this->member('member-2'));

        foreach (['/conversation/1', '/conversation/1?embed=1'] as $url) {
            $crawler = $this->client->request('GET', $url);

            self::assertResponseIsSuccessful();
            $this->assertNoReply($crawler, $url);
        }
        // the drawer's head: a member who is gone has no preview to open
        self::assertCount(0, $crawler->filter('a.c-contact-head__member'), 'a preview link on a member who is gone');
    }

    public function testAConversationWithAnActiveMemberKeepsItsReplyBox(): void
    {
        $this->client->loginUser($this->member('member-2'));

        $crawler = $this->client->request('GET', '/conversation/1?embed=1');

        self::assertCount(1, $crawler->filter('form.js-inbox-reply'));
        self::assertCount(0, $crawler->filter('.c-inbox-reply--closed'));
        self::assertCount(1, $crawler->filter('a.c-contact-head__member'), 'the head opens the member\'s preview');
    }

    public function testTheDrawerShowsARetiredMemberAsAFormerMember(): void
    {
        $this->retire('member-1');
        $this->client->loginUser($this->member('member-2'));

        $crawler = $this->client->request('GET', '/conversation/1?embed=1');

        self::assertResponseIsSuccessful();
        $former = static::getContainer()->get(TranslatorInterface::class)->trans('forum.former.member');
        self::assertSame($former, trim($crawler->filter('.c-contact-head__name')->text()));
        self::assertCount(0, $crawler->filter('a.c-contact-head__member'), 'a preview link on the former member');
        $this->assertNoReply($crawler, 'the drawer');
    }

    public function testAReplyWrittenBeforeTheMemberLeftIsNotSaved(): void
    {
        $this->client->loginUser($this->member('member-2'));
        $crawler = $this->client->request('GET', '/conversation/1');
        $form = $crawler->filter('form.js-inbox-reply')->form();
        $form['app_message[message]'] = 'zztest a reply to a member who left';
        $this->retire('member-1');
        $before = $this->messages();

        $this->client->submit($form);

        $this->assertBackAtTheConversation(false);
        self::assertSame($before, $this->messages(), 'the reply was saved');
    }

    public function testTheReplyPageRefusesAMemberWhoIsGone(): void
    {
        $this->setMemberStatus('member-1', 'Banned');
        $this->client->loginUser($this->member('member-2'));
        $before = $this->messages();

        $this->client->request('GET', '/conversation/1/reply');
        $this->assertBackAtTheConversation(false);

        $this->client->request('POST', '/conversation/1/reply?embed=1', [
            'app_message' => ['message' => 'zztest a reply to a banned member'],
        ]);
        $this->assertBackAtTheConversation(true);
        self::assertSame($before, $this->messages(), 'the reply was saved');
    }

    public function testAHostingRequestOfAMemberWhoLeftCantBeAnswered(): void
    {
        $message = $this->createRequest('member-4', 'member-6');
        $this->retire('member-4');
        $this->client->loginUser($this->member('member-6'));

        $crawler = $this->client->request('GET', '/conversation/' . $message->getId());

        self::assertResponseIsSuccessful();
        self::assertCount(0, $crawler->filter('form.js-respond'), 'the host can answer the request');
        $this->assertNoReply($crawler, 'the request');

        $this->client->request('GET', '/conversation/' . $message->getId() . '/reply');
        self::assertResponseRedirects('/conversation/' . $message->getId());
    }

    /**
     * The refused reply leads back to the conversation (its newest message), in the drawer when sent from there,
     * which says why there is no reply box.
     */
    private function assertBackAtTheConversation(bool $embed): void
    {
        $location = (string) $this->client->getResponse()->headers->get('Location');
        self::assertMatchesRegularExpression('#^/conversation/\d+' . ($embed ? '\?embed=1' : '') . '$#', $location);
        $this->assertNoReply($this->client->followRedirect(), $location);
    }

    private function assertNoReply(Crawler $crawler, string $where): void
    {
        self::assertCount(0, $crawler->filter('form.js-inbox-reply'), $where . ': a reply box');
        self::assertCount(0, $crawler->filter('a.c-inbox-reply'), $where . ': a link to the reply page');
        self::assertCount(1, $crawler->filter('.c-inbox-reply--closed'), $where . ': no note instead of the reply box');
    }

    private function messages(): int
    {
        return (int) static::getContainer()->get(Connection::class)->fetchOne('SELECT COUNT(*) FROM message');
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
        // the members loaded before keep the old name and status otherwise
        static::getContainer()->get(EntityManagerInterface::class)->clear();
    }

    private function setMemberStatus(string $username, string $status): void
    {
        static::getContainer()->get(Connection::class)->executeStatement(
            'UPDATE member SET Status = ? WHERE Username = ?',
            [$status, $username],
        );
        static::getContainer()->get(EntityManagerInterface::class)->clear();
    }

    private function member(string $username): Member
    {
        $member = static::getContainer()->get(EntityManagerInterface::class)
            ->getRepository(Member::class)->findOneBy(['username' => $username]);
        self::assertInstanceOf(Member::class, $member);

        return $member;
    }

    private function createRequest(string $guestName, string $hostName): Message
    {
        $entityManager = static::getContainer()->get(EntityManagerInterface::class);
        $guest = $this->member($guestName);
        $host = $this->member($hostName);

        $request = new HostingRequest()
            ->setArrival(new DateTime('+20 days'))
            ->setDeparture(new DateTime('+23 days'));
        $subject = new Subject()->setSubject('zztest request of a member who left');
        $entityManager->persist($subject);
        $message = new Message()
            ->setSubject($subject)
            ->setSender($guest)
            ->setReceiver($host)
            ->setInitiator($guest)
            ->setMessage('zztest request of a member who left')
            ->setStatus(MessageStatusType::SENT)
            ->setFolder(InFolderType::NORMAL)
            ->setDeleteRequest('')
            ->setRequest($request);
        $entityManager->persist($request);
        $entityManager->persist($message);
        $entityManager->flush();

        return $message;
    }
}
