<?php

namespace App\Model;

use App\Doctrine\DomainType;
use App\Entity\Word;
use App\Repository\WordRepository;
use App\TranslationLoader\DatabaseLoader;
use App\Utilities\MemoryLimit;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpKernel\CacheWarmer\WarmableInterface;
use Symfony\Component\Lock\LockFactory;
use Symfony\Contracts\Translation\TranslatorInterface;

class TranslationModel
{
    public function __construct(
        private readonly TranslatorInterface $translator,
        private readonly EntityManagerInterface $entityManager,
        private readonly Filesystem $filesystem,
        private readonly string $cacheDirectory,
        private readonly array $locales,
        private readonly LockFactory $lockFactory,
        #[Autowire('%translator.default_path%')]
        private readonly string $translationsDirectory,
    ) {
    }

    /**
     * Updates one text in the cached catalogues instead of building them again from the database: in the
     * catalogues of the locale, or of every language for an English text (each has the English texts as its
     * fallback, and shows the English text instead of a translation older than its last major change).
     * The texts of the code are set as DatabaseLoader would load them, and removed from the domain they aren't in
     * (the domain may have changed). Catalogues not cached yet are built when they're used, as always.
     */
    public function updateCachedText(string $code, string $locale): void
    {
        /** @var WordRepository $words */
        $words = $this->entityManager->getRepository(Word::class);
        $texts = $words->getCatalogueTexts($code);
        $english = $texts['en'] ?? null;

        $lock = $this->lockFactory->createLock('translation-catalogues');
        $lock->acquire(true);
        try {
            foreach ($this->getCachedCatalogues() as $file) {
                // Changed as text, not included: PHP keeps what it compiles from a file until the request ends, about
                // 2 MB per catalogue, more than the memory limit for the catalogues of every language.
                $content = file_get_contents($file);
                if (false === $content) {
                    continue;
                }
                $catalogues = $this->catalogueBodies($content);
                if ('en' !== $locale && !\in_array($locale, array_column($catalogues, 'locale'), true)) {
                    continue;
                }
                $changed = $this->withText($content, $catalogues, $code, $english, $texts);
                if (null === $changed) {
                    // not written as expected: the translator builds it again when it's used
                    $this->filesystem->remove($file);
                } elseif ($changed !== $content) {
                    $this->filesystem->dumpFile($file, $changed);
                }
                if (\function_exists('opcache_invalidate')) {
                    opcache_invalidate($file, true);
                }
            }
        } finally {
            $lock->release();
        }
    }

    /**
     * Remove the cache file corresponding to the given locale.
     */
    public function refreshTranslationsCacheForLocale(string $locale): void
    {
        $this->removeAndWarmupCache($locale);
    }

    /**
     * Remove the translation cache files.
     */
    public function refreshTranslationsCache(): void
    {
        foreach ($this->locales as $locale) {
            $this->removeAndWarmupCache($locale);
        }
    }

    public function updateDomainOfTranslations(Word $updatedTranslation): void
    {
        $translationRepository = $this->entityManager->getRepository(Word::class);
        $translations = $translationRepository->findBy(['code' => $updatedTranslation->getCode()]);

        foreach ($translations as $translation) {
            $translation->setDomain($updatedTranslation->getDomain());
            $this->entityManager->persist($translation);
        }
        $this->entityManager->flush();
    }

    /**
     * @return list<string> the cached catalogues (Translator writes one per locale)
     */
    private function getCachedCatalogues(): array
    {
        $files = glob(\sprintf('%s/translations/catalogue.*.php', $this->cacheDirectory));

        return false === $files ? [] : $files;
    }

    /**
     * The catalogues of a cached file (Translator::dumpCatalogue(): the locale's, then its fallbacks'), each with the
     * text of its messages: from after "new MessageCatalogue('de', array (" to before the "));" that ends it.
     *
     * @return list<array{locale: string, offset: int, length: int}>
     */
    private function catalogueBodies(string $content): array
    {
        preg_match_all(
            '/^\$catalogue\w* = new MessageCatalogue\(\'([^\'\\\\]+)\', array \(\n/m',
            $content,
            $matches,
            \PREG_SET_ORDER | \PREG_OFFSET_CAPTURE,
        );
        $catalogues = [];
        foreach ($matches as $match) {
            $offset = $match[0][1] + \strlen($match[0][0]);
            $end = strpos($content, "));\n", $offset);
            while (false !== $end && 0 !== $end - $offset && "\n" !== $content[$end - 1]) {
                $end = strpos($content, "));\n", $end + 1);
            }
            if (false !== $end) {
                $catalogues[] = ['locale' => $match[1][0], 'offset' => $offset, 'length' => $end - $offset];
            }
        }

        return $catalogues;
    }

    /**
     * The file with the text of the code as each of its catalogues shows it (DatabaseLoader), in its domain only;
     * null when a catalogue isn't written as var_export() writes it.
     *
     * @param list<array{locale: string, offset: int, length: int}> $catalogues
     * @param array<string, Word>                                   $texts
     */
    private function withText(string $content, array $catalogues, string $code, ?Word $english, array $texts): ?string
    {
        // from the last, so the offsets of the others stay valid
        foreach (array_reverse($catalogues) as $catalogue) {
            $locale = $catalogue['locale'];
            $text = null === $english ? null : $this->shownText($locale, $code, $english, $texts[$locale] ?? null);
            $body = $this->withTextInBody(
                substr($content, $catalogue['offset'], $catalogue['length']),
                $code,
                null === $text ? null : [$english->getDomain(), $text],
            );
            if (null === $body) {
                return null;
            }
            $content = substr_replace($content, $body, $catalogue['offset'], $catalogue['length']);
        }

        return $content;
    }

    /**
     * The messages of a catalogue (var_export(): a list of "'domain' => array (...)") without the code in any domain,
     * then with it in the given domain; null when they aren't written as expected.
     *
     * @param array{string, string}|null $domainText the domain and the text to set
     */
    private function withTextInBody(string $body, string $code, ?array $domainText): ?string
    {
        $string = "'(?:[^'\\\\]++|\\\\.)*+'";
        // var_export() writes keys that are numbers without quotes, as the array keeps them
        $key = var_export(array_key_first([$code => true]), true);
        preg_match_all(
            "/  ({$string}) => \\n  array \\(\\n((?:    (?:{$string}|-?\\d+) => {$string},\\n)*+)  \\),\\n/",
            $body,
            $domains,
            \PREG_SET_ORDER,
        );
        if (implode('', array_column($domains, 0)) !== $body) {
            return null;
        }

        $changed = '';
        $added = null === $domainText;
        $cleaned = [DomainType::MESSAGES, DomainType::ICU_MESSAGES, DomainType::VALIDATORS, $domainText[0] ?? null];
        $line = static fn (string $text): string => "    {$key} => " . var_export($text, true) . ",\n";
        foreach ($domains as [, $quotedDomain, $entries]) {
            // var_export() escapes only \ and ' in strings
            $domain = strtr(substr($quotedDomain, 1, -1), ['\\\\' => '\\', "\\'" => "'"]);
            if (\in_array($domain, $cleaned, true)) {
                preg_match_all("/    (?:{$string}|-?\\d+) => {$string},\\n/", $entries, $lines);
                $others = array_filter(
                    $lines[0],
                    static fn (string $entry): bool => !str_starts_with($entry, "    {$key} => "),
                );
                $entries = implode('', $others);
                if (!$added && $domain === $domainText[0]) {
                    $entries .= $line($domainText[1]);
                    $added = true;
                }
            }
            $changed .= "  {$quotedDomain} => \n  array (\n{$entries}  ),\n";
        }
        if (!$added) {
            $quotedDomain = var_export($domainText[0], true);
            $changed .= "  {$quotedDomain} => \n  array (\n" . $line($domainText[1]) . "  ),\n";
        }

        return $changed;
    }

    /**
     * The text of the code as the language shows it (DatabaseLoader): the English text, a translation in the same
     * domain, or the English one where the translation is older than its last major change; null for none.
     */
    private function shownText(string $locale, string $code, Word $english, ?Word $translation): ?string
    {
        $text = match (true) {
            'en' === $locale => $english->getSentence(),
            null === $translation || $translation->getDomain() !== $english->getDomain() => null,
            default => DatabaseLoader::shownText(
                $code,
                $english->getSentence(),
                $english->getMajorUpdate(),
                $english->getTranslationAllowed(),
                $translation->getSentence(),
                $translation->getUpdated(),
            ),
        };
        // the database is a resource of the domains that have a file for the locale (translations/messages.de.db)
        $resource = \sprintf('%s/%s.%s.db', $this->translationsDirectory, $english->getDomain(), $locale);

        return null !== $text && is_file($resource) ? $text : null;
    }

    private function removeAndWarmupCache(string $locale): void
    {
        $translationDir = \sprintf('%s/translations', $this->cacheDirectory);

        $finder = new Finder();

        // Make sure the directory exists
        $this->filesystem->mkdir($translationDir);

        // Remove the translations for this locale or all if locale is null
        $files = $finder->files()->name('*.' . $locale . '.*')->in($translationDir);
        foreach ($files as $file) {
            $this->filesystem->remove($file);
        }

        // Build them again; that needs up to 1 GB
        if ($this->translator instanceof WarmableInterface) {
            $translator = $this->translator;
            MemoryLimit::atLeast('1G', static fn () => $translator->warmUp($translationDir));
        }
    }
}
