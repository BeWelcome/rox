<?php

namespace App\Service;

use App\Entity\GalleryImage;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Throwable;

/**
 * The files of a gallery picture: the original and the smaller copies the pages show.
 *
 * The copies are made on the first request and kept next to the original, named with a prefix
 * (thumb, thumb1 to thumb3) as the old gallery did, so the copies made by it are used as well
 * and MemberGallery::delete() removes them with the picture.
 */
class GalleryImageFiles
{
    public const string THUMBNAIL = 'thumbnail';
    public const string SMALL = 'small';
    public const string MEDIUM = 'medium';
    public const string LARGE = 'large';

    /**
     * Prefix of the copy and its bounds in pixels; the thumbnail is cut to a square.
     */
    private const array SIZES = [
        self::THUMBNAIL => ['thumb', 100],
        self::SMALL => ['thumb1', 240],
        self::MEDIUM => ['thumb2', 500],
        self::LARGE => ['thumb3', 1600],
    ];

    public function __construct(
        #[Autowire('%gallery_directory%')]
        private readonly string $galleryDirectory,
    ) {
    }

    /**
     * @return list<string>
     */
    public static function sizes(): array
    {
        return array_keys(self::SIZES);
    }

    /**
     * Path of the original, null if it is missing.
     */
    public function original(GalleryImage $image): ?string
    {
        $file = $this->directory($image) . (string) $image->getFile();

        return $this->isUsable($image) && is_file($file) ? $file : null;
    }

    /**
     * Path of the copy in the size, made if it doesn't exist yet. A picture that is already small enough
     * (or can't be scaled) is served as it is. Null if the original is missing.
     */
    public function sized(GalleryImage $image, string $size): ?string
    {
        $original = $this->original($image);
        if (null === $original || !isset(self::SIZES[$size])) {
            return null;
        }

        [$prefix, $bound] = self::SIZES[$size];
        $copy = $this->directory($image) . $prefix . $image->getFile();
        if (is_file($copy) && filesize($copy) > 0) {
            return $copy;
        }

        return $this->createCopy($original, $copy, $bound, self::THUMBNAIL === $size) ? $copy : $original;
    }

    private function createCopy(string $original, string $copy, int $bound, bool $square): bool
    {
        $info = @getimagesize($original);
        if (false === $info || (!$square && $info[0] <= $bound && $info[1] <= $bound)) {
            return false;
        }

        try {
            $image = new ImageManager(new Driver())->read($original)->orient();
            if ($square) {
                $image->cover($bound, $bound);
            } else {
                $image->scaleDown($bound, $bound);
            }
            $encoded = \in_array($info['mime'], ['image/jpeg', 'image/webp'], true)
                ? $image->encodeByMediaType($info['mime'], quality: 85)
                : $image->encodeByMediaType($info['mime']);

            return false !== file_put_contents($copy, (string) $encoded);
        } catch (Throwable) {
            return false;
        }
    }

    private function directory(GalleryImage $image): string
    {
        return $this->galleryDirectory . '/member' . $image->getOwner()->getId() . '/';
    }

    /**
     * The file name comes from the database: only a plain name inside the member's directory.
     */
    private function isUsable(GalleryImage $image): bool
    {
        $file = (string) $image->getFile();

        return '' !== $file && !str_contains($file, '/') && !str_contains($file, '\\') && !str_starts_with($file, '.');
    }
}
