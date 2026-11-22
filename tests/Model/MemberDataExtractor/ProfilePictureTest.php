<?php

namespace App\Tests\Model\MemberDataExtractor;

use App\Model\MemberDataExtractor\ProfilePicture;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * The stored pictures have no extension; the export names them after their type (the cropper sends WebP).
 */
final class ProfilePictureTest extends TestCase
{
    private string $file;

    protected function tearDown(): void
    {
        if (isset($this->file) && is_file($this->file)) {
            unlink($this->file);
        }
    }

    /**
     * @return iterable<string, array{string, string}>
     */
    public static function pictures(): iterable
    {
        // a 1x1 picture of each type
        yield 'webp' => ['UklGRhoAAABXRUJQVlA4TA0AAAAvAAAAEAcQERGIiP4HAA==', '.webp'];
        yield 'png' => ['iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNkYPhfDwAChwGA60e6kgAAAABJRU5ErkJggg==', '.png'];
    }

    #[DataProvider('pictures')]
    public function testThePictureIsNamedAfterItsType(string $picture, string $extension): void
    {
        $this->file = sys_get_temp_dir() . '/' . uniqid('12_') . '_original';
        file_put_contents($this->file, base64_decode($picture, true));

        self::assertSame(basename($this->file) . $extension, ProfilePicture::exportName($this->file));
    }
}
