<?php

namespace App\Service;

use App\Entity\FaqCategory;
use App\Entity\Newsletter;
use App\Entity\Word;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Yaml\Yaml;
use Symfony\Contracts\Cache\CacheInterface;
use XMLWriter;

/**
 * The sitemap: the public pages of config/sitemap.yaml, the FAQ categories and the published newsletters in each of
 * their languages, all at their canonical address. The list of addresses is cached until a FAQ category or a
 * newsletter changes (SitemapCacheListener, MassmailModel); the XML is built from it with the host of the request.
 */
class Sitemap
{
    private const string CACHE_KEY = 'sitemap_entries';
    private const string NEWSLETTER_BODY = 'broadcast_body_';

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly UrlGeneratorInterface $urlGenerator,
        private readonly CacheInterface $cache,
        #[Autowire('%kernel.project_dir%/config/sitemap.yaml')]
        private readonly string $configFile,
    ) {
    }

    /**
     * @param string $baseUrl scheme and host, e.g. https://bewelcome.org
     */
    public function getXml(string $baseUrl): string
    {
        $xml = new XMLWriter();
        $xml->openMemory();
        $xml->setIndent(true);
        $xml->setIndentString('    ');
        $xml->startDocument('1.0', 'UTF-8');
        $xml->startElement('urlset');
        $xml->writeAttribute('xmlns', 'http://www.sitemaps.org/schemas/sitemap/0.9');
        foreach ($this->getEntries() as $entry) {
            $xml->startElement('url');
            $xml->writeElement('loc', rtrim($baseUrl, '/') . $entry['path']);
            if (null !== $entry['lastmod']) {
                $xml->writeElement('lastmod', $entry['lastmod']);
            }
            $xml->writeElement('priority', \sprintf('%.1F', $entry['priority']));
            $xml->endElement();
        }
        $xml->endElement();
        $xml->endDocument();

        return $xml->outputMemory();
    }

    /**
     * @return list<array{path: string, priority: float, lastmod: ?string}>
     */
    public function getEntries(): array
    {
        return $this->cache->get(self::CACHE_KEY, fn (): array => $this->collectEntries());
    }

    /**
     * The next sitemap is built from the database again.
     */
    public function invalidate(): void
    {
        $this->cache->delete(self::CACHE_KEY);
    }

    /**
     * Whether a change of this entity changes the sitemap: FAQ categories, newsletters and the newsletters' texts (a
     * new translation is a new address).
     */
    public static function isAbout(object $entity): bool
    {
        return $entity instanceof FaqCategory
            || $entity instanceof Newsletter
            || ($entity instanceof Word && str_starts_with(strtolower($entity->getCode()), self::NEWSLETTER_BODY));
    }

    /**
     * @return list<array{path: string, priority: float, lastmod: ?string}>
     */
    private function collectEntries(): array
    {
        $config = Yaml::parseFile($this->configFile)['sitemap'];
        $entries = [];
        foreach ($config['static_routes'] as $route) {
            $entries[] = ['path' => $route['path'], 'priority' => (float) $route['priority'], 'lastmod' => null];
        }

        // the router leaves out the default category, so the first one is /faq, as the pages call it
        $faqPriority = (float) $config['dynamic_routes']['faq_categories']['priority'];
        $categories = $this->entityManager->getRepository(FaqCategory::class)->findBy([], ['sortOrder' => 'ASC', 'id' => 'ASC']);
        foreach ($categories as $category) {
            $entries[] = [
                'path' => $this->urlGenerator->generate('faqs_overview', ['category' => $category->getId()]),
                'priority' => $faqPriority,
                'lastmod' => null,
            ];
        }

        $newsletterPriority = (float) $config['dynamic_routes']['newsletters']['priority'];
        $words = $this->entityManager->getRepository(Word::class);
        foreach ($this->entityManager->getRepository(Newsletter::class)->findAllPublished() as $newsletter) {
            $translations = $words->findBy(['code' => strtolower(self::NEWSLETTER_BODY . $newsletter->getName())]);
            foreach ($translations as $translation) {
                $entries[] = [
                    'path' => $this->urlGenerator->generate('newsletter_single', [
                        'id' => $newsletter->getId(),
                        'language' => $translation->getLanguage()->getShortCode(),
                    ]),
                    'priority' => $newsletterPriority,
                    'lastmod' => $newsletter->getCreated()->format('Y-m-d'),
                ];
            }
        }

        return $entries;
    }
}
