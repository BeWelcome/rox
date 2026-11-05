<?php

namespace App\Tests\Controller\Admin;

use App\Entity\GroupCategory;
use App\Entity\Member;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * The group admin team keeps the list of group categories; the name is entered per language.
 */
#[Group('integration')]
final class GroupCategoryAdminTest extends WebTestCase
{
    public function testMembersWithoutTheGroupRightCantSeeTheCategories(): void
    {
        $client = static::createClient();
        $client->loginUser($this->getMember('member-2'));

        $client->request('GET', '/admin/groups/categories');

        self::assertResponseStatusCodeSame(403);
    }

    public function testAGroupAdminAddsACategoryAndNamesItInOtherLanguages(): void
    {
        $client = $this->groupAdmin();

        $crawler = $client->request('GET', '/admin/groups/categories');
        self::assertResponseIsSuccessful();
        $client->submit($crawler->filter('form[name="group_category_name"]')->form([
            'group_category_name[name]' => 'Outdoors',
        ]));

        $category = $this->findCategory('Outdoors');
        self::assertResponseRedirects('/admin/groups/categories/' . $category->getId());

        $crawler = $client->followRedirect();
        $client->submit($crawler->filter('form[name="group_category_name"]')->form([
            'group_category_name[locale]' => 'de',
            'group_category_name[name]' => 'Draußen',
        ]));
        self::assertResponseRedirects('/admin/groups/categories/' . $category->getId());

        $category = $this->findCategory('Outdoors');
        self::assertSame('Draußen', $category->getName('de'));
        self::assertSame('Outdoors', $category->getName('fr'), 'languages without a name fall back to English');

        $client->request('GET', '/admin/groups/categories');
        self::assertSelectorTextContains('.p-gadmin__table', 'Outdoors');
    }

    public function testAnEmptyNameRemovesALanguageButNotEnglish(): void
    {
        $client = $this->groupAdmin();
        $category = $this->createCategory(['en' => 'Food', 'de' => 'Essen']);
        $url = '/admin/groups/categories/' . $category->getId();

        $crawler = $client->request('GET', $url);
        $client->submit($crawler->filter('form[name="group_category_name"]')->form([
            'group_category_name[locale]' => 'de',
            'group_category_name[name]' => '',
        ]));
        self::assertResponseRedirects($url);

        $crawler = $client->request('GET', $url);
        $client->submit($crawler->filter('form[name="group_category_name"]')->form([
            'group_category_name[locale]' => 'en',
            'group_category_name[name]' => '',
        ]));
        self::assertResponseStatusCodeSame(422);

        $category = $this->findCategory('Food');
        self::assertSame(['en'], array_keys($category->getNames()));
    }

    public function testAGroupAdminDeletesACategory(): void
    {
        $client = $this->groupAdmin();
        $category = $this->createCategory(['en' => 'Music']);
        $id = $category->getId();

        $crawler = $client->request('GET', '/admin/groups/categories/' . $id);
        // asks first in the site's dialog (confirm.js), not the browser's
        self::assertNotEmpty($crawler->filter('form[action$="/delete"] button')->attr('data-confirm'));
        self::assertNull($crawler->filter('form[action$="/delete"]')->attr('onsubmit'));
        $client->submit($crawler->filter('form[action$="/delete"]')->form());

        self::assertResponseRedirects('/admin/groups/categories');
        $entityManager = static::getContainer()->get(EntityManagerInterface::class);
        $entityManager->clear();
        self::assertNull($entityManager->find(GroupCategory::class, $id));
    }

    public function testTheIconAndTheKeywordsSave(): void
    {
        $client = $this->groupAdmin();
        $category = $this->createCategory(['en' => 'Outdoors']);
        $url = '/admin/groups/categories/' . $category->getId();

        $crawler = $client->request('GET', $url);
        self::assertResponseIsSuccessful();
        $client->submit($crawler->filter('form[action$="/icon"]')->selectButton('person-hiking')->form());
        self::assertResponseRedirects($url);
        self::assertSame('person-hiking', $this->findCategory('Outdoors')->getIcon());

        $crawler = $client->request('GET', $url);
        $client->submit($crawler->filter('form[action$="/keywords"]')->form(['add' => 'Hike, mountains']));
        self::assertSame(['hike', 'mountains'], $this->findCategory('Outdoors')->getKeywords());

        $crawler = $client->request('GET', $url);
        self::assertCount(1, $crawler->filter('.p-gadmin__icon.is-on[value="person-hiking"]'));
        $client->submit($crawler->filter('form[action$="/keywords"] button[value="hike"]')->form());
        self::assertSame(['mountains'], $this->findCategory('Outdoors')->getKeywords());
        self::assertSame(1, $this->countLogs('%(Outdoors): icon set to person-hiking'));
        self::assertSame(2, $this->countLogs('%(Outdoors): keywords changed'));
    }

    public function testAGroupIsAddedToAndRemovedFromACategory(): void
    {
        $client = $this->groupAdmin();
        $category = $this->createCategory(['en' => 'Outdoors']);
        $group = $this->findGroup('Hiking Enthusiasts');
        $url = '/admin/groups/categories/' . $category->getId();

        $crawler = $client->request('GET', $url);
        $client->submit($crawler->filter('form[action$="/groups"]')->form(['group' => (string) $group->getId()]));
        self::assertResponseRedirects($url . '#groups');
        self::assertTrue($this->findGroup('Hiking Enthusiasts')->hasCategory($this->findCategory('Outdoors')));
        self::assertSame(1, $this->countLogs('%added to category Outdoors by member-3'));

        $crawler = $client->request('GET', $url);
        self::assertStringContainsString('Hiking Enthusiasts', $crawler->filter('.p-gadmin__groups')->text());
        $client->submit($crawler->filter('form[action$="/groups/' . $group->getId() . '/remove"]')->form());
        self::assertFalse($this->findGroup('Hiking Enthusiasts')->hasCategory($this->findCategory('Outdoors')));
        self::assertSame(1, $this->countLogs('%removed from category Outdoors by member-3'));
    }

    public function testAGroupWithThreeCategoriesTakesNoFourth(): void
    {
        $client = $this->groupAdmin();
        $entityManager = static::getContainer()->get(EntityManagerInterface::class);
        $group = $this->findGroup('Hiking Enthusiasts');
        foreach (['One', 'Two', 'Three'] as $name) {
            $group->addCategory($this->createCategory(['en' => $name]));
        }
        $entityManager->flush();
        $fourth = $this->createCategory(['en' => 'Four']);
        $url = '/admin/groups/categories/' . $fourth->getId();

        // offered greyed in the field, refused when posted all the same
        $crawler = $client->request('GET', $url);
        self::assertCount(1, $crawler->filter('select[name="group"] option[value="' . $group->getId() . '"][disabled]'));
        $client->request('POST', $url . '/groups', ['group' => $group->getId(), '_token' => $crawler->filter('form[action$="/groups"] input[name="_token"]')->attr('value')]);

        self::assertCount(3, $this->findGroup('Hiking Enthusiasts')->getCategories());
    }

    public function testMembersWithoutTheGroupRightChangeNoCategory(): void
    {
        $client = static::createClient();
        $category = $this->createCategory(['en' => 'Outdoors']);
        $client->loginUser($this->getMember('member-2'));

        foreach (['/icon', '/keywords', '/groups'] as $path) {
            $client->request('POST', '/admin/groups/categories/' . $category->getId() . $path, ['icon' => 'book']);
            self::assertResponseStatusCodeSame(403, $path);
        }
        $client->request('GET', '/admin/groups/categories/' . $category->getId());
        self::assertResponseStatusCodeSame(403);
    }

    private function groupAdmin(): KernelBrowser
    {
        $client = static::createClient();
        $client->loginUser($this->getMember('member-3'));

        return $client;
    }

    /**
     * @param array<string, string> $names
     */
    private function createCategory(array $names): GroupCategory
    {
        $category = new GroupCategory();
        foreach ($names as $locale => $name) {
            $category->setName($locale, $name);
        }
        $entityManager = static::getContainer()->get(EntityManagerInterface::class);
        $entityManager->persist($category);
        $entityManager->flush();

        return $category;
    }

    private function findCategory(string $englishName): GroupCategory
    {
        $entityManager = static::getContainer()->get(EntityManagerInterface::class);
        $entityManager->clear();
        foreach ($entityManager->getRepository(GroupCategory::class)->findAll() as $category) {
            if ($category->getName('en') === $englishName) {
                return $category;
            }
        }
        self::fail("No group category named {$englishName}.");
    }

    private function getMember(string $username): Member
    {
        $member = static::getContainer()->get(EntityManagerInterface::class)
            ->getRepository(Member::class)->findOneBy(['username' => $username]);
        self::assertInstanceOf(Member::class, $member);

        return $member;
    }

    private function findGroup(string $name): \App\Entity\Group
    {
        $entityManager = static::getContainer()->get(EntityManagerInterface::class);
        $entityManager->clear();
        $group = $entityManager->getRepository(\App\Entity\Group::class)->findOneBy(['name' => $name]);
        self::assertInstanceOf(\App\Entity\Group::class, $group);

        return $group;
    }

    private function countLogs(string $like): int
    {
        return (int) static::getContainer()->get(EntityManagerInterface::class)->getConnection()->fetchOne(
            "SELECT COUNT(*) FROM logs WHERE Type = 'Group' AND Str LIKE ?",
            [$like],
        );
    }
}
