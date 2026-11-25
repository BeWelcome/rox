<?php

namespace App\Tests\Controller;

use App\Doctrine\LegOptionsType;
use App\Entity\Leg;
use App\Entity\Location;
use App\Entity\Member;
use App\Entity\MemberPreference;
use App\Entity\Preference;
use App\Entity\Trip;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * The invitation form of a stop of a trip (/new/invitation/<leg>): a stop that looks for hosts takes invitations; a
 * private stop and a stop of a removed trip have no form, which would show their place and dates to another member.
 * An invitation with a block word waits for the checkers, as a message or a request with one does.
 */
#[Group('integration')]
final class InvitationForAStopTest extends WebTestCase
{
    private const string SUMMARY = 'zztest invitation for a stop';

    private KernelBrowser $client;
    private Member $traveller;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $entityManager = $this->entityManager();
        $members = $entityManager->getRepository(Member::class);
        $this->traveller = $members->findOneBy(['username' => 'member-2']);
        // the fixtures' members have no picture and About Me: the traveller takes invitations without them
        foreach ([Preference::ALLOW_CONTACT_WITHOUT_PICTURE, Preference::ALLOW_CONTACT_WITHOUT_ABOUT_ME] as $codename) {
            $preference = $entityManager->getRepository(Preference::class)->findOneBy(['codename' => $codename]);
            $memberPreference = $entityManager->getRepository(MemberPreference::class)
                ->findOneBy(['member' => $this->traveller, 'preference' => $preference])
                ?? new MemberPreference()->setMember($this->traveller)->setPreference($preference);
            $entityManager->persist($memberPreference->setValue('Yes'));
        }
        $entityManager->flush();
        $this->client->loginUser($members->findOneBy(['username' => 'member-5']));
    }

    public function testAStopThatLooksForHostsHasTheForm(): void
    {
        $leg = $this->stop(LegOptionsType::LOOKING_FOR_HOST);

        $crawler = $this->client->request('GET', '/new/invitation/' . $leg->getId());

        self::assertResponseIsSuccessful();
        self::assertCount(1, $crawler->filter('[name$="[request][arrival]"]'));
        self::assertStringContainsString(self::SUMMARY, (string) $this->client->getResponse()->getContent());
    }

    public function testAPrivateStopHasNone(): void
    {
        $leg = $this->stop(LegOptionsType::PRIVATE);

        foreach (['', '?embed=1'] as $panel) {
            $this->client->request('GET', '/new/invitation/' . $leg->getId() . $panel);
            self::assertResponseStatusCodeSame(404);
        }
    }

    public function testAStopOfARemovedTripHasNone(): void
    {
        $leg = $this->stop(LegOptionsType::LOOKING_FOR_HOST);
        $leg->getTrip()->setDeleted(new DateTime());
        $this->entityManager()->flush();

        $this->client->request('GET', '/new/invitation/' . $leg->getId());

        self::assertResponseStatusCodeSame(404);
    }

    public function testAnInvitationIsMailedAtOnce(): void
    {
        $invitation = $this->invite('You are welcome to stay with me.');

        self::assertEmailCount(1);
        self::assertSame(['Sent', 'Normal', 'NotSpam'], $this->state($invitation));
    }

    public function testAnInvitationWithABlockWordWaitsForTheCheckers(): void
    {
        $invitation = $this->invite('Write to our support first, please.');

        self::assertEmailCount(0);
        self::assertSame(['ToCheck', 'Spam', 'SpamBlkWord'], $this->state($invitation));

        // a checker finds it with the block words and lets it through: the traveller gets the invitation's mail
        $this->client->loginUser($this->entityManager()->getRepository(Member::class)->findOneBy(['username' => 'bwadmin']));
        $crawler = $this->client->request('GET', '/admin/spam/messages/blocked');
        self::assertCount(2, $crawler->filter('input[name="decisions[' . $invitation . ']"]'));
        $this->client->request('POST', '/admin/spam/messages/blocked', [
            '_token' => (string) $crawler->filter('main form input[name="_token"]')->first()->attr('value'),
            'decisions' => [$invitation => 'ok'],
        ]);

        self::assertResponseRedirects('/admin/spam/messages/blocked');
        self::assertEmailCount(1);
        self::assertSame(['Checked', 'Normal', 'SpamBlkWord'], $this->state($invitation));
    }

    /**
     * The host invites the traveller for a stop that looks for hosts, with this text. Returns the message's id.
     */
    private function invite(string $text): int
    {
        $leg = $this->stop(LegOptionsType::LOOKING_FOR_HOST);
        $crawler = $this->client->request('GET', '/new/invitation/' . $leg->getId());
        $send = $crawler->filter('button[name$="[send]"]');
        self::assertCount(1, $send);
        $name = substr((string) $send->attr('name'), 0, -\strlen('[send]'));

        $this->client->submit($send->form([
            $name . '[subject][subject]' => 'zztest invitation',
            $name . '[message]' => $text,
        ]));

        self::assertResponseRedirects();
        $id = (int) $this->entityManager()->getConnection()->fetchOne(
            'SELECT MAX(m.id) FROM message m JOIN request r ON r.id = m.request_id WHERE r.invite_for_leg = ?',
            [$leg->getId()],
        );
        self::assertGreaterThan(0, $id);

        return $id;
    }

    /**
     * @return array{string, string, string} the message's status, folder and spam info
     */
    private function state(int $id): array
    {
        $row = $this->entityManager()->getConnection()->fetchNumeric('SELECT status, folder, spam_info FROM message WHERE id = ?', [$id]);
        self::assertIsArray($row);

        return [(string) $row[0], (string) $row[1], (string) $row[2]];
    }

    /**
     * A stop of a trip of the traveller, in ten days.
     */
    private function stop(string $option): Leg
    {
        $entityManager = $this->entityManager();
        $location = $entityManager->getRepository(Location::class)->findOneBy([]);
        self::assertInstanceOf(Location::class, $location);

        $trip = new Trip()
            ->setSummary(self::SUMMARY)
            ->setDescription(self::SUMMARY)
            ->setCreated(new DateTime())
            ->setCreator($this->traveller)
            ->setInvitationRadius(50);
        $leg = new Leg()
            ->setLocation($location)
            ->setArrival(new DateTime('+10 days'))
            ->setDeparture(new DateTime('+12 days'))
            ->setOptions([$option])
            ->setTrip($trip);
        $trip->addLeg($leg);
        $entityManager->persist($trip);
        $entityManager->flush();

        return $leg;
    }

    private function entityManager(): EntityManagerInterface
    {
        return static::getContainer()->get(EntityManagerInterface::class);
    }
}
