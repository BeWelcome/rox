<?php

namespace App\Model;

use App\Entity\Word;
use App\Kernel;
use App\Pagerfanta\ArchivedTranslationAdapter;
use App\Pagerfanta\DoNotTranslateTranslationAdapter;
use App\Pagerfanta\MissingTranslationAdapter;
use App\Pagerfanta\TranslationAdapter;
use App\Pagerfanta\UpdateTranslationAdapter;
use App\Utilities\ManagerTrait;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpKernel\CacheWarmer\WarmableInterface;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class TranslationModel
{
    use ManagerTrait;

    /** @var KernelInterface */
    private $kernel;

    /** @var TranslatorInterface */
    private $translator;

    /** @var Filesystem */
    private $filesystem;

    /**
     * @required
     * @param KernelInterface $kernel
     * @param TranslatorInterface $translator
     * @param Filesystem $filesystem
     */
    public function setKernel(KernelInterface $kernel, TranslatorInterface $translator, Filesystem $filesystem)
    {
        $this->kernel = $kernel;
        $this->translator = $translator;
        $this->filesystem = $filesystem;
    }

    /**
     * Remove the cache file corresponding to the given locale.
     * @param string|null $locale
     */
    public function removeCacheFiles(?string $locale = null): void
    {
        $translationDir = \sprintf('%s/translations', $this->kernel->getCacheDir());

        $finder = new Finder();

        // Make sure the directory exists
        $this->filesystem->mkdir($translationDir);

        // Remove the translations for this locale
        $files = $finder->files()->name($locale ? '*.'.$locale.'.*' : '*')->in($translationDir);
        foreach ($files as $file) {
            $this->filesystem->remove($file);
        }

        // Build them again
        if ($this->translator instanceof WarmableInterface) {
            $this->translator->warmUp($translationDir);
        }
    }

    public function getAdapter($type, $locale, $code)
    {
        $translationAdapter = null;
        $connection = $this->getManager()->getConnection();

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
                $translationAdapter = new ArchivedTranslationAdapter($connection, $locale);
                break;
            case 'donottranslate':
                $translationAdapter = new DoNotTranslateTranslationAdapter($connection, $locale);
                break;
        }

        return $translationAdapter;
    }

    public function updateDomainOfTranslations(Word $updatedTranslation)
    {
        $em = $this->getManager();
        $translationRepository = $em->getRepository(Word::class);
        $translations = $translationRepository->findBy(['code' => $updatedTranslation->getCode()]);

        foreach ($translations as $translation) {
            $translation->setDomain($updatedTranslation->getDomain());
            $em->persist($translation);
        }
        $em->flush();
    }
}
