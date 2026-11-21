<?php

namespace App\Tests\Controller;

use App\Doctrine\MessageStatusType;
use App\Entity\HostingRequest;
use App\Entity\Leg;
use App\Entity\Location;
use App\Entity\Member;
use App\Entity\Message;
use App\Entity\Subject;
use App\Entity\Trip;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * A cancelled invitation, or one whose arrival has passed, is answered by a message: the reply page of the guest
 * and of the host is the message form (the guest's failed with a 500, forwarded to a controller that doesn't exist).
 */
#[Group('integration')]
class ClosedInvitationReplyTest extends WebTestCase
{
    private KernelBrowser $client;
    private Member $guest;
    private Member $host;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $members = static::getContainer()->get(EntityManagerInterface::class)->getRepository(Member::class);
        $this->guest = $members->findOneBy(['username' => 'member-2']);
        $this->host = $members->findOneBy(['username' => 'member-5']);
    }

    /**
     * @return iterable<string, array{int, string, bool}>
     */
    public static function closedInvitations(): iterable
    {
        yield 'cancelled, the guest' => [HostingRequest::REQUEST_CANCELLED, '+5 days', true];
        yield 'cancelled, the host' => [HostingRequest::REQUEST_CANCELLED, '+5 days', false];
        yield 'arrival passed, the guest' => [HostingRequest::REQUEST_OPEN, '-3 days', true];
        yield 'arrival passed, the host' => [HostingRequest::REQUEST_OPEN, '-3 days', false];
    }

    #[DataProvider('closedInvitations')]
    public function testTheReplyPageOfAClosedInvitationIsTheMessageForm(int $status, string $arrival, bool $asGuest): void
    {
        $invitation = $this->storedInvitation($status, new DateTime($arrival));
        $this->client->loginUser($asGuest ? $this->guest : $this->host);

        $crawler = $this->client->request('GET', '/conversation/' . $invitation->getId() . '/reply');

        self::assertResponseIsSuccessful();
        // a message: no answer to the invitation, no dates to change
        self::assertCount(0, $crawler->filter('.js-stay-pick, [name$="[request][arrival]"]'));
    }

    /**
     * The host invites the guest for a stop of the guest's trip.
     */
    private function storedInvitation(int $status, DateTime $arrival): Message
    {
        $entityManager = static::getContainer()->get(EntityManagerInterface::class);
        $departure = (clone $arrival)->modify('+3 days');
        $location = $entityManager->getRepository(Location::class)->findOneBy([]);
        self::assertInstanceOf(Location::class, $location);

        $trip = new Trip()
            ->setSummary('Closed invitation test')
            ->setDescription('Closed invitation test')
            ->setCreated(new DateTime())
            ->setCreator($this->guest)
            ->setInvitationRadius(50);
        $leg = new Leg()
            ->setLocation($location)
            ->setArrival(clone $arrival)
            ->setDeparture(clone $departure)
            ->setTrip($trip);
        $trip->addLeg($leg);
        $entityManager->persist($trip);

        $request = new HostingRequest()
            ->setArrival($arrival)
            ->setDeparture($departure)
            ->setNumberOfTravellers(1)
            ->setStatus($status)
            ->setInviteForLeg($leg);
        $message = new Message()
            ->setSender($this->host)
            ->setReceiver($this->guest)
            ->setSubject(new Subject()->setSubject('Stored invitation'))
            ->setMessage('Stay with me!')
            ->setStatus(MessageStatusType::SENT)
            ->setDeleteRequest('')
            ->setRequest($request);
        $entityManager->persist($message->getSubject());
        $entityManager->persist($request);
        $entityManager->persist($message);
        $entityManager->flush();

        return $message;
    }
}
