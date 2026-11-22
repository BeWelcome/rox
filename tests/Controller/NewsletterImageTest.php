<?php

namespace App\Tests\Controller;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Filesystem\Filesystem;

/**
 * An image uploaded for a newsletter is served with its type; one that isn't there (any more) is a 404, not an error
 * (rox #527).
 */
#[Group('integration')]
final class NewsletterImageTest extends WebTestCase
{
    private const string NAME = 'zztestNewsletterImage';

    private string $directory;

    protected function tearDown(): void
    {
        if (isset($this->directory)) {
            new Filesystem()->remove($this->directory . '/' . self::NAME);
        }
        parent::tearDown();
    }

    public function testAnUploadedImageIsServed(): void
    {
        $client = static::createClient();
        $this->directory = static::getContainer()->getParameter('newsletter_image_directory');
        new Filesystem()->mkdir($this->directory);
        // a 1x1 transparent PNG
        $png = 'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNkYPhfDwAChwGA60e6kgAAAABJRU5ErkJggg==';
        file_put_contents($this->directory . '/' . self::NAME, base64_decode($png, true));

        $client->request('GET', '/images/newsletter/' . self::NAME);

        self::assertResponseIsSuccessful();
        self::assertResponseHeaderSame('Content-Type', 'image/png');
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function missingImages(): iterable
    {
        yield 'an image that is gone' => ['zztestNoSuchImage'];
        yield 'the folder above' => ['..'];
        yield 'the folder itself' => ['.'];
        yield 'a hidden file' => ['.gitignore'];
    }

    #[DataProvider('missingImages')]
    public function testAMissingImageIsNotFound(string $id): void
    {
        $client = static::createClient();

        $client->request('GET', '/images/newsletter/' . $id);

        self::assertResponseStatusCodeSame(404);
    }
}
