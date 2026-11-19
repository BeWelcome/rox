<?php

namespace App\Tests\Controller;

use App\Doctrine\MessageStatusType;
use App\Entity\HostingRequest;
use App\Entity\Member;
use App\Entity\MemberPreference;
use App\Entity\Message;
use App\Entity\Preference;
use App\Entity\Subject;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * A request can't be for days that have passed (that would only be a message: past requests are answered by
 * messages), neither when it's sent nor when the guest changes it. A declined or cancelled request stays so: the guest
 * can only write; so can the host of a cancelled request.
 */
#[Group('integration')]
class RequestDatesTest extends WebTestCase
{
    private KernelBrowser $client;
    private Member $guest;
    private Member $host;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $members = static::getContainer()->get(EntityManagerInterface::class)->getRepository(Member::class);
        $this->guest = $members->findOneBy(['username' => 'member-2']);
        $this->host = $members->findOneBy(['username' => 'member-5']); // hosts (member-3 doesn't)
        // the guest has no picture and About Me in the fixtures: the host accepts requests without them
        $entityManager = static::getContainer()->get(EntityManagerInterface::class);
        foreach ([Preference::ALLOW_CONTACT_WITHOUT_PICTURE, Preference::ALLOW_CONTACT_WITHOUT_ABOUT_ME] as $codename) {
            $preference = $entityManager->getRepository(Preference::class)->findOneBy(['codename' => $codename]);
            $memberPreference = $entityManager->getRepository(MemberPreference::class)
                ->findOneBy(['member' => $this->host, 'preference' => $preference])
                ?? new MemberPreference()->setMember($this->host)->setPreference($preference);
            $entityManager->persist($memberPreference->setValue('Yes'));
        }
        $entityManager->flush();
        $this->client->loginUser($this->guest);
    }

    public function testANewRequestForPastDaysIsRefused(): void
    {
        $this->sendRequest('Past request', new DateTime('-10 days'));

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('body', $this->trans('request.error.arrival.past'));
        self::assertSame(0, $this->requests('Past request'));
    }

    public function testANewRequestForComingDaysIsSent(): void
    {
        $this->sendRequest('Coming request', new DateTime('+10 days'));

        self::assertResponseRedirects();
        self::assertSame(1, $this->requests('Coming request'));
    }

    public function testAnOpenRequestCannotBeChangedToPastDays(): void
    {
        $message = $this->storedRequest(HostingRequest::REQUEST_OPEN);
        $before = $message->getRequest()->getArrival()->format('Y-m-d');

        $this->changeRequest($message, new DateTime('-10 days'));

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('body', $this->trans('request.error.arrival.past'));
        self::assertSame($before, $this->newestRequest($message)->getArrival()->format('Y-m-d'));
    }

    public function testAMessageOnAnOpenRequestKeepsItsDates(): void
    {
        $message = $this->storedRequest(HostingRequest::REQUEST_OPEN);

        $crawler = $this->client->request('GET', '/conversation/' . $message->getId() . '/reply');
        $form = $crawler->filter('#js-respond-form button[name="app_message[update]"]')->form();
        $form['app_message[message]'] = 'Just a question about the stay';
        $this->client->submit($form);

        self::assertResponseRedirects();
    }

    #[DataProvider('closedStatuses')]
    public function testAClosedRequestCanOnlyBeAnsweredByAMessage(int $status): void
    {
        $message = $this->storedRequest($status);

        $crawler = $this->client->request('GET', '/conversation/' . $message->getId() . '/reply');

        self::assertResponseIsSuccessful();
        self::assertCount(0, $crawler->filter('[name="app_message[request][arrival]"]'), 'the guest is offered new dates');
        self::assertCount(0, $crawler->filter('[name="app_message[cancel]"]'), 'the guest is offered to cancel');

        // dates posted anyway don't change the declined request
        $this->client->request('POST', '/conversation/' . $message->getId() . '/reply', ['app_message' => [
            'message' => 'Other dates?',
            'request' => ['arrival' => new DateTime('+20 days')->format('Y-m-d'), 'departure' => new DateTime('+22 days')->format('Y-m-d')],
            'update' => '',
        ]]);
        $newest = $this->newestRequest($message);
        self::assertSame($status, $newest->getStatus());
        self::assertSame($message->getRequest()->getArrival()->format('Y-m-d'), $newest->getArrival()->format('Y-m-d'));
    }

    /**
     * @return iterable<string, array{int}>
     */
    public static function closedStatuses(): iterable
    {
        yield 'declined' => [HostingRequest::REQUEST_DECLINED];
        yield 'cancelled' => [HostingRequest::REQUEST_CANCELLED];
    }

    public function testTheHostOfACancelledRequestCanOnlyWrite(): void
    {
        $message = $this->storedRequest(HostingRequest::REQUEST_CANCELLED);
        $this->client->loginUser($this->host);

        $crawler = $this->client->request('GET', '/conversation/' . $message->getId() . '/reply');

        self::assertResponseIsSuccessful();
        foreach (['accept', 'tentatively', 'decline'] as $answer) {
            self::assertCount(0, $crawler->filter(\sprintf('[name="app_message[%s]"], [data-button="app_message[%s]"]', $answer, $answer)), "the host is offered to {$answer}");
        }

        // the plain message form: none of the request's fields
        self::assertCount(0, $crawler->filter('[name^="app_message[request]"]'), 'the host gets the request answer form');
        self::assertCount(1, $crawler->filter('textarea[name$="[message]"]'), 'the host has no message form');
    }

    private function sendRequest(string $subject, DateTime $arrival): void
    {
        $crawler = $this->client->request('GET', '/new/request/' . $this->host->getUsername());
        $form = $crawler->filter('form[name=app_message] [type=submit]')->last()->form();
        $form['app_message[subject][subject]'] = $subject;
        $form['app_message[message]'] = 'Could you host me?';
        $form['app_message[request][arrival]'] = $arrival->format('Y-m-d');
        $form['app_message[request][departure]'] = (clone $arrival)->modify('+2 days')->format('Y-m-d');
        $form['app_message[request][numberOfTravellers]'] = '1';
        $this->client->submit($form);
    }

    private function changeRequest(Message $message, DateTime $arrival): void
    {
        $crawler = $this->client->request('GET', '/conversation/' . $message->getId() . '/reply');
        $form = $crawler->filter('#js-respond-form button[name="app_message[update]"]')->form();
        $form['app_message[message]'] = 'Other dates';
        $form['app_message[request][arrival]'] = $arrival->format('Y-m-d');
        $form['app_message[request][departure]'] = (clone $arrival)->modify('+2 days')->format('Y-m-d');
        $form['app_message[request][duration]'] = '';
        $this->client->submit($form);
    }

    private function storedRequest(int $status): Message
    {
        $entityManager = static::getContainer()->get(EntityManagerInterface::class);
        $request = new HostingRequest()
            ->setArrival(new DateTime('+5 days'))
            ->setDeparture(new DateTime('+8 days'))
            ->setNumberOfTravellers(1)
            ->setStatus($status);
        $message = new Message()
            ->setSender($this->guest)
            ->setReceiver($this->host)
            ->setSubject(new Subject()->setSubject('Stored request'))
            ->setMessage('Could you host me?')
            ->setStatus(MessageStatusType::SENT)
            ->setDeleteRequest('')
            ->setRequest($request);
        $entityManager->persist($message->getSubject());
        $entityManager->persist($request);
        $entityManager->persist($message);
        $entityManager->flush();

        return $message;
    }

    private function newestRequest(Message $message): HostingRequest
    {
        $entityManager = static::getContainer()->get(EntityManagerInterface::class);
        $entityManager->clear();
        $newest = $entityManager->getRepository(Message::class)->findOneBy(['subject' => $message->getSubject()->getId()], ['id' => 'DESC']);

        return $newest->getRequest();
    }

    private function requests(string $subject): int
    {
        return (int) static::getContainer()->get(EntityManagerInterface::class)->getConnection()->fetchOne(
            'SELECT COUNT(*) FROM message m JOIN subject s ON s.id = m.subject_id WHERE s.subject = ? AND m.request_id IS NOT NULL',
            [$subject],
        );
    }

    private function trans(string $key): string
    {
        return static::getContainer()->get('translator')->trans($key);
    }
}
