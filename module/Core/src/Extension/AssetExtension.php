<?php

namespace Rox\Core\Extension;

use Twig_Extension;
use Twig_SimpleFunction;

class AssetExtension extends Twig_Extension
{
    public function getFunctions()
    {
        return [
            new Twig_SimpleFunction('asset', [$this, 'getAssetUrl']),
        ];
    }

    /**
     * @todo provide a simple way to use the non-versioned assets in dev env.
     * @param string $path
     * @return string
     */
    public function getAssetUrl($path)
    {
        $path = 'assets/' . $path;
        //$path = 'assets/build/' . $path;

        $data = file_get_contents('cache/assets_versioning.json');

        $manifest = json_decode($data, JSON_OBJECT_AS_ARRAY);

        $assets = array_column($manifest, 'versionedPath', 'originalPath');

        return $assets[$path];
    }

    public function getName()
    {
        return 'asset';
    }
}
