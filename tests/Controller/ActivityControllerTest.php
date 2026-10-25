<?php

namespace App\Tests\Controller;

use App\Entity\Activity;
use App\Entity\ActivityAttendee;
use App\Entity\Location;
use App\Entity\Member;
use App\Entity\Preference;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * The activities pages (App\Controller\ActivityController) as members use them. Each test creates its
 * own activity in Berlin, where the fixture members 1 to 5 live, and removes it again.
 *
 * @infection-ignore-all
 */
#[Group('integration')]
class ActivityControllerTest extends WebTestCase
{
    private const string TITLE = 'Activity controller test picnic';

    private KernelBrowser $client;
    private ?int $activityId = null;
    private array $createdIds = [];

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->activityId = $this->createActivity('member-1')->getId();
    }

    protected function tearDown(): void
    {
        $entityManager = $this->getEntityManager();
        $entityManager->clear();
        $ids = array_filter(array_merge([$this->activityId], $this->createdIds));
        foreach ($entityManager->getRepository(Activity::class)->findBy(['id' => $ids]) as $activity) {
            foreach ($activity->getAttendees() as $attendee) {
                $entityManager->remove($attendee);
            }
            $entityManager->remove($activity);
        }
        $entityManager->flush();

        parent::tearDown();
    }

    public static function listPages(): array
    {
        return [
            'near me' => ['/activities/nearme', true],
            'mine' => ['/activities/myactivities', true],
            'upcoming' => ['/activities/upcoming', true],
            'upcoming page 1' => ['/activities/upcoming/page/1', true],
            'online' => ['/activities/online', false],
            'past' => ['/activities/past', false],
            'search form' => ['/activities/search', false],
            'search results' => ['/activities/search/controller%20test%20picnic', true],
            'rules' => ['/activities/rules', false],
        ];
    }

    #[DataProvider('listPages')]
    public function testListPagesRender(string $url, bool $listsTheActivity): void
    {
        $this->client->loginUser($this->member('member-1'));

        $crawler = $this->client->request('GET', $url);

        $this->assertResponseIsSuccessful();
        self::assertCount(1, $crawler->filter('.p-activities__tabs'));
        self::assertSame($listsTheActivity, str_contains($crawler->filter('.p-activities__body, .p-activities-panel')->text(''), self::TITLE));
    }

    public function testActivitiesRedirectsToNearMe(): void
    {
        $this->client->loginUser($this->member('member-1'));

        $this->client->request('GET', '/activities');

        $this->assertResponseRedirects('/activities/nearme');
    }

    public function testListPageBeyondTheLastIsNotFound(): void
    {
        $this->client->loginUser($this->member('member-1'));

        $this->client->request('GET', '/activities/upcoming/page/999');

        $this->assertResponseStatusCodeSame(404);
    }

    public function testListsUseTheMembersItemsPerPage(): void
    {
        $entityManager = $this->getEntityManager();
        $member = $this->member('member-5');
        $preference = $entityManager->getRepository(Preference::class)->findOneBy(['codename' => Preference::ITEMS_PER_PAGE]);
        $memberPreference = $member->getMemberPreference($preference);
        $previous = $memberPreference->getValue();
        $memberPreference->setValue('1');
        $entityManager->persist($memberPreference);
        $entityManager->flush();

        try {
            $this->client->loginUser($member);
            $crawler = $this->client->request('GET', '/activities/upcoming');
            $upcoming = $this->getEntityManager()->getRepository(Activity::class)
                ->findUpcoming(false, 1, 1)->getNbResults();

            $this->assertResponseIsSuccessful();
            self::assertGreaterThan(1, $upcoming);
            self::assertCount(1, $crawler->filter('.p-activities-card'));
            // the page number and the next arrow
            self::assertGreaterThanOrEqual(1, $crawler->filter('.p-activities__pager a[href="/activities/upcoming/page/2"]')->count());

            $this->client->request('GET', '/activities/upcoming/page/' . $upcoming);
            $this->assertResponseIsSuccessful();
            $this->client->request('GET', '/activities/upcoming/page/' . ($upcoming + 1));
            $this->assertResponseStatusCodeSame(404);
        } finally {
            $memberPreference = $this->member('member-5')->getMemberPreference($preference);
            $memberPreference->setValue($previous);
            $this->getEntityManager()->flush();
        }
    }

    public function testSearchFormRedirectsToTheResults(): void
    {
        $this->client->loginUser($this->member('member-2'));
        $crawler = $this->client->request('GET', '/activities/search');

        $form = $crawler->filter('form.p-activities-search')->form(['search[keyword]' => 'Berlin']);
        $this->client->submit($form);

        $this->assertResponseRedirects('/activities/search/Berlin');
        $crawler = $this->client->followRedirect();
        self::assertStringContainsString(self::TITLE, $crawler->filter('.p-activities__list')->text(''));
    }

    public function testChangingTheRadiusIsSaved(): void
    {
        $member = $this->member('member-3');
        $this->client->loginUser($member);
        $crawler = $this->client->request('GET', '/activities/nearme');

        $form = $crawler->filter('form.p-activities-inline-form')->form(['activities[radius]' => '0']);
        $this->client->submit($form);

        $this->assertResponseRedirects('/activities/nearme');
        $preference = $this->getEntityManager()->getRepository(Preference::class)
            ->findOneBy(['codename' => Preference::ACTIVITIES_NEAR_ME_RADIUS]);
        self::assertSame('0', (string) $this->member('member-3')->getMemberPreference($preference)->getValue());
        // 0 km: only the member's own place, which is the activity's place
        $crawler = $this->client->followRedirect();
        self::assertStringContainsString(self::TITLE, $crawler->filter('.p-activities__list')->text(''));
    }

    public function testShowPageForAnotherMember(): void
    {
        $this->client->loginUser($this->member('member-2'));

        $crawler = $this->client->request('GET', '/activities/' . $this->activityId);

        $this->assertResponseIsSuccessful();
        self::assertSame(self::TITLE, trim($crawler->filter('.p-activities__title')->text()));
        self::assertStringContainsString('member-1', $crawler->filter('.p-activity-hero__organizers')->text());
        self::assertCount(1, $crawler->filter('form.p-activity-answer'));
        self::assertCount(0, $crawler->filter('.p-activities a[href="/activities/' . $this->activityId . '/edit"]'), 'only organizers can edit');
        self::assertCount(0, $crawler->filter('#cancelActivity'), 'only organizers can cancel');
    }

    public function testShowPageForTheOrganizer(): void
    {
        $this->client->loginUser($this->member('member-1'));

        $crawler = $this->client->request('GET', '/activities/' . $this->activityId);

        $this->assertResponseIsSuccessful();
        self::assertCount(1, $crawler->filter('a[href="/activities/' . $this->activityId . '/edit"]'));
        self::assertCount(1, $crawler->filter('#cancelActivity form'));
        self::assertCount(0, $crawler->filter('.p-activity-answer__leave'), 'organizers can not leave');
    }

    public function testLegacyAttendeePagesRedirectToTheActivity(): void
    {
        $this->client->loginUser($this->member('member-1'));

        $this->client->request('GET', '/activities/' . $this->activityId . '/attendees/page/2');

        $this->assertResponseRedirects('/activities/' . $this->activityId, 301);
    }

    public function testCreateActivity(): void
    {
        $member = $this->member('member-4');
        $this->client->loginUser($member);
        $crawler = $this->client->request('GET', '/activities/create');
        $this->assertResponseIsSuccessful();
        $berlin = $this->berlin();

        $form = $crawler->filter('form.p-activities-form')->form([
            'activity[title]' => 'Created by the activity test',
            'activity[location][fullname]' => 'Berlin, Germany',
            'activity[location][name]' => 'Berlin',
            'activity[location][geoname_id]' => (string) $berlin->getGeonameId(),
            'activity[location][latitude]' => (string) $berlin->getLatitude(),
            'activity[location][longitude]' => (string) $berlin->getLongitude(),
            'activity[address]' => 'At the fountain',
            'activity[starts]' => new DateTime('+3 days 18:00')->format('Y-m-d\TH:i'),
            'activity[ends]' => new DateTime('+3 days 21:00')->format('Y-m-d\TH:i'),
            'activity[description]' => '<p>Bring something to share.</p>',
        ]);
        $this->client->submit($form);

        $activity = $this->getEntityManager()->getRepository(Activity::class)->findOneBy(['title' => 'Created by the activity test']);
        self::assertInstanceOf(Activity::class, $activity);
        $this->createdIds[] = $activity->getId();
        $this->assertResponseRedirects('/activity/' . $activity->getId() . '/created');
        self::assertSame(Activity::STATUS_ACTIVE, $activity->getStatus());
        self::assertFalse($activity->isOnline());
        self::assertSame('At the fountain', $activity->getAddress());
        self::assertSame($berlin->getGeonameId(), $activity->getLocation()->getGeonameId());
        self::assertTrue($activity->isOrganizer($this->member('member-4')));
        self::assertSame(ActivityAttendee::ATTENDS_YES, (int) $activity->getAttendeeFor($this->member('member-4'))->getStatus());
    }

    public function testCreateActivityThatEndsBeforeItStartsShowsAnError(): void
    {
        $this->client->loginUser($this->member('member-4'));
        $crawler = $this->client->request('GET', '/activities/create');
        $berlin = $this->berlin();

        $form = $crawler->filter('form.p-activities-form')->form([
            'activity[title]' => 'Ends before it starts',
            'activity[location][fullname]' => 'Berlin, Germany',
            'activity[location][name]' => 'Berlin',
            'activity[location][geoname_id]' => (string) $berlin->getGeonameId(),
            'activity[starts]' => new DateTime('+3 days 18:00')->format('Y-m-d\TH:i'),
            'activity[ends]' => new DateTime('+3 days 17:00')->format('Y-m-d\TH:i'),
            'activity[description]' => 'Something',
        ]);
        $crawler = $this->client->submit($form);

        $this->assertResponseStatusCodeSame(422);
        self::assertStringContainsString(
            $this->translate('activity.end.before.start', [], 'validators'),
            $crawler->filter('form.p-activities-form')->text()
        );
        self::assertNull($this->getEntityManager()->getRepository(Activity::class)->findOneBy(['title' => 'Ends before it starts']));
    }

    public function testOrganizerEditsTheActivity(): void
    {
        $this->client->loginUser($this->member('member-1'));
        $crawler = $this->client->request('GET', '/activities/' . $this->activityId . '/edit');
        $this->assertResponseIsSuccessful();

        $form = $crawler->filter('form.p-activities-form')->form(['activity[title]' => self::TITLE . ' (edited)']);
        $this->client->submit($form);

        $this->assertResponseRedirects('/activities/' . $this->activityId);
        self::assertSame(self::TITLE . ' (edited)', $this->activity()->getTitle());
    }

    public function testOtherMembersCanNotEdit(): void
    {
        $this->client->loginUser($this->member('member-2'));

        $this->client->request('GET', '/activities/' . $this->activityId . '/edit');

        $this->assertResponseStatusCodeSame(403);
    }

    public function testMemberAnswersChangesTheAnswerAndLeaves(): void
    {
        $this->client->loginUser($this->member('member-2'));

        $this->answer(ActivityAttendee::ATTENDS_YES, 'Count me in');
        $attendee = $this->activity()->getAttendeeFor($this->member('member-2'));
        self::assertNotNull($attendee);
        self::assertSame(ActivityAttendee::ATTENDS_YES, (int) $attendee->getStatus());
        self::assertSame('Count me in', $attendee->getComment());
        self::assertFalse($this->activity()->isOrganizer($this->member('member-2')));

        $this->answer(ActivityAttendee::ATTENDS_MAYBE, '');
        self::assertSame(ActivityAttendee::ATTENDS_MAYBE, (int) $this->activity()->getAttendeeFor($this->member('member-2'))->getStatus());
        self::assertCount(2, $this->activity()->getAttendees(), 'answering again changes the answer');

        $crawler = $this->client->request('GET', '/activities/' . $this->activityId);
        self::assertStringContainsString(
            $this->translate('activities.count.maybe', ['count' => 1]),
            $crawler->filter('.p-activity-side')->text()
        );
        $this->client->submit($crawler->filter('.p-activity-answer__leave')->form());
        $this->assertResponseRedirects('/activities/' . $this->activityId);
        self::assertNull($this->activity()->getAttendeeFor($this->member('member-2')));
    }

    public function testOrganizerCancelsAndReactivates(): void
    {
        $this->client->loginUser($this->member('member-1'));
        $crawler = $this->client->request('GET', '/activities/' . $this->activityId);

        $this->client->submit($crawler->filter('#cancelActivity form')->form());

        $this->assertResponseRedirects('/activities/' . $this->activityId);
        $activity = $this->activity();
        self::assertTrue($activity->isCancelled());
        foreach ($activity->getAttendees() as $attendee) {
            self::assertSame(ActivityAttendee::ACTIVITY_CANCELLED, (int) $attendee->getStatus());
        }
        $crawler = $this->client->followRedirect();
        self::assertCount(0, $crawler->filter('form.p-activity-answer'), 'nobody answers a cancelled activity');

        $this->client->submit($crawler->filter('form[action="/activities/' . $this->activityId . '/reactivate"]')->form());

        self::assertFalse($this->activity()->isCancelled());
        self::assertSame(ActivityAttendee::ATTENDS_YES, (int) $this->activity()->getAttendeeFor($this->member('member-1'))->getStatus());
    }

    public function testOtherMembersCanNotCancel(): void
    {
        $this->client->loginUser($this->member('member-1'));
        $crawler = $this->client->request('GET', '/activities/' . $this->activityId);
        $token = $crawler->filter('#cancelActivity input[name="_token"]')->attr('value');

        $this->client->loginUser($this->member('member-2'));
        $this->client->request('POST', '/activities/' . $this->activityId . '/cancel', ['_token' => $token]);

        $this->assertResponseStatusCodeSame(403);
        self::assertFalse($this->activity()->isCancelled());
    }

    public function testCalendarDownload(): void
    {
        $this->client->loginUser($this->member('member-2'));

        $this->client->request('GET', '/activity/' . $this->activityId . '/download');

        $this->assertResponseIsSuccessful();
        self::assertStringStartsWith('text/calendar', (string) $this->client->getResponse()->headers->get('Content-Type'));
        self::assertStringContainsString('SUMMARY:' . self::TITLE, (string) $this->client->getResponse()->getContent());
    }

    private function answer(int $status, string $comment): void
    {
        $crawler = $this->client->request('GET', '/activities/' . $this->activityId);
        $form = $crawler->filter('form.p-activity-answer')->form([
            'activity_attend[status]' => (string) $status,
            'activity_attend[comment]' => $comment,
        ]);
        $this->client->submit($form);
        $this->assertResponseRedirects('/activities/' . $this->activityId);
    }

    private function createActivity(string $organizer): Activity
    {
        $entityManager = $this->getEntityManager();
        $member = $this->member($organizer);

        $activity = new Activity();
        $activity
            ->setCreatedBy($member)
            ->setTitle(self::TITLE)
            ->setDescription('<p>Bring a blanket.</p>')
            ->setAddress('Tiergarten')
            ->setLocation($this->berlin())
            ->setStarts(new DateTime('+1 day 12:00'))
            ->setEnds(new DateTime('+1 day 15:00'))
            ->setStatus(Activity::STATUS_ACTIVE)
            ->setOnline(0)
        ;
        $organizerAttendee = new ActivityAttendee($activity, $member, ActivityAttendee::ATTENDS_YES, '', true);
        $activity->addAttendee($organizerAttendee);
        $entityManager->persist($activity);
        $entityManager->persist($organizerAttendee);
        $entityManager->flush();

        return $activity;
    }

    private function activity(): Activity
    {
        $entityManager = $this->getEntityManager();
        $entityManager->clear();

        return $entityManager->getRepository(Activity::class)->find($this->activityId);
    }

    private function member(string $username): Member
    {
        $member = $this->getEntityManager()->getRepository(Member::class)->findOneBy(['username' => $username]);
        self::assertInstanceOf(Member::class, $member);

        return $member;
    }

    private function berlin(): Location
    {
        $berlin = $this->getEntityManager()->getRepository(Location::class)->findOneBy(['geonameId' => 2950159]);
        self::assertInstanceOf(Location::class, $berlin);

        return $berlin;
    }

    /**
     * The text the page shows for a key, so the tests pass with and without the new translations imported.
     */
    private function translate(string $key, array $parameters = [], string $domain = 'messages'): string
    {
        return static::getContainer()->get(TranslatorInterface::class)->trans($key, $parameters, $domain);
    }

    private function getEntityManager(): EntityManagerInterface
    {
        return static::getContainer()->get(EntityManagerInterface::class);
    }
}
