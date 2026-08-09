<?php

namespace App\Command;

use App\Entity\FaqCategory;
use App\Entity\Newsletter;
use App\Entity\Word;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Yaml\Yaml;
use XMLWriter;

#[AsCommand(
    name: 'sitemap:generate',
    description: 'Generates sitemap.xml with all publicly accessible URLs',
    hidden: false,
)]
class GenerateSitemapCommand extends Command
{
    private array $sitemapConfig;

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly RouterInterface $router,
        private readonly string $projectDir,
    ) {
        parent::__construct();
        $this->sitemapConfig = $this->loadSitemapConfig();
    }

    protected function configure(): void
    {
        $this
            ->addOption('base-url', 'b', InputOption::VALUE_OPTIONAL, 'Base URL for the sitemap (scheme + host)', null)
            ->setHelp('This command generates a sitemap.xml file with all publicly accessible URLs including newsletters and FAQ categories.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln([
            'Generating Sitemap',
            '==================',
            '',
        ]);

        // Get base URL
        $baseUrl = $input->getOption('base-url');
        if (!$baseUrl) {
            $context = $this->router->getContext();
            $scheme = $context->getScheme();
            $host = $context->getHost();
            $port = 'https' === $scheme ? $context->getHttpsPort() : $context->getHttpPort();
            $baseUrl = $scheme . '://' . $host;
            if (80 !== $port && 443 !== $port) {
                $baseUrl .= ':' . $port;
            }
        }
        // Remove index.php or similar from base URL if present
        $baseUrl = preg_replace('#/index\.php$#', '', $baseUrl);

        $output->writeln("Base URL: {$baseUrl}");

        // Collect all URLs
        $urls = $this->collectUrls($output);

        // Generate XML
        $xml = $this->generateSitemapXml($urls, $baseUrl);

        // Write to file
        $sitemapPath = $this->projectDir . '/public/sitemap.xml';
        file_put_contents($sitemapPath, $xml);

        $output->writeln('');
        $output->writeln('Sitemap generated with ' . \count($urls) . ' URLs');
        $output->writeln("Written to: {$sitemapPath}");

        return Command::SUCCESS;
    }

    /**
     * Load sitemap configuration from YAML file.
     */
    private function loadSitemapConfig(): array
    {
        $configFile = $this->projectDir . '/config/sitemap.yaml';
        if (!file_exists($configFile)) {
            return ['sitemap' => ['static_routes' => [], 'dynamic_routes' => []]];
        }

        $yaml = Yaml::parseFile($configFile);

        return $yaml['sitemap'] ?? [];
    }

    /**
     * Collect all URLs from static routes and dynamic content.
     */
    private function collectUrls(OutputInterface $output): array
    {
        $urls = [];
        $now = new DateTime();

        // Add static public routes from config
        $staticRoutes = $this->sitemapConfig['static_routes'] ?? [];
        foreach ($staticRoutes as $route) {
            $urls[] = [
                'loc' => $route['path'],
                'lastmod' => $now,
                'priority' => $route['priority'],
            ];
        }

        $output->writeln('Static routes: ' . \count($staticRoutes));

        // Add FAQ category URLs (dynamic)
        $faqPriority = $this->sitemapConfig['dynamic_routes']['faq_categories']['priority'] ?? 0.6;
        $faqUrls = $this->getFaqCategoryUrls($faqPriority);
        $urls = array_merge($urls, $faqUrls);
        $output->writeln('FAQ categories: ' . \count($faqUrls));

        // Add Newsletter URLs (dynamic with language variants)
        $newsletterPriority = $this->sitemapConfig['dynamic_routes']['newsletters']['priority'] ?? 0.6;
        $newsletterUrls = $this->getNewsletterUrls($newsletterPriority);
        $urls = array_merge($urls, $newsletterUrls);
        $output->writeln('Newsletter URLs: ' . \count($newsletterUrls));

        return $urls;
    }

    /**
     * Get all FAQ category URLs.
     */
    private function getFaqCategoryUrls(float $priority): array
    {
        $urls = [];
        $faqCategories = $this->entityManager->getRepository(FaqCategory::class)
            ->findBy([], ['sortOrder' => 'ASC']);

        foreach ($faqCategories as $category) {
            $urls[] = [
                'loc' => '/faq/' . $category->getId(),
                'lastmod' => new DateTime(),
                'priority' => $priority,
            ];
        }

        return $urls;
    }

    /**
     * Get all newsletter URLs with their available language translations.
     */
    private function getNewsletterUrls(float $priority): array
    {
        $urls = [];
        $newsletterRepository = $this->entityManager->getRepository(Newsletter::class);
        $translationRepository = $this->entityManager->getRepository(Word::class);

        $newsletters = $newsletterRepository->findAllPublished();

        foreach ($newsletters as $newsletter) {
            $id = $newsletter->getId();
            $name = $newsletter->getName();
            $created = $newsletter->getCreated();

            // Find all languages this newsletter is translated into
            $translations = $translationRepository->findBy([
                'code' => strtolower('Broadcast_body_' . $name),
            ]);

            foreach ($translations as $translation) {
                $languageCode = $translation->getLanguage()->getShortCode();
                $urls[] = [
                    'loc' => "/newsletters/{$id}/{$languageCode}",
                    'lastmod' => $created instanceof \Carbon\Carbon ? $created->toDateTime() : $created,
                    'priority' => $priority,
                ];
            }
        }

        return $urls;
    }

    /**
     * Generate the sitemap XML content.
     */
    private function generateSitemapXml(array $urls, string $baseUrl): string
    {
        $xml = new XMLWriter();
        $xml->openMemory();
        $xml->startDocument('1.0', 'UTF-8');
        $xml->setIndent(true);
        $xml->setIndentString('    ');

        $xml->startElement('urlset');
        $xml->writeAttribute('xmlns', 'http://www.sitemaps.org/schemas/sitemap/0.9');

        foreach ($urls as $urlData) {
            $xml->startElement('url');

            // Location
            $xml->startElement('loc');
            $fullUrl = rtrim($baseUrl, '/') . $urlData['loc'];
            $xml->text($fullUrl);
            $xml->endElement(); // loc

            // Last modified
            $xml->startElement('lastmod');
            $lastmod = $urlData['lastmod'];
            if ($lastmod instanceof \Carbon\Carbon) {
                $lastmod = $lastmod->toDateTime();
            }
            $xml->text($lastmod->format(DateTime::W3C));
            $xml->endElement(); // lastmod

            // Priority
            $xml->startElement('priority');
            $xml->text((string) $urlData['priority']);
            $xml->endElement(); // priority

            $xml->endElement(); // url
        }

        $xml->endElement(); // urlset
        $xml->endDocument();

        return $xml->outputMemory(true);
    }
}
