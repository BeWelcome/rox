<?php

namespace App\Processor\Fixtures;

/**
 * Generates a simple solid-color JPEG placeholder for fixture-created gallery/group images, since the
 * fixtures only insert database rows - the application still expects a real file to exist on disk at
 * the path those rows point to (member galleries, group pictures).
 */
final class PlaceholderImage
{
    public static function create(string $path, int $width, int $height): void
    {
        $directory = \dirname($path);
        if (!is_dir($directory)) {
            mkdir($directory, 0o775, true);
        }

        $image = imagecreatetruecolor($width, $height);
        $color = imagecolorallocate($image, random_int(60, 220), random_int(60, 220), random_int(60, 220));
        imagefill($image, 0, 0, $color);
        imagejpeg($image, $path, 85);
        imagedestroy($image);
    }
}
