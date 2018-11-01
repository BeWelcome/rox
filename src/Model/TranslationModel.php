<?php

namespace App\Model;

use Symfony\Component\Finder\Finder;

class TranslationModel
{
    /**
     * Remove the cache file corresponding to the given locale.
     *
     * @param $kernelCacheDir
     * @param string $locale
     *
     * @return bool
     */
    public function removeCacheFile($kernelCacheDir, $locale)
    {
        $localeExploded = explode('_', $locale);
        $finder = new Finder();
        $finder->files()->in($kernelCacheDir)->name(sprintf('/catalogue\.%s.*\.php$/', $localeExploded[0]));
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
