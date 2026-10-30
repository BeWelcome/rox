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
        self::assertSelectorTextContains('table', 'Outdoors');
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
        $client->submit($crawler->filter('form[action$="/delete"]')->form());

        self::assertResponseRedirects('/admin/groups/categories');
        $entityManager = static::getContainer()->get(EntityManagerInterface::class);
        $entityManager->clear();
        self::assertNull($entityManager->find(GroupCategory::class, $id));
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
}
