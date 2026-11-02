<?php

namespace App\Tests\Controller;

use App\Doctrine\InFolderType;
use App\Doctrine\MessageStatusType;
use App\Entity\HostingRequest;
use App\Entity\Member;
use App\Entity\Message;
use App\Entity\Subject;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * The host answers a hosting request from the stay card: Accept, Maybe and Decline pick the answer, the
 * message line at the bottom sends it. The guest only reads where the request stands.
 */
#[Group('integration')]
class StayAnswerTest extends WebTestCase
{
    public function testTheHostAcceptsFromTheStayCard(): void
    {
        $client = static::createClient();
        [$message, $guest, $host] = $this->createRequest();

        $client->loginUser($host);
        $crawler = $client->request('GET', '/conversation/' . $message->getId());

        $this->assertResponseIsSuccessful();
        self::assertSelectorTextContains('.c-stay-card__status', 'request.status.waiting.host');
        self::assertSelectorTextContains('.c-stay-answer__q', 'request.decide.prompt');
        $picks = $crawler->filter('.c-stay-card .js-stay-pick');
        self::assertSame(['accept', 'maybe', 'decline'], $picks->each(static fn ($pick) => $pick->attr('data-choice')));
        // The big decision panel under the messages is gone
        self::assertCount(0, $crawler->filter('.js-decide-step[data-step="decide"]'));

        // Send as Accept: what requests.js does with the button the pick names
        $accept = $picks->first()->attr('data-button');
        $form = $crawler->filter('form.js-respond')->form();
        $values = $form->getPhpValues();
        parse_str($accept . '=', $button);
        $values = array_replace_recursive($values, $button);
        $values[array_key_first($button)]['message'] = 'zztest see you then';
        $client->request('POST', $form->getUri(), $values);

        $entityManager = static::getContainer()->get(EntityManagerInterface::class);
        $request = $entityManager->getRepository(Message::class)->find($message->getId())->getRequest();
        self::assertSame(HostingRequest::REQUEST_ACCEPTED, $request->getStatus());
    }

    public function testTheGuestSeesNoAnswerButtons(): void
    {
        $client = static::createClient();
        [$message, $guest] = $this->createRequest();

        $client->loginUser($guest);
        $crawler = $client->request('GET', '/conversation/' . $message->getId());

        $this->assertResponseIsSuccessful();
        self::assertSelectorTextContains('.c-stay-card__status', 'request.status.waiting.guest');
        self::assertCount(0, $crawler->filter('.js-stay-pick'));
    }

    /**
     * @return array{Message, Member, Member}
     */
    private function createRequest(): array
    {
        $entityManager = static::getContainer()->get(EntityManagerInterface::class);
        $members = $entityManager->getRepository(Member::class);
        $guest = $members->findOneBy(['username' => 'member-4']);
        $host = $members->findOneBy(['username' => 'member-6']);

        $request = new HostingRequest()
            ->setArrival(new DateTime('+20 days'))
            ->setDeparture(new DateTime('+23 days'));
        $subject = new Subject()->setSubject('zztest stay answer subject');
        $entityManager->persist($subject);
        $message = new Message()
            ->setSubject($subject)
            ->setSender($guest)
            ->setReceiver($host)
            ->setInitiator($guest)
            ->setMessage('zztest request for the stay answer')
            ->setStatus(MessageStatusType::SENT)
            ->setFolder(InFolderType::NORMAL)
            ->setDeleteRequest('')
            ->setRequest($request);
        $entityManager->persist($request);
        $entityManager->persist($message);
        $entityManager->flush();

        return [$message, $guest, $host];
    }
}
