<?php

namespace App\Model;

use App\Entity\Word;
use App\Pagerfanta\WordListAdapter;
use App\Repository\WordRepository;
use App\Service\NativeLanguageNames;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpKernel\CacheWarmer\WarmableInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class TranslationModel
{
    public function __construct(private readonly TranslatorInterface $translator, private readonly EntityManagerInterface $entityManager, private readonly Filesystem $filesystem, private readonly string $cacheDirectory, private readonly array $locales, private readonly NativeLanguageNames $nativeLanguageNames)
    {
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

    public function getAdapter(string $type, string $locale, ?string $code): ?WordListAdapter
    {
        /** @var WordRepository $words */
        $words = $this->entityManager->getRepository(Word::class);
        $code ??= '';

        $list = match ($type) {
            'missing' => $words->missingList($locale, $code),
            'update' => $words->updateList($locale),
            'all' => $words->allList($locale, $code),
            'archived' => $words->archivedList(),
            'donottranslate' => $words->doNotTranslateList(),
            default => null,
        };

        return null === $list ? null : new WordListAdapter($list);
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

        // Language names are cached separately from the catalogues; drop them so edits show up.
        $this->nativeLanguageNames->clear();
    }
}
