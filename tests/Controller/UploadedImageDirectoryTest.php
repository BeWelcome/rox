<?php

namespace App\Tests\Controller;

use App\Entity\Member;
use App\Entity\UploadedImage;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Filesystem\Filesystem;

/**
 * A picture uploaded in a message or a post is shown as a grey placeholder when its file isn't there (or to
 * visitors). The placeholder is written next to the uploads: where nothing was uploaded yet, that directory doesn't
 * exist, and the picture answered with a server error instead.
 */
#[Group('integration')]
class UploadedImageDirectoryTest extends WebTestCase
{
    private string $directory;
    private ?string $before;

    protected function setUp(): void
    {
        // a directory of this test's own, which doesn't exist yet (config/services.yaml: upload_directory)
        $this->directory = 'var/zztest-uploaded-' . bin2hex(random_bytes(4));
        $this->before = $_SERVER['upload_directory'] ?? null;
        $_SERVER['upload_directory'] = $_ENV['upload_directory'] = $this->directory;
    }

    protected function tearDown(): void
    {
        new Filesystem()->remove(\dirname(__DIR__, 2) . '/' . $this->directory);
        if (null === $this->before) {
            unset($_SERVER['upload_directory'], $_ENV['upload_directory']);
        } else {
            $_SERVER['upload_directory'] = $_ENV['upload_directory'] = $this->before;
        }
        parent::tearDown();
    }

    public function testThePlaceholderIsShownWhereNothingWasUploadedYet(): void
    {
        $client = static::createClient();
        $entityManager = static::getContainer()->get(EntityManagerInterface::class);
        $image = new UploadedImage()
            ->setFilename('zztest-missing.png')
            ->setSize(1)
            ->setWidth(40)
            ->setHeight(30)
            ->setMimeType('image/png')
            ->setFileInfo('zztestfileinfo')
            ->setFileHash(hash('sha256', 'zztest-missing'));
        $entityManager->persist($image);
        $entityManager->flush();
        $path = \dirname(__DIR__, 2) . '/' . $this->directory;
        self::assertDirectoryDoesNotExist($path);
        $client->loginUser($entityManager->getRepository(Member::class)->findOneBy(['username' => 'member-2']));

        $client->request('GET', '/gallery/show/uploaded/' . $image->getId() . '/zztestfileinfo');

        self::assertResponseIsSuccessful();
        self::assertSame('image/png', $client->getResponse()->headers->get('Content-Type'));
        self::assertDirectoryExists($path);
    }
}
