<?php

namespace App\Tests\Controller;

use App\Entity\Group;
use App\Entity\Member;
use App\Entity\Poll;
use App\Entity\PollChoice;
use App\Entity\PollTranslation;
use DateTime;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group as TestGroup;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * The polls pages (App\Controller\PollController) as members use them. Each test creates its own polls
 * and removes them again.
 *
 * @infection-ignore-all
 */
#[TestGroup('integration')]
class PollControllerTest extends WebTestCase
{
    private const string TITLE = 'Poll controller test: where to meet?';

    private KernelBrowser $client;
    /** @var int[] */
    private array $pollIds = [];

    protected function setUp(): void
    {
        $this->client = static::createClient();
    }

    protected function tearDown(): void
    {
        $entityManager = $this->getEntityManager();
        $entityManager->clear();
        $connection = $entityManager->getConnection();
        foreach ($this->pollIds as $id) {
            // Choices, votes, groups and translations go with the poll (ON DELETE CASCADE)
            $connection->executeStatement('DELETE FROM poll WHERE id = ?', [$id]);
        }

        parent::tearDown();
    }

    public static function listPages(): array
    {
        return [
            'open' => ['/polls', true],
            'open list' => ['/polls/list/open', true],
            'open page 1' => ['/polls/list/open/page/1', true],
            'closed' => ['/polls/list/closed', false],
            'drafts' => ['/polls/list/new', false],
            'drafts (legacy url)' => ['/polls/listProject', false],
            'all' => ['/polls/list/all', true],
            'contributed' => ['/polls/list/contributed', false],
        ];
    }

    #[DataProvider('listPages')]
    public function testListPagesRender(string $url, bool $listsTheOpenPoll): void
    {
        $this->createPoll('member-1', Poll::STATUS_OPEN, ['Park', 'Cafe']);
        $this->client->loginUser($this->member('member-2'));

        $crawler = $this->client->request('GET', $url);

        $this->assertResponseIsSuccessful();
        self::assertCount(1, $crawler->filter('.p-polls__tabs'));
        self::assertSame($listsTheOpenPoll, str_contains($crawler->filter('.p-polls__body')->text(''), self::TITLE));
    }

    public function testCreateAddChoicesAndOpen(): void
    {
        $this->client->loginUser($this->member('member-1'));
        $crawler = $this->client->request('GET', '/polls/create');
        $this->assertResponseIsSuccessful();

        $this->client->submit($crawler->filter('form.p-polls-form')->form([
            'poll[title]' => self::TITLE,
            'poll[description]' => '<p>Pick a place.</p>',
            'poll[ended]' => new DateTime('+1 week')->format('Y-m-d\TH:i'),
        ]));

        $poll = $this->findPoll(self::TITLE);
        $this->pollIds[] = $poll->getId();
        $this->assertResponseRedirects('/polls/update/' . $poll->getId());
        self::assertTrue($poll->isDraft());
        self::assertSame(['en' => self::TITLE], $poll->getTitles());

        // Opening needs two choices
        $crawler = $this->client->request('GET', '/polls/update/' . $poll->getId());
        $this->client->submit($crawler->filter('form.p-polls-form')->form(['poll[status]' => Poll::STATUS_OPEN]));
        $this->assertResponseStatusCodeSame(422);
        self::assertTrue($this->reload($poll)->isDraft());

        foreach (['Park', 'Cafe'] as $text) {
            $crawler = $this->client->request('GET', '/polls/update/' . $poll->getId());
            $this->client->submit($crawler->filter('form[action$="/choices"]')->form(['poll_choice[text]' => $text]));
            $this->assertResponseRedirects();
        }
        $crawler = $this->client->request('GET', '/polls/update/' . $poll->getId());
        $this->client->submit($crawler->filter('form.p-polls-form')->form(['poll[status]' => Poll::STATUS_OPEN]));

        $poll = $this->reload($poll);
        self::assertTrue($poll->isOpen());
        self::assertSame(['Park', 'Cafe'], array_map(static fn (PollChoice $choice) => $choice->getText(), $poll->getChoices()->toArray()));
    }

    public function testMemberCreatesSeveralPolls(): void
    {
        // Poll::$creator was mapped one-to-one, which made IdCreator unique in databases built from the mapping.
        $first = $this->createPoll('member-1', Poll::STATUS_DRAFT, ['A', 'B']);
        $second = $this->createPoll('member-1', Poll::STATUS_DRAFT, ['C', 'D']);

        self::assertNotSame($first->getId(), $second->getId());
        self::assertSame($first->getCreator(), $second->getCreator());
    }

    public function testDraftIsOnlyForItsCreator(): void
    {
        $poll = $this->createPoll('member-1', Poll::STATUS_DRAFT, ['A', 'B']);

        $this->client->loginUser($this->member('member-2'));
        $this->client->request('GET', '/polls/contribute/' . $poll->getId());
        $this->assertResponseStatusCodeSame(403);
        $crawler = $this->client->request('GET', '/polls/list/new');
        self::assertStringNotContainsString(self::TITLE, $crawler->filter('.p-polls__body')->text(''));

        $this->client->loginUser($this->member('member-1'));
        $crawler = $this->client->request('GET', '/polls/list/new');
        self::assertStringContainsString(self::TITLE, $crawler->filter('.p-polls__body')->text(''));
        $this->client->request('GET', '/polls/update/' . $poll->getId());
        $this->assertResponseIsSuccessful();
    }

    public function testOthersCanNotEdit(): void
    {
        $poll = $this->createPoll('member-1', Poll::STATUS_DRAFT, ['A', 'B']);
        $this->client->loginUser($this->member('member-2'));

        $this->client->request('GET', '/polls/update/' . $poll->getId());

        $this->assertResponseStatusCodeSame(403);
    }

    public function testVoteForOneChoiceThenSeeResults(): void
    {
        $poll = $this->createPoll('member-1', Poll::STATUS_OPEN, ['Park', 'Cafe'], ['anonymous' => false]);
        $cafe = $poll->getChoices()[1];
        $this->client->loginUser($this->member('member-2'));

        // Results are visible after voting
        $this->client->request('GET', '/polls/results/' . $poll->getId());
        $this->assertResponseRedirects('/polls/contribute/' . $poll->getId());

        $crawler = $this->client->request('GET', '/polls/contribute/' . $poll->getId());
        $this->client->submit($crawler->filter('form.p-poll-vote')->form(['poll_vote[choices]' => (string) $cafe->getId()]));
        $this->assertResponseRedirects('/polls/contribute/' . $poll->getId());

        $poll = $this->reload($poll);
        self::assertSame([0, 1], array_map(static fn (PollChoice $choice) => $choice->getVotes(), $poll->getChoices()->toArray()));
        self::assertCount(1, $poll->getVotes());
        self::assertCount(1, $poll->getVotes()->first()->getChoices(), 'who chose what is kept for polls that are not anonymous');

        $crawler = $this->client->request('GET', '/polls/contribute/' . $poll->getId());
        self::assertCount(0, $crawler->filter('form.p-poll-vote'), 'members vote once');
        $crawler = $this->client->request('GET', '/polls/seeresults/' . $poll->getId());
        $this->assertResponseIsSuccessful();
        self::assertStringContainsString('Cafe', $crawler->filter('.p-poll-results')->text());
        self::assertStringContainsString('100 %', $crawler->filter('.p-poll-results')->text());
    }

    public function testVoteForSeveralChoicesWithComment(): void
    {
        $poll = $this->createPoll('member-1', Poll::STATUS_OPEN, ['Park', 'Cafe', 'Beach'], [
            'type' => Poll::CHOICE_MULTIPLE,
            'comment' => true,
        ]);
        [$park, , $beach] = $poll->getChoices()->toArray();
        $this->client->loginUser($this->member('member-3'));
        $crawler = $this->client->request('GET', '/polls/contribute/' . $poll->getId());

        $form = $crawler->filter('form.p-poll-vote')->form();
        $form['poll_vote[choices]'][0]->tick();
        $form['poll_vote[choices]'][2]->tick();
        $form['poll_vote[comment]'] = 'Both are fine';
        $this->client->submit($form);

        $poll = $this->reload($poll);
        self::assertSame([1, 0, 1], array_map(static fn (PollChoice $choice) => $choice->getVotes(), $poll->getChoices()->toArray()));
        self::assertSame('Both are fine', $poll->getVotes()->first()->getComment());
        self::assertNotNull($park);
        self::assertNotNull($beach);
    }

    public function testTakeBackVote(): void
    {
        $poll = $this->createPoll('member-1', Poll::STATUS_OPEN, ['Park', 'Cafe'], ['anonymous' => false, 'changeVote' => true]);
        $this->client->loginUser($this->member('member-2'));
        $crawler = $this->client->request('GET', '/polls/contribute/' . $poll->getId());
        $this->client->submit($crawler->filter('form.p-poll-vote')->form(['poll_vote[choices]' => (string) $poll->getChoices()[0]->getId()]));

        $crawler = $this->client->request('GET', '/polls/contribute/' . $poll->getId());
        $this->client->submit($crawler->filter('form[action$="/cancelvote/' . $poll->getId() . '"]')->form());

        $poll = $this->reload($poll);
        self::assertCount(0, $poll->getVotes());
        self::assertSame(0, $poll->getChoices()[0]->getVotes());
        $crawler = $this->client->request('GET', '/polls/contribute/' . $poll->getId());
        self::assertCount(1, $crawler->filter('form.p-poll-vote'), 'the member can vote again');
    }

    public function testAnonymousVotesCanNotBeTakenBack(): void
    {
        $poll = $this->createPoll('member-1', Poll::STATUS_OPEN, ['Park', 'Cafe'], ['anonymous' => true, 'changeVote' => true]);
        $this->client->loginUser($this->member('member-2'));
        $crawler = $this->client->request('GET', '/polls/contribute/' . $poll->getId());
        $this->client->submit($crawler->filter('form.p-poll-vote')->form(['poll_vote[choices]' => (string) $poll->getChoices()[0]->getId()]));

        $crawler = $this->client->request('GET', '/polls/contribute/' . $poll->getId());

        self::assertCount(0, $crawler->filter('form[action$="/cancelvote/' . $poll->getId() . '"]'));
        self::assertCount(0, $this->reload($poll)->getVotes()->first()->getChoices(), 'anonymous votes do not keep the choices');
    }

    public function testPollForOtherGroupsIsNotShown(): void
    {
        $vegans = $this->getEntityManager()->getRepository(Group::class)->find(3);
        self::assertInstanceOf(Group::class, $vegans);
        $poll = $this->createPoll('member-6', Poll::STATUS_OPEN, ['A', 'B'], ['groups' => [$vegans]]);
        $this->client->loginUser($this->member('member-2'));

        $this->client->request('GET', '/polls/contribute/' . $poll->getId());
        $this->assertResponseStatusCodeSame(403);
        $crawler = $this->client->request('GET', '/polls');
        self::assertStringNotContainsString(self::TITLE, $crawler->filter('.p-polls__body')->text(''));
    }

    public function testPollPastItsEndIsClosed(): void
    {
        $poll = $this->createPoll('member-1', Poll::STATUS_OPEN, ['A', 'B'], ['ended' => new DateTimeImmutable('-1 day')]);
        $this->client->loginUser($this->member('member-2'));

        $crawler = $this->client->request('GET', '/polls/list/closed');
        self::assertStringContainsString(self::TITLE, $crawler->filter('.p-polls__body')->text(''));
        $crawler = $this->client->request('GET', '/polls/contribute/' . $poll->getId());
        self::assertCount(0, $crawler->filter('form.p-poll-vote'));
    }

    public function testTranslationsOfOldPollsAreKept(): void
    {
        $poll = $this->createPoll('member-1', Poll::STATUS_CLOSED, ['A', 'B']);
        $entityManager = $this->getEntityManager();
        $poll->addTranslation(new PollTranslation('fr', 'title', 'Où se retrouver ?'));
        $entityManager->flush();

        $titles = $this->reload($poll)->getTitles();

        self::assertSame(self::TITLE, $titles['en']);
        self::assertSame('Où se retrouver ?', $titles['fr']);
    }

    /**
     * @param string[] $choices
     */
    private function createPoll(string $creator, string $status, array $choices, array $options = []): Poll
    {
        $entityManager = $this->getEntityManager();
        $poll = new Poll();
        $poll->setTranslatableLocale('en');
        $poll->setCreator($this->member($creator));
        $poll->setTitle(self::TITLE);
        $poll->setDescription('<p>Where should we meet?</p>');
        $poll->setStatus($status);
        $poll->setChoiceType($options['type'] ?? Poll::CHOICE_SINGLE);
        $poll->setStartsAt(new DateTimeImmutable('-1 day'));
        $poll->setEndsAt($options['ended'] ?? new DateTimeImmutable('+1 week'));
        $poll->setResultsVisibility(Poll::RESULTS_AFTER_VOTE);
        $poll->setAnonymous($options['anonymous'] ?? true);
        $poll->setAllowComments($options['comment'] ?? false);
        $poll->setCanChangeVote($options['changeVote'] ?? false);
        foreach ($options['groups'] ?? [] as $group) {
            $poll->addGroup($group);
        }
        foreach ($choices as $text) {
            $choice = new PollChoice();
            $choice->setTranslatableLocale('en');
            $choice->setText($text);
            $poll->addChoice($choice);
            $entityManager->persist($choice);
        }
        $entityManager->persist($poll);
        $entityManager->flush();
        $this->pollIds[] = $poll->getId();

        return $poll;
    }

    private function reload(Poll $poll): Poll
    {
        $entityManager = $this->getEntityManager();
        $entityManager->clear();

        return $entityManager->getRepository(Poll::class)->find($poll->getId());
    }

    private function findPoll(string $title): Poll
    {
        $poll = $this->getEntityManager()->getRepository(Poll::class)->findOneBy(['title' => $title]);
        self::assertInstanceOf(Poll::class, $poll);

        return $poll;
    }

    private function member(string $username): Member
    {
        $member = $this->getEntityManager()->getRepository(Member::class)->findOneBy(['username' => $username]);
        self::assertInstanceOf(Member::class, $member);

        return $member;
    }

    private function getEntityManager(): EntityManagerInterface
    {
        return static::getContainer()->get(EntityManagerInterface::class);
    }
}
