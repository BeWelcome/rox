<?php

namespace App\Tests\Controller;

use App\Doctrine\InFolderType;
use App\Doctrine\MessageStatusType;
use App\Entity\Member;
use App\Entity\Message;
use App\Entity\Subject;
use App\Service\MessageSearch;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * The search field of the conversation list finds conversations by the other member's username and by the words
 * of their messages (found by MessageSearch in the message_rt index, stubbed here: CI has no Manticore).
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
        [$member, $friend, $stranger] = $this->members();
        $words = $this->message($friend, $member, 'Shall we meet for zzquokkaberry tea on Sunday?');
        $other = $this->message($stranger, $member, 'zztest nothing to see');
        // The words are looked up in Manticore (MessageSearchTest covers the query); here it finds $words
        $search = $this->findMessages($words);

        $client->loginUser($member);
        $crawler = $client->request('GET', '/conversations/', ['q' => 'zzquokka']);

        $this->assertResponseIsSuccessful();
        self::assertSame('zzquokka', $search->words);
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

    private function findMessages(Message $message): object
    {
        $stub = new class extends MessageSearch {
            public ?string $words = null;
            /** @var array{subjectIds: list<int>, messageIds: list<int>, hits: array<string, array{id: int, count: int}>} */
            public array $found = ['subjectIds' => [], 'messageIds' => [], 'hits' => []];

            public function __construct()
            {
            }

            public function find(Member $member, string $words): array
            {
                $this->words = $words;

                return $this->found;
            }
        };
        $subjectId = (int) $message->getSubject()?->getId();
        $stub->found = [
            'subjectIds' => [$subjectId],
            'messageIds' => [],
            'hits' => ['s' . $subjectId => ['id' => (int) $message->getId(), 'count' => 1]],
        ];
        static::getContainer()->set(MessageSearch::class, $stub);

        return $stub;
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
