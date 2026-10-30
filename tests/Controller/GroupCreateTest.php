<?php

namespace App\Tests\Controller;

use App\Entity\Group;
use App\Entity\GroupTranslation;
use App\Entity\Member;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\Group as TestGroup;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\DomCrawler\Field\FileFormField;
use Symfony\Component\DomCrawler\Form;

/**
 * A member creates a group, as on /new/group: the description is the group's own text (GroupTranslation), so the
 * volunteers who approve the group and later translators see it, not only its creator.
 */
#[TestGroup('integration')]
class GroupCreateTest extends WebTestCase
{
    private const string NAME = 'Zz test cyclists';
    private const string DESCRIPTION = '<p>For members who travel by bike.</p>';

    private KernelBrowser $client;
    private string $picture;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        // a picture within the allowed size (200 to 400 pixels square)
        $this->picture = sys_get_temp_dir() . '/group-create-test.png';
        imagepng(imagecreatetruecolor(256, 256), $this->picture);
    }

    protected function tearDown(): void
    {
        // the picture of the created group and its thumbnail in the group image directory
        $entityManager = static::getContainer()->get(EntityManagerInterface::class);
        $picture = $entityManager->getConnection()->fetchOne('SELECT picture FROM `group` WHERE name = ?', [self::NAME]);
        if (\is_string($picture) && '' !== $picture) {
            $directory = static::getContainer()->getParameter('group_directory');
            @unlink($directory . '/' . $picture);
            @unlink($directory . '/thumb' . $picture);
        }
        @unlink($this->picture);

        parent::tearDown();
    }

    public function testAMemberCreatesAGroup(): void
    {
        $creator = $this->member('member-3');
        $this->client->loginUser($creator);
        $crawler = $this->client->request('GET', '/new/group');
        $this->assertResponseIsSuccessful();

        $this->client->submit($this->form($crawler, 'Yes'));

        $this->assertResponseRedirects();
        $group = $this->group();
        self::assertFalse($group->isMembersOnly());
        self::assertNotSame('', $group->getPicture());
        $descriptions = $group->getDescriptions();
        self::assertSame(['en'], array_keys($descriptions));
        self::assertInstanceOf(GroupTranslation::class, $descriptions['en']);
        self::assertSame(self::DESCRIPTION, $descriptions['en']->getContent());
        self::assertSame($creator->getId(), $descriptions['en']->getTranslator()?->getId());
    }

    public function testTheApprovalPageShowsTheDescription(): void
    {
        $this->client->loginUser($this->member('member-3'));
        $crawler = $this->client->request('GET', '/new/group');
        $this->client->submit($this->form($crawler, 'No'));

        // bwadmin has the Group right (level 10) in the fixtures
        $this->client->loginUser($this->member('bwadmin'));
        $this->client->request('GET', '/admin/groups/approval');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('table', self::NAME);
        $this->assertSelectorTextContains('table', 'For members who travel by bike.');
    }

    public function testAGroupNeedsAPicture(): void
    {
        $this->client->loginUser($this->member('member-3'));
        $crawler = $this->client->request('GET', '/new/group');
        // no required attribute: the form shows the hint, not the browser
        self::assertNull($crawler->filter('input[name="group[picture]"]')->attr('required'));

        $form = $this->form($crawler, 'Yes');
        $form['group[picture]']->setValue('');
        $crawler = $this->client->submit($form);

        self::assertFalse($this->client->getResponse()->isRedirect());
        self::assertCount(1, $crawler->filter('#group_picture.is-invalid'));
        self::assertCount(1, $crawler->filter('.p-gnew__pic .p-gnew__error'));
        self::assertNull(static::getContainer()->get(EntityManagerInterface::class)
            ->getRepository(Group::class)->findOneBy(['name' => self::NAME]));
    }

    private function form(Crawler $crawler, string $visiblePosts): Form
    {
        $form = $crawler->filter('form[name=group]')->form([
            'group[name]' => self::NAME,
            'group[description]' => self::DESCRIPTION,
            'group[type]' => 'Public',
            'group[membersOnly]' => $visiblePosts,
        ]);
        $picture = $form['group[picture]'];
        self::assertInstanceOf(FileFormField::class, $picture);
        $picture->upload($this->picture);

        return $form;
    }

    private function group(): Group
    {
        $entityManager = static::getContainer()->get(EntityManagerInterface::class);
        $entityManager->clear();
        $group = $entityManager->getRepository(Group::class)->findOneBy(['name' => self::NAME]);
        self::assertInstanceOf(Group::class, $group);

        return $group;
    }

    private function member(string $username): Member
    {
        $member = static::getContainer()->get(EntityManagerInterface::class)
            ->getRepository(Member::class)->findOneBy(['username' => $username]);
        self::assertInstanceOf(Member::class, $member);

        return $member;
    }
}
