<?php

namespace App\Tests\Controller;

use App\Entity\Member;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * A trip has at least one leg, and a leg taken off a trip goes unless it has invitations: then it stays, marked
 * removed, hidden everywhere but in the invitations. In the fixtures member-2's trip "Exploring Berlin and Papua"
 * has two legs (Berlin, then Jayapura), each with an invitation.
 *
 * @infection-ignore-all
 */
#[Group('integration')]
class TripLegsTest extends WebTestCase
{
    private KernelBrowser $client;
    private int $trip;
    /** @var array{0: int, 1: int} the trip's legs: Berlin, Jayapura */
    private array $legs;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->trip = (int) $this->connection()->fetchOne("SELECT id FROM trip WHERE summary = 'Exploring Berlin and Papua'");
        $this->legs = array_map('intval', $this->connection()->fetchFirstColumn('SELECT id FROM leg WHERE trip_id = ? ORDER BY arrival', [$this->trip]));
        self::assertCount(2, $this->legs);
        $this->client->loginUser($this->member('member-2'));
    }

    public function testATripIsNotCreatedWithoutLegs(): void
    {
        $trips = $this->tripCount();
        $crawler = $this->client->request('GET', '/new/trip');
        $form = $crawler->filter('form.p-trips-form')->form();
        $values = $form->getPhpValues();
        $values['trip']['summary'] = 'zztest trip without legs';
        $values['trip']['description'] = 'A trip without legs.';
        unset($values['trip']['legs']);

        $this->client->request('POST', $form->getUri(), $values);

        self::assertFalse($this->client->getResponse()->isRedirection(), 'a trip without legs was saved');
        self::assertSame($trips, $this->tripCount());
        self::assertStringContainsString('trip.error.no.legs', $this->client->getResponse()->getContent());
    }

    public function testTheLastLegIsNotRemoved(): void
    {
        $values = $this->editValues();
        unset($values['trip']['legs']);

        $this->client->request('POST', '/trip/' . $this->trip . '/edit', $values);

        self::assertFalse($this->client->getResponse()->isRedirection(), 'a trip without legs was saved');
        self::assertSame(2, $this->visibleLegs());
    }

    public function testALegWithoutInvitationsIsDeleted(): void
    {
        $this->connection()->executeStatement('UPDATE request SET invite_for_leg = NULL WHERE invite_for_leg = ?', [$this->legs[1]]);
        $values = $this->editValues();
        unset($values['trip']['legs'][1]);

        $this->client->request('POST', '/trip/' . $this->trip . '/edit', $values);

        $this->assertResponseRedirects();
        self::assertFalse($this->connection()->fetchOne('SELECT id FROM leg WHERE id = ?', [$this->legs[1]]), 'the leg is still there');
        // the trip and its other leg stay
        self::assertSame(1, $this->visibleLegs());
        self::assertNotFalse($this->connection()->fetchOne('SELECT id FROM trip WHERE id = ?', [$this->trip]), 'deleting a leg deleted its trip');
    }

    public function testALegWithAnInvitationIsOnlyRemoved(): void
    {
        $values = $this->editValues();
        unset($values['trip']['legs'][0]);

        $this->client->request('POST', '/trip/' . $this->trip . '/edit', $values);

        $this->assertResponseRedirects();
        $removed = $this->legs[0];
        self::assertNotNull($this->connection()->fetchOne('SELECT removed FROM leg WHERE id = ?', [$removed]), 'the leg with an invitation is not marked removed');
        self::assertSame(1, (int) $this->connection()->fetchOne('SELECT COUNT(*) FROM request WHERE invite_for_leg = ?', [$removed]), 'the invitation lost its leg');

        // the trip shows the other leg only, and the removed one takes no more invitations
        $crawler = $this->client->request('GET', '/trip/' . $this->trip);
        self::assertCount(0, $crawler->filter('#leg' . $removed));
        self::assertCount(1, $crawler->filter('#leg' . $this->legs[1]));
        $this->client->request('GET', '/trip/leg/' . $removed . '/panel?embed=1');
        $this->assertResponseStatusCodeSame(404);
        $this->client->loginUser($this->member('member-3'));
        $this->client->request('GET', '/new/invitation/' . $removed);
        $this->assertResponseStatusCodeSame(404);

        // editing again keeps it removed: the form has the visible leg only
        $values = $this->editValues();
        self::assertCount(1, $values['trip']['legs']);
    }

    /**
     * @return array<string, mixed> the values of member-2's edit form as it comes
     */
    private function editValues(): array
    {
        $this->client->loginUser($this->member('member-2'));
        $crawler = $this->client->request('GET', '/trip/' . $this->trip . '/edit');
        $this->assertResponseIsSuccessful();

        return $crawler->filter('form.p-trips-form')->form()->getPhpValues();
    }

    private function visibleLegs(): int
    {
        return (int) $this->connection()->fetchOne('SELECT COUNT(*) FROM leg WHERE trip_id = ? AND removed IS NULL', [$this->trip]);
    }

    private function tripCount(): int
    {
        return (int) $this->connection()->fetchOne('SELECT COUNT(*) FROM trip');
    }

    private function member(string $username): Member
    {
        $member = static::getContainer()->get(EntityManagerInterface::class)
            ->getRepository(Member::class)->findOneBy(['username' => $username]);
        self::assertInstanceOf(Member::class, $member);

        return $member;
    }

    private function connection(): Connection
    {
        return static::getContainer()->get(Connection::class);
    }
}
