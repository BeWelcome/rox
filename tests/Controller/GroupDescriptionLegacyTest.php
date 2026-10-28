<?php

namespace App\Tests\Controller;

use App\Entity\Group;
use App\Entity\Member;
use DAMA\DoctrineTestBundle\PHPUnit\SkipDatabaseRollback;
use Doctrine\ORM\EntityManagerInterface;
use GroupsModel;
use PHPUnit\Framework\Attributes\Group as TestGroup;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use ReflectionProperty;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DomCrawler\Crawler;

/**
 * The legacy group pages read and write the description in group_translation, as App\Entity\Group does: the
 * group page shows it, the group settings change it.
 *
 * As in ForumTranslationsTest: the legacy code uses its own database connection (no rollback, cleanup in
 * tearDown()) and keeps state in static singletons (a process per test).
 *
 * @infection-ignore-all
 */
#[TestGroup('integration')]
#[SkipDatabaseRollback]
#[RunTestsInSeparateProcesses]
class GroupDescriptionLegacyTest extends WebTestCase
{
    private const string NAME = 'Zz test legacy description';

    private KernelBrowser $client;
    private string $originalWorkingDirectory;
    private int $groupId;

    protected function setUp(): void
    {
        $this->originalWorkingDirectory = (string) getcwd();
        chdir(__DIR__ . '/../../public');
        $this->client = static::createClient();
        $entityManager = $this->entityManager();
        $group = new Group();
        $group->setName(self::NAME)->setType('Public')->setMembersOnly(false)->setPicture('')->setApproved(Group::APPROVED);
        $group->setDescription('en', 'Written in English', $this->member('member-3'));
        $group->setDescription('de', 'Auf Deutsch geschrieben', $this->member('member-3'));
        $entityManager->persist($group);
        $entityManager->flush();
        $this->groupId = $group->getId();
    }

    protected function tearDown(): void
    {
        $this->entityManager()->getConnection()->executeStatement('DELETE FROM `groups` WHERE id = ?', [$this->groupId]);
        chdir($this->originalWorkingDirectory);

        parent::tearDown();
    }

    public function testTheGroupPageShowsTheDescriptionInTheMembersLanguage(): void
    {
        $this->client->loginUser($this->member('member-2'));
        $this->client->request('GET', '/rox/in/de');

        $crawler = $this->legacyRequest('/group/' . $this->groupId);

        $this->assertResponseIsSuccessful();
        self::assertStringContainsString('Auf Deutsch geschrieben', $crawler->filter('body')->text());
    }

    public function testTheSettingsChangeTheDescriptionInTheMembersLanguage(): void
    {
        $member = $this->member('member-2');
        $this->client->loginUser($member);
        $this->legacyRequest('/group/' . $this->groupId);

        // as the group settings form does (GroupsController::changeGroupSettings())
        $legacyGroup = new GroupsModel()->findGroup($this->groupId);
        self::assertTrue($legacyGroup->setDescription("Changed\nin English"));

        $rows = $this->entityManager()->getConnection()->fetchAllAssociativeIndexed(
            'SELECT locale, content, translator_id, updated_at FROM group_translation WHERE object_id = ? ORDER BY locale',
            [$this->groupId],
        );
        self::assertSame(['de', 'en'], array_keys($rows));
        self::assertSame('Changed<br />in English', $rows['en']['content']);
        self::assertSame($member->getId(), (int) $rows['en']['translator_id']);
        self::assertNotNull($rows['en']['updated_at']);
        self::assertSame('Auf Deutsch geschrieben', $rows['de']['content']);
    }

    /**
     * Requests a legacy page, as ForumTranslationsTest::legacyRequest() does.
     */
    private function legacyRequest(string $url): Crawler
    {
        $server = $_SERVER;
        $_SERVER['argc'] = 0;
        $_SERVER['REQUEST_URI'] = $url;
        $_SERVER['SCRIPT_NAME'] = '/index.php';
        foreach ([['PRequest', '_instance'], ['RequestRouter', '_request_args']] as [$class, $property]) {
            if (class_exists($class, false)) {
                new ReflectionProperty($class, $property)->setValue(null, null);
            }
        }
        try {
            $this->client->followRedirects();

            return $this->client->request('GET', $url);
        } finally {
            $_SERVER = $server;
        }
    }

    private function member(string $username): Member
    {
        $member = $this->entityManager()->getRepository(Member::class)->findOneBy(['username' => $username]);
        self::assertInstanceOf(Member::class, $member);

        return $member;
    }

    private function entityManager(): EntityManagerInterface
    {
        return static::getContainer()->get(EntityManagerInterface::class);
    }
}
