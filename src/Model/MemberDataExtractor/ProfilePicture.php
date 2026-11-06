<?php

namespace App\Model\MemberDataExtractor;

use App\Entity\MemberPhoto;

/**
 * Where the member's profile picture is in the export: ProfilePicturesExtractor copies the files into pictures/,
 * named after the stored file with the extension of its type.
 */
final class ProfilePicture
{
    private const string THUMBNAIL = '_150';

    /**
     * The newest picture that is still on disk: its original and a small version (the original without one).
     *
     * @param iterable<MemberPhoto> $photos newest first
     *
     * @return array{original: string, thumbnail: string}|null paths relative to the export's root
     */
    public static function files(iterable $photos): ?array
    {
        foreach ($photos as $photo) {
            $file = $photo->getFilepath();
            if (is_file($file)) {
                $original = self::exportPath($file);

                return [
                    'original' => $original,
                    'thumbnail' => is_file($file . self::THUMBNAIL) ? self::exportPath($file . self::THUMBNAIL) : $original,
                ];
            }
        }

        return null;
    }

    public static function exportName(string $file): string
    {
        return pathinfo($file, \PATHINFO_FILENAME) . self::extension($file);
    }

    public static function extension(string $file): string
    {
        return match (mime_content_type($file)) {
            'image/png' => '.png',
            'image/jpeg' => '.jpg',
            'image/gif' => '.gif',
            'image/bmp' => '.bmp',
            default => '',
        };
    }

    private static function exportPath(string $file): string
    {
        return 'pictures/' . self::exportName($file);
    }
}
