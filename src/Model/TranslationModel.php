<?php

namespace App\Model;

use App\Entity\Word;
use App\Pagerfanta\ArchivedTranslationAdapter;
use App\Pagerfanta\DoNotTranslateTranslationAdapter;
use App\Pagerfanta\MissingTranslationAdapter;
use App\Pagerfanta\TranslationAdapter;
use App\Pagerfanta\UpdateTranslationAdapter;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpKernel\CacheWarmer\WarmableInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class TranslationModel
{
    public function __construct(private readonly TranslatorInterface $translator, private readonly EntityManagerInterface $entityManager, private readonly Filesystem $filesystem, private readonly string $cacheDirectory, private readonly array $locales)
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

    public function getAdapter($type, $locale, $code)
    {
        $translationAdapter = null;
        $connection = $this->entityManager->getConnection();

        $translationAdapter = match ($type) {
            'missing' => new MissingTranslationAdapter($connection, $locale, $code),
            'update' => new UpdateTranslationAdapter($connection, $locale),
            'all' => new TranslationAdapter($connection, $locale, $code),
            'archived' => new ArchivedTranslationAdapter($connection),
            'donottranslate' => new DoNotTranslateTranslationAdapter($connection),
            default => $translationAdapter,
        };

        return $translationAdapter;
    }

    public function updateDomainOfTranslations(Word $updatedTranslation)
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
    }
}
