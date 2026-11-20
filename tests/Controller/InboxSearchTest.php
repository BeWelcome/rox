<?php

namespace App\Tests\Controller;

use App\Doctrine\InFolderType;
use App\Doctrine\MessageStatusType;
use App\Entity\Member;
use App\Entity\Message;
use App\Entity\Subject;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * The search field of the conversation list finds conversations by the other member's username and by the words
 * of their messages (message_rt index, kept up to date as messages are written).
 */
#[Group('integration')]
class InboxSearchTest extends WebTestCase
{
    public function testSearchByTheOtherMembersUsername(): void
    {
        $client = static::createClient();
        [$member, $friend, $stranger] = $this->members();
        $withFriend = $this->message($friend, $member, 'zztest hello');
        $withStranger = $this->message($stranger, $member, 'zztest hello');

        $client->loginUser($member);
        $crawler = $client->request('GET', '/conversations/', ['q' => $friend->getUsername()]);

        $this->assertResponseIsSuccessful();
        self::assertCount(1, $crawler->filter('input[name="q"][value="' . $friend->getUsername() . '"]'));
        self::assertCount(1, $crawler->filter('a[href^="/conversation/' . $withFriend->getId() . '?"]'));
        self::assertCount(0, $crawler->filter('a[href^="/conversation/' . $withStranger->getId() . '?"]'));
    }

    public function testSearchByTheWordsOfAMessage(): void
    {
        $client = static::createClient();
        $tester = new CommandTester(new Application($client->getKernel())->find('manticore:indices:messages'));
        self::assertSame(0, $tester->execute([]));

        [$member, $friend, $stranger] = $this->members();
        $words = $this->message($friend, $member, 'Shall we meet for zzquokkaberry tea on Sunday?');
        $other = $this->message($stranger, $member, 'zztest nothing to see');

        $client->loginUser($member);
        $crawler = $client->request('GET', '/conversations/', ['q' => 'zzquokka']);

        $this->assertResponseIsSuccessful();
        self::assertCount(1, $crawler->filter('a[href^="/conversation/' . $words->getId() . '?"]'));
        self::assertCount(0, $crawler->filter('a[href^="/conversation/' . $other->getId() . '?"]'));
    }

    public function testNothingFound(): void
    {
        $client = static::createClient();
        [$member] = $this->members();

        $client->loginUser($member);
        $client->request('GET', '/conversations/', ['q' => 'zznothing-at-all-here']);

        $this->assertResponseIsSuccessful();
        self::assertSelectorExists('.c-inbox-search__clear');
        self::assertSelectorNotExists('.c-inbox-item');
    }

    /**
     * @return array{Member, Member, Member}
     */
    private function members(): array
    {
        $members = static::getContainer()->get(EntityManagerInterface::class)->getRepository(Member::class);

        return [
            $members->findOneBy(['username' => 'member-2']),
            $members->findOneBy(['username' => 'member-5']),
            $members->findOneBy(['username' => 'member-3']),
        ];
    }

    private function message(Member $sender, Member $receiver, string $text): Message
    {
        $entityManager = static::getContainer()->get(EntityManagerInterface::class);
        $subject = new Subject()->setSubject('zztest search');
        $entityManager->persist($subject);
        $message = new Message()
            ->setSubject($subject)
            ->setSender($sender)
            ->setReceiver($receiver)
            ->setInitiator($sender)
            ->setMessage($text)
            ->setStatus(MessageStatusType::SENT)
            ->setFolder(InFolderType::NORMAL);
        $entityManager->persist($message);
        $entityManager->flush();

        return $message;
    }
}
