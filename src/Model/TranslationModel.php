<?php

namespace App\Model;

use App\Entity\Word;
use App\Pagerfanta\ArchivedTranslationAdapter;
use App\Pagerfanta\DoNotTranslateTranslationAdapter;
use App\Pagerfanta\MissingTranslationAdapter;
use App\Pagerfanta\TranslationAdapter;
use App\Pagerfanta\UpdateTranslationAdapter;
use App\Utilities\ManagerTrait;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpKernel\CacheWarmer\WarmableInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class TranslationModel
{
    private TranslatorInterface $translator;

    private Filesystem $filesystem;

    private string $cacheDirectory;

    private array $locales;
    private EntityManagerInterface $entityManager;

    public function __construct(
        TranslatorInterface $translator,
        EntityManagerInterface $entityManager,
        Filesystem $filesystem,
        string $cacheDirectory,
        array $locales
    ) {
        $this->translator = $translator;
        $this->cacheDirectory = $cacheDirectory;
        $this->filesystem = $filesystem;
        $this->locales = $locales;
        $this->entityManager = $entityManager;
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

        switch ($type) {
            case 'missing':
                $translationAdapter = new MissingTranslationAdapter($connection, $locale, $code);
                break;
            case 'update':
                $translationAdapter = new UpdateTranslationAdapter($connection, $locale);
                break;
            case 'all':
                $translationAdapter = new TranslationAdapter($connection, $locale, $code);
                break;
            case 'archived':
                $translationAdapter = new ArchivedTranslationAdapter($connection);
                break;
            case 'donottranslate':
                $translationAdapter = new DoNotTranslateTranslationAdapter($connection);
                break;
        }

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
        $translationDir = sprintf('%s/translations', $this->cacheDirectory);

        $finder = new Finder();

        // Make sure the directory exists
        $this->filesystem->mkdir($translationDir);

        // Remove the translations for this locale or all if locale is null
        $files = $finder->files()->name('*.' . $locale . '.*')->in($translationDir);
        foreach ($files as $file) {
            $this->filesystem->remove($file);
        }

        $memoryLimit = ini_get('memory_limit');
        ini_set('memory_limit', '1G');

        // Build them again
        if ($this->translator instanceof WarmableInterface) {
            $this->translator->warmUp($translationDir);
        }

        ini_set('memory_limit', $memoryLimit);
    }
}
