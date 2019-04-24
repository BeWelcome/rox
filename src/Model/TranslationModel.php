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
        $kernelCacheDir = $kernel->getCacheDir() . '/translations';
        $localeExploded = explode('_', $locale);
        $cacheFilename = sprintf('/catalogue\.%s.*\.php$/', $localeExploded[0]);
        $finder = new Finder();
        $finder->files()->in($kernelCacheDir)->name($cacheFilename);
        $deleted = true;
        foreach ($finder as $file) {
            $path = $file->getRealPath();
            $deleted = unlink($path);
            $metadata = $path.'.meta';
            if (file_exists($metadata)) {
                unlink($metadata);
            }
        }

        return $deleted;
    }
}
