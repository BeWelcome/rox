<?php

namespace App\Model;

use App\Doctrine\DomainType;
use App\Entity\Word;
use App\Repository\WordRepository;
use App\TranslationLoader\DatabaseLoader;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpKernel\CacheWarmer\WarmableInterface;
use Symfony\Component\Lock\LockFactory;
use Symfony\Component\Translation\MessageCatalogue;
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
                $chain = $this->readCachedCatalogue($file);
                $locales = array_column($chain, 'locale');
                if ('en' !== $locale && !\in_array($locale, $locales, true)) {
                    continue;
                }
                foreach ($chain as $index => $catalogue) {
                    $chain[$index]['messages'] = $this->withText($catalogue['messages'], $catalogue['locale'], $code, $english, $texts[$catalogue['locale']] ?? null);
                }
                $this->writeCachedCatalogue($file, $chain);
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
     * The catalogues of a cached file: the locale's and its fallbacks', with their messages by domain.
     *
     * @return list<array{locale: string, messages: array<string, array<string, string>>}>
     */
    private function readCachedCatalogue(string $file): array
    {
        $chain = [];
        /** @var MessageCatalogue|null $catalogue */
        $catalogue = (static fn (string $file) => include $file)($file);
        while ($catalogue instanceof MessageCatalogue) {
            $chain[] = ['locale' => $catalogue->getLocale(), 'messages' => $this->getAllMessages($catalogue)];
            $catalogue = $catalogue->getFallbackCatalogue();
        }

        return $chain;
    }

    /**
     * Writes the catalogues as Translator::dumpCatalogue() does.
     *
     * @param list<array{locale: string, messages: array<string, array<string, string>>}> $chain
     */
    private function writeCachedCatalogue(string $file, array $chain): void
    {
        $variable = static fn (int $index, string $locale): string => 0 === $index
            ? '$catalogue' : '$catalogue' . ucfirst((string) preg_replace('/[^a-z0-9_]/i', '_', $locale));

        $content = "<?php\n\nuse Symfony\\Component\\Translation\\MessageCatalogue;\n\n";
        foreach ($chain as $index => $catalogue) {
            $content .= \sprintf("%s = new MessageCatalogue('%s', %s);\n", $variable($index, $catalogue['locale']), $catalogue['locale'], var_export($catalogue['messages'], true));
            if ($index > 0) {
                $content .= \sprintf("%s->addFallbackCatalogue(%s);\n", $variable($index - 1, $chain[$index - 1]['locale']), $variable($index, $catalogue['locale']));
            }
            $content .= "\n";
        }
        $content .= "return \$catalogue;\n";

        $this->filesystem->dumpFile($file, $content);
        if (\function_exists('opcache_invalidate')) {
            opcache_invalidate($file, true);
        }
    }

    /**
     * The messages with the text of the code as the language shows it (DatabaseLoader), in its domain only.
     *
     * @param array<string, array<string, string>> $messages
     *
     * @return array<string, array<string, string>>
     */
    private function withText(array $messages, string $locale, string $code, ?Word $english, ?Word $translation): array
    {
        foreach ([DomainType::MESSAGES, DomainType::ICU_MESSAGES, DomainType::VALIDATORS] as $domain) {
            unset($messages[$domain][$code]);
        }
        if (null === $english) {
            return $messages;
        }

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
        if (null !== $text && is_file($resource)) {
            $messages[$english->getDomain()][$code] = $text;
        }

        return $messages;
    }

    /**
     * The messages by domain, the ICU ones in their own domain (as Translator::getAllMessages()).
     *
     * @return array<string, array<string, string>>
     */
    private function getAllMessages(MessageCatalogue $catalogue): array
    {
        $allMessages = [];
        foreach ($catalogue->all() as $domain => $messages) {
            if ($intlMessages = $catalogue->all($domain . MessageCatalogue::INTL_DOMAIN_SUFFIX)) {
                $allMessages[$domain . MessageCatalogue::INTL_DOMAIN_SUFFIX] = $intlMessages;
                $messages = array_diff_key($messages, $intlMessages);
            }
            if ($messages) {
                $allMessages[$domain] = $messages;
            }
        }

        return $allMessages;
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

        $memoryLimit = \ini_get('memory_limit');
        ini_set('memory_limit', '1G');

        // Build them again
        if ($this->translator instanceof WarmableInterface) {
            $this->translator->warmUp($translationDir);
        }

        ini_set('memory_limit', $memoryLimit);
    }
}
