<?php

namespace App\Model;

use App\Kernel;
use Symfony\Component\Finder\Finder;

class TranslationModel
{
    /**
     * Remove the cache file corresponding to the given locale.
     *
     * @param Kernel $kernel
     * @param string $locale
     *
     * @return bool
     */
    public function removeCacheFile(Kernel $kernel, $locale)
    {
        $locale;
        $kernelCacheDir = $kernel->getCacheDir();
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
}
