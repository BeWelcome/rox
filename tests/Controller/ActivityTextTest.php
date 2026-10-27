<?php

namespace App\Tests\Controller;

use App\Dto\ActivityDto;
use App\Entity\Activity;
use App\Entity\Member;
use App\Model\ActivityModel;
use DateTime;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Activities near me without any activity nearby, and how titles and descriptions are kept and shown.
 */
#[Group('integration')]
class ActivityTextTest extends WebTestCase
{
    private KernelBrowser $client;
    private EntityManagerInterface $entityManager;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->entityManager = static::getContainer()->get(EntityManagerInterface::class);
    }

    public function testNearMeWithoutActivitiesNearbySaysSo(): void
    {
        static::getContainer()->get(Connection::class)->executeStatement('DELETE FROM activitiesattendees');
        static::getContainer()->get(Connection::class)->executeStatement('DELETE FROM activities');
        $this->client->loginUser($this->member());

        $this->client->request('GET', '/activities/nearme');

        // rendered with the radius (failed with U_ARGUMENT_TYPE_MISMATCH); the test database may lack the text itself
        $this->assertResponseIsSuccessful();
        $text = $this->client->getCrawler()->filter('main')->text();
        self::assertTrue(
            str_contains($text, 'within 25 km of your place') || str_contains($text, 'activities.empty.within'),
            'the near me page says there are no activities nearby'
        );
    }

    public function testTheTitleIsShownAsWritten(): void
    {
        $activity = $this->save("Let's meet \"K.S\" & co >> 8pm", '<p>See you there.</p>');
        $this->client->loginUser($this->member());

        $crawler = $this->client->request('GET', '/activities/' . $activity->getId());

        $this->assertResponseIsSuccessful();
        self::assertStringStartsWith("Let's meet \"K.S\" & co >> 8pm |", $crawler->filter('title')->text());
        self::assertStringContainsString("Let's meet \"K.S\" & co >> 8pm", $crawler->filter('h1')->text());
    }

    public function testTitleAndDescriptionArePurifiedWhenSaved(): void
    {
        $activity = $this->save(
            '<b>Picnic</b> <script>alert(1)</script>at the lake',
            '<p onclick="steal()">Bring food.</p><script>alert(1)</script><a href="javascript:alert(1)">map</a>'
        );

        // plain text: tags go, and so does text in angle brackets, which the purifier takes for a tag
        self::assertSame('Picnic at the lake', $activity->getTitle());
        self::assertStringContainsString('Bring food.', $activity->getDescription());
        self::assertStringNotContainsString('script', $activity->getDescription());
        self::assertStringNotContainsString('onclick', $activity->getDescription());
        self::assertStringNotContainsString('javascript:', $activity->getDescription());
    }

    private function save(string $title, string $description): Activity
    {
        $member = $this->member();
        $data = new ActivityDto();
        $data->title = $title;
        $data->description = $description;
        $data->location = $member->getActiveAddress()->getLocation();
        $data->starts = new DateTime('+3 days 18:00');
        $data->ends = new DateTime('+3 days 21:00');

        return static::getContainer()->get(ActivityModel::class)->create($data, $member);
    }

    private function member(): Member
    {
        $member = $this->entityManager->getRepository(Member::class)->findOneBy(['username' => 'member-2']);
        self::assertNotFalse($member->getActiveAddress(), 'member-2 needs an address in the fixtures');

        return $member;
    }
}
