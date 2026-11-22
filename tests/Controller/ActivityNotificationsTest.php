<?php

namespace App\Tests\Controller;

use App\Entity\Activity;
use App\Entity\ActivityAttendee;
use App\Entity\Location;
use App\Entity\Member;
use App\Entity\MemberActivityNotificationSent;
use App\Entity\MemberPreference;
use App\Entity\Preference;
use App\Model\ActivityModel;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Mime\Email;

/**
 * The mails about new activities near a member (preference ActivitiesNotifications), like the trip notifications:
 * right away when an activity is created, saved again or reactivated, or daily, weekly or monthly
 * (activities:notifications:send). The fixture members 1 to 5 live in Berlin, within each other's radius.
 * member-2 wants them right away, member-3 daily, the others never (the default).
 */
#[Group('integration')]
class ActivityNotificationsTest extends WebTestCase
{
    private const int BERLIN = 2950159;

    private KernelBrowser $client;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->prefer('member-2', Preference::ACTIVITY_NOTIFICATIONS_IMMEDIATELY);
        $this->prefer('member-3', Preference::ACTIVITY_NOTIFICATIONS_DAILY);
    }

    public function testACreatedActivityIsMailedToWhoWantsItRightAway(): void
    {
        $this->client->loginUser($this->member('member-4'));
        $crawler = $this->client->request('GET', '/activities/create');
        $berlin = $this->berlin();

        $this->client->submit($crawler->filter('form.p-activities-form')->form([
            'activity[title]' => 'Zz notification picnic',
            'activity[location][fullname]' => 'Berlin, Germany',
            'activity[location][name]' => 'Berlin',
            'activity[location][geoname_id]' => (string) $berlin->getGeonameId(),
            'activity[location][latitude]' => (string) $berlin->getLatitude(),
            'activity[location][longitude]' => (string) $berlin->getLongitude(),
            'activity[starts]' => new DateTime('+3 days 18:00')->format('Y-m-d\TH:i'),
            'activity[ends]' => new DateTime('+3 days 21:00')->format('Y-m-d\TH:i'),
            'activity[description]' => '<p>Bring something to share.</p>',
        ]));

        self::assertResponseRedirects();
        $activity = $this->entityManager()->getRepository(Activity::class)->findOneBy(['title' => 'Zz notification picnic']);
        self::assertInstanceOf(Activity::class, $activity);
        self::assertNotNull($activity->getCreated());
        $mails = $this->mailsAbout($activity);
        self::assertSame([$this->member('member-2')->getEmail()], array_keys($mails));
        self::assertStringContainsString('Zz notification picnic', (string) $mails[$this->member('member-2')->getEmail()]->getHtmlBody());
    }

    public function testSavingTheActivityAgainMailsNoOneTwice(): void
    {
        $activity = $this->activity('member-4');
        $model = static::getContainer()->get(ActivityModel::class);

        self::assertSame(1, $model->notifyMembersAboutActivity($activity));
        self::assertSame(0, $model->notifyMembersAboutActivity($activity));
        self::assertCount(1, $this->entityManager()->getRepository(MemberActivityNotificationSent::class)->findBy(['activity' => $activity]));
    }

    public function testNoMailForTheCreatorOrWhoAnsweredAlready(): void
    {
        $model = static::getContainer()->get(ActivityModel::class);

        // member-2 created it
        self::assertSame(0, $model->notifyMembersAboutActivity($this->activity('member-2')));
        // member-2 answered maybe
        $activity = $this->activity('member-4');
        $answer = new ActivityAttendee($activity, $this->member('member-2'), ActivityAttendee::ATTENDS_MAYBE, '');
        $this->entityManager()->persist($answer);
        $this->entityManager()->flush();
        self::assertSame(0, $model->notifyMembersAboutActivity($activity));
    }

    public function testNoMailForOnlineCancelledOrPastActivities(): void
    {
        $model = static::getContainer()->get(ActivityModel::class);

        self::assertSame(0, $model->notifyMembersAboutActivity($this->activity('member-4', static fn (Activity $activity) => $activity->setOnline(1))));
        self::assertSame(0, $model->notifyMembersAboutActivity($this->activity('member-4', static fn (Activity $activity) => $activity->setStatus(Activity::STATUS_CANCELLED))));
        self::assertSame(0, $model->notifyMembersAboutActivity($this->activity('member-4', static fn (Activity $activity) => $activity
            ->setStarts(new DateTime('-3 days 18:00'))->setEnds(new DateTime('-3 days 21:00')))));
    }

    public function testTheDailyRunMailsTheActivitiesOfTheDayInOneMail(): void
    {
        $first = $this->activity('member-4');
        $second = $this->activity('member-5', static fn (Activity $activity) => $activity->setTitle('Zz notification walk'));
        $command = new Application(static::$kernel)->find('activities:notifications:send');
        $tester = new CommandTester($command);

        self::assertSame(Command::SUCCESS, $tester->execute(['frequency' => 'daily']));

        // one mail to who wants them daily (member-3), with both; nothing for who wants them right away
        self::assertStringContainsString('Sent 1 activity notification emails.', $tester->getDisplay());
        $daily = $this->member('member-3')->getEmail();
        self::assertSame([$daily], array_keys($this->mailsAbout($first)));
        self::assertSame($this->mailsAbout($first)[$daily], $this->mailsAbout($second)[$daily]);
        $mail = $this->mailsAbout($first)[$daily];
        self::assertStringContainsString('Zz notification meeting', (string) $mail->getHtmlBody());
        self::assertStringContainsString('Zz notification walk', (string) $mail->getHtmlBody());
        self::assertSame('activity.notification.digest.subject.daily', $mail->getSubject());
        self::assertCount(1, self::getMailerMessages());
        // the next run doesn't mail them again
        $tester->execute(['frequency' => 'daily']);
        self::assertStringContainsString('Sent 0 activity notification emails.', $tester->getDisplay());
        self::assertCount(1, self::getMailerMessages());
    }

    public function testTheWeeklyRunLeavesTheDailyMembersAlone(): void
    {
        $this->activity('member-4');
        $tester = new CommandTester(new Application(static::$kernel)->find('activities:notifications:send'));

        self::assertSame(Command::SUCCESS, $tester->execute(['frequency' => 'weekly']));

        self::assertStringContainsString('Sent 0 activity notification emails.', $tester->getDisplay());
        self::assertCount(0, self::getMailerMessages());
    }

    public function testTheCommandRefusesOtherFrequencies(): void
    {
        $tester = new CommandTester(new Application(static::$kernel)->find('activities:notifications:send'));

        self::assertSame(Command::INVALID, $tester->execute(['frequency' => 'yearly']));
    }

    /**
     * An activity in Berlin three days ahead, created now by $creator, who attends.
     */
    private function activity(string $creator, ?callable $change = null): Activity
    {
        $activity = new Activity()
            ->setCreatedBy($this->member($creator))
            ->setTitle('Zz notification meeting')
            ->setDescription('<p>Coffee.</p>')
            ->setLocation($this->berlin())
            ->setStarts(new DateTime('+3 days 18:00'))
            ->setEnds(new DateTime('+3 days 21:00'))
            ->setStatus(Activity::STATUS_ACTIVE);
        if (null !== $change) {
            $change($activity);
        }
        $entityManager = $this->entityManager();
        $entityManager->persist($activity);
        $entityManager->flush();

        return $activity;
    }

    /**
     * @return array<string, Email> the mails linking to the activity, by receiver address
     */
    private function mailsAbout(Activity $activity): array
    {
        $mails = [];
        foreach (self::getMailerMessages() as $message) {
            if ($message instanceof Email && str_contains((string) $message->getHtmlBody(), '/activities/' . $activity->getId())) {
                $mails[$message->getTo()[0]->getAddress()] = $message;
            }
        }
        ksort($mails);

        return $mails;
    }

    private function prefer(string $username, string $value): void
    {
        $entityManager = $this->entityManager();
        $preference = $entityManager->getRepository(Preference::class)->findOneBy(['codename' => Preference::ACTIVITY_NOTIFICATIONS]);
        self::assertInstanceOf(Preference::class, $preference);
        $memberPreference = new MemberPreference();
        $memberPreference->setMember($this->member($username))->setPreference($preference)->setValue($value);
        $entityManager->persist($memberPreference);
        $entityManager->flush();
    }

    private function member(string $username): Member
    {
        $member = $this->entityManager()->getRepository(Member::class)->findOneBy(['username' => $username]);
        self::assertInstanceOf(Member::class, $member);

        return $member;
    }

    private function berlin(): Location
    {
        $berlin = $this->entityManager()->getRepository(Location::class)->findOneBy(['geonameId' => self::BERLIN]);
        self::assertInstanceOf(Location::class, $berlin);

        return $berlin;
    }

    private function entityManager(): EntityManagerInterface
    {
        return static::getContainer()->get(EntityManagerInterface::class);
    }
}
