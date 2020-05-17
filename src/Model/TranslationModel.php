<?php

namespace App\Model;

use App\Kernel;
use App\Pagerfanta\ArchivedTranslationAdapter;
use App\Pagerfanta\DoNotTranslateTranslationAdapter;
use App\Pagerfanta\MissingTranslationAdapter;
use App\Pagerfanta\TranslationAdapter;
use App\Pagerfanta\UpdateTranslationAdapter;
use App\Utilities\ManagerTrait;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mailer\MailerInterface;

class TranslationModel
{
    use ManagerTrait;

    /** @var Kernel */
    private $kernel;

    /**
     * @required
     *
     * @param KernelInterface $kernel
     */
    public function setKernel(KernelInterface $kernel)
    {
        $this->kernel = $kernel;
    }

    /**
     * Remove the cache file corresponding to the given locale.
     *
     * @return bool
     */
    public function removeCacheFiles()
    {
        $kernelCacheDir = $this->kernel->getCacheDir();
        $finder = new Finder();
        $finder->files()->in($kernelCacheDir . '/translations')->name('/.*/');
        $deleted = true;
        foreach ($finder as $file) {
            $path = $file->getRealPath();
            $deleted = unlink($path);
        }

        $finder->files()->in($kernelCacheDir)->name('');

        return $deleted;
    }

    public function getAdapter($type, $locale, $code)
    {
        $translationAdapter = null;
        $connection = $this->getManager()->getConnection();

        switch($type)
        {
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
}
