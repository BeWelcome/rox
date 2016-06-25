<?php

namespace Rox\Core\Extension;

use Twig_Extension;
use Twig_SimpleFunction;

class AssetExtension extends Twig_Extension
{
    const MANIFEST = 'cache/assets_versioning.json';

    public function getFunctions()
    {
        return [
            new Twig_SimpleFunction('asset', [$this, 'getAssetUrl']),
        ];
    }

    /**
     * @todo provide a simple way to use the non-versioned assets in dev env.
     *
     * @param string $path
     *
     * @return string
     */
    public function getAssetUrl($path)
    {
        $path = 'assets/'.$path;

        // If the manifest file doesn't exist, then return the unversioned file.
        if (!is_readable(self::MANIFEST)) {
            return $path;
        }

        $data = file_get_contents(self::MANIFEST);

        $manifest = json_decode($data, JSON_OBJECT_AS_ARRAY);

        $assets = array_column($manifest, 'versionedPath', 'originalPath');

        return $assets[$path];
    }

    public function getName()
    {
        return 'asset';
    }
}
