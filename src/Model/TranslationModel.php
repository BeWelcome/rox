<?php

namespace App\Model;

use App\Kernel;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mailer\MailerInterface;

class TranslationModel
{
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
        $finder->files()->in($kernelCacheDir.'/translations')->name('/.*/');
        $deleted = true;
        foreach ($finder as $file) {
            $path = $file->getRealPath();
            $deleted = unlink($path);
        }

        $finder->files()->in($kernelCacheDir)->name('');

        return $deleted;
    }
}
