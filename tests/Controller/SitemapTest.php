<?php

namespace App\Tests\Controller;

use App\Entity\FaqCategory;
use App\Entity\Member;
use App\Entity\Newsletter;
use App\Model\Admin\MassmailModel;
use App\Service\Sitemap;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * /sitemap.xml lists the public pages at their canonical address, the FAQ categories (the default one as /faq) and the
 * published newsletters in each of their languages; a new FAQ category or a newly published newsletter shows up at
 * once.
 */
#[Group('integration')]
class SitemapTest extends WebTestCase
{
    private const array STATIC_PAGES = [
        '/', '/signup', '/login', '/about', '/about/getactive', '/about/thepeople', '/about/credits',
        '/about/statistics', '/about/commentguidelines', '/press-information', '/feedback', '/safety',
        '/safety/basics', '/safety/whattodo', '/safety/team', '/safety/faq', '/safety/tips', '/volunteer', '/terms',
        '/privacy', '/impressum',
    ];

    private KernelBrowser $client;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        // the cache outlives the rolled back transaction of an earlier test
        static::getContainer()->get(Sitemap::class)->invalidate();
    }

    public function testAVisitorGetsThePublicPagesAndTheFaqCategories(): void
    {
        $paths = $this->sitemapPaths();

        $this->assertResponseHeaderSame('Content-Type', 'application/xml; charset=UTF-8');
        foreach (self::STATIC_PAGES as $page) {
            self::assertContains($page, $paths);
        }
        self::assertNotContains('/about/faq', $paths, 'a redirect is no page of its own');
        self::assertNotContains('/stats', $paths);

        $categories = $this->entityManager()->getRepository(FaqCategory::class)->findAll();
        self::assertNotEmpty($categories);
        foreach ($categories as $category) {
            self::assertContains(1 === $category->getId() ? '/faq' : '/faq/' . $category->getId(), $paths);
        }
        self::assertNotContains('/faq/1', $paths, '/faq is the default category');
        self::assertSame(\count($paths), \count(array_unique($paths)), 'each address once');
    }

    public function testANewFaqCategoryIsListedAtOnce(): void
    {
        $this->sitemapPaths();

        $category = new FaqCategory();
        $category->setDescription('zztest_sitemap_category');
        $category->setSortOrder(99);
        $category->setCreated(new DateTime());
        $this->entityManager()->persist($category);
        $this->entityManager()->flush();

        self::assertContains('/faq/' . $category->getId(), $this->sitemapPaths());
    }

    public function testANewsletterIsListedInItsLanguagesOnceItIsPublished(): void
    {
        $newsletter = $this->newsletter('zztest_sitemap_newsletter', ['en', 'de']);
        self::assertEmpty(preg_grep('#^/newsletters/' . $newsletter->getId() . '/#', $this->sitemapPaths()));

        static::getContainer()->get(MassmailModel::class)->trigger($newsletter);

        $paths = $this->sitemapPaths();
        self::assertContains('/newsletters/' . $newsletter->getId() . '/en', $paths);
        self::assertContains('/newsletters/' . $newsletter->getId() . '/de', $paths);
    }

    /**
     * @return list<string> the paths of the sitemap's addresses
     */
    private function sitemapPaths(): array
    {
        $this->client->request('GET', '/sitemap.xml');
        $this->assertResponseIsSuccessful();
        $xml = simplexml_load_string((string) $this->client->getResponse()->getContent());
        self::assertNotFalse($xml);

        $paths = [];
        foreach ($xml->url as $url) {
            self::assertStringStartsWith('http://localhost/', (string) $url->loc);
            $paths[] = substr((string) $url->loc, \strlen('http://localhost'));
        }

        return $paths;
    }

    /**
     * A mass mail of type newsletter, not published yet, with its text in the given languages.
     */
    private function newsletter(string $name, array $languages): Newsletter
    {
        $entityManager = $this->entityManager();
        $newsletter = new Newsletter();
        $newsletter->setName($name);
        $newsletter->setType(Newsletter::REGULAR_NEWSLETTER);
        $newsletter->setStatus('Created');
        $newsletter->setCreatedBy($entityManager->getRepository(Member::class)->findOneBy(['username' => 'bwadmin']));
        $newsletter->setCreated(new DateTime());
        $entityManager->persist($newsletter);
        $entityManager->flush();
        foreach ($languages as $language) {
            $entityManager->getConnection()->executeStatement(
                "INSERT INTO word (code, domain, Sentence, created, donottranslate, Description, TranslationPriority, ShortCode)
                 VALUES (?, 'messages', 'Text', NOW(), 'No', '', 0, ?)",
                ['broadcast_body_' . $name, $language],
            );
        }

        return $newsletter;
    }

    private function entityManager(): EntityManagerInterface
    {
        return static::getContainer()->get(EntityManagerInterface::class);
    }
}
