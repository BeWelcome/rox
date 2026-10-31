<?php

namespace App\Tests\Controller;

use App\Doctrine\InFolderType;
use App\Doctrine\MessageStatusType;
use App\Doctrine\SpamInfoType;
use App\Entity\Member;
use App\Entity\Message;
use App\Entity\Subject;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * A member's spam folder lists the messages that went to spam, with and without a subject.
 */
#[Group('integration')]
class SpamFolderTest extends WebTestCase
{
    public function testTheSpamFolderListsSpamMessages(): void
    {
        $client = static::createClient();
        $entityManager = static::getContainer()->get(EntityManagerInterface::class);
        $members = $entityManager->getRepository(Member::class);
        $receiver = $members->findOneBy(['username' => 'member-2']);
        $sender = $members->findOneBy(['username' => 'member-5']);

        $subject = new Subject()->setSubject('zztest spam subject');
        $entityManager->persist($subject);
        $withSubject = $this->spam($sender, $receiver)->setSubject($subject);
        $withoutSubject = $this->spam($sender, $receiver);
        $entityManager->persist($withSubject);
        $entityManager->persist($withoutSubject);
        $entityManager->flush();

        $client->loginUser($receiver);
        $crawler = $client->request('GET', '/conversations/spam/');

        $this->assertResponseIsSuccessful();
        self::assertCount(1, $crawler->filter('a[href="/conversation/' . $withSubject->getId() . '?folder=spam"]'));
        self::assertCount(1, $crawler->filter('a[href="/conversation/' . $withoutSubject->getId() . '?folder=spam"]'));
    }

    private function spam(Member $sender, Member $receiver): Message
    {
        return new Message()
            ->setSender($sender)
            ->setReceiver($receiver)
            ->setInitiator($sender)
            ->setMessage('zztest spam')
            ->setStatus(MessageStatusType::SENT)
            ->setFolder(InFolderType::SPAM)
            ->setSpamInfo(SpamInfoType::MEMBER_SAYS_SPAM);
    }
}
