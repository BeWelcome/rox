<?php

namespace App\Tests\Controller;

use App\Entity\GalleryImage;
use App\Entity\Member;
use App\Service\MemberGallery;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * Gallery pictures: their files (/gallery/images/...), the member's gallery pages (/members/{username}/gallery)
 * and the old URLs of both, which lead there.
 */
#[Group('integration')]
class GalleryImagesTest extends WebTestCase
{
    private KernelBrowser $client;
    private EntityManagerInterface $entityManager;

    /** @var list<string> */
    private array $files = [];

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->entityManager = static::getContainer()->get(EntityManagerInterface::class);
    }

    protected function tearDown(): void
    {
        new Filesystem()->remove($this->files);
        parent::tearDown();
    }

    public function testTheOriginalIsServed(): void
    {
        $owner = $this->member('member-3');
        $picture = $this->picture($owner, 900, 600);
        $this->client->loginUser($this->member('member-2'));

        $this->client->request('GET', '/gallery/images/' . $picture->getId());

        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('Content-Type', 'image/jpeg');
        self::assertSame([900, 600], $this->servedSize());
        self::assertStringContainsString('private', (string) $this->client->getResponse()->headers->get('Cache-Control'));
    }

    public function testTheDownloadKeepsTheUploadedName(): void
    {
        $picture = $this->picture($this->member('member-3'), 300, 200);
        $this->client->loginUser($this->member('member-2'));

        $this->client->request('GET', '/gallery/images/' . $picture->getId() . '/download');

        $this->assertResponseIsSuccessful();
        self::assertStringStartsWith('attachment;', (string) $this->client->getResponse()->headers->get('Content-Disposition'));
        self::assertStringContainsString('holiday.jpg', (string) $this->client->getResponse()->headers->get('Content-Disposition'));
    }

    public function testTheSmallerCopiesAreMadeOnce(): void
    {
        $owner = $this->member('member-3');
        $picture = $this->picture($owner, 2000, 1000);
        $this->client->loginUser($owner);

        $expected = ['thumbnail' => [100, 100], 'small' => [240, 120], 'medium' => [500, 250], 'large' => [1600, 800]];
        foreach ($expected as $size => $dimensions) {
            $this->client->request('GET', '/gallery/images/' . $picture->getId() . '/' . $size);
            $this->assertResponseIsSuccessful();
            self::assertSame($dimensions, $this->servedSize(), $size);
        }
        $prefixes = ['thumb', 'thumb1', 'thumb2', 'thumb3'];
        foreach ($prefixes as $prefix) {
            self::assertFileExists($this->directory($owner) . $prefix . $picture->getFile());
        }
    }

    public function testASmallPictureIsServedAsItIs(): void
    {
        $owner = $this->member('member-3');
        $picture = $this->picture($owner, 200, 150);
        $this->client->loginUser($owner);

        $this->client->request('GET', '/gallery/images/' . $picture->getId() . '/medium');

        $this->assertResponseIsSuccessful();
        self::assertSame([200, 150], $this->servedSize());
        self::assertFileDoesNotExist($this->directory($owner) . 'thumb2' . $picture->getFile());
    }

    public function testAMissingFileIsNotFound(): void
    {
        $picture = $this->picture($this->member('member-3'), 0, 0);
        $this->client->loginUser($this->member('member-2'));

        $this->client->request('GET', '/gallery/images/' . $picture->getId() . '/small');

        $this->assertResponseStatusCodeSame(404);
    }

    public function testTheFilesNeedALogin(): void
    {
        $picture = $this->picture($this->member('member-3'), 300, 200);

        $this->client->request('GET', '/gallery/images/' . $picture->getId());

        $this->assertResponseRedirects();
        self::assertStringContainsString('/login', (string) $this->client->getResponse()->headers->get('Location'));
    }

    public function testTheOldFileUrlsLeadToTheNewOnes(): void
    {
        $picture = $this->picture($this->member('member-3'), 300, 200);
        $id = $picture->getId();
        $this->client->loginUser($this->member('member-2'));

        $old = [
            '/gallery/img?id=' . $id => '/gallery/images/' . $id,
            '/gallery/img?id=' . $id . '&t=1' => '/gallery/images/' . $id,
            '/gallery/img?id=' . $id . '&s=1' => '/gallery/images/' . $id . '/download',
            '/gallery/thumbimg?id=' . $id => '/gallery/images/' . $id . '/thumbnail',
            '/gallery/thumbimg?id=' . $id . '&t=1' => '/gallery/images/' . $id . '/small',
            '/gallery/thumbimg?id=' . $id . '&t=2' => '/gallery/images/' . $id . '/medium',
            '/gallery/thumbimg?id=' . $id . '&t=3' => '/gallery/images/' . $id . '/large',
        ];
        foreach ($old as $url => $new) {
            $this->client->request('GET', $url);
            $this->assertResponseRedirects($new, 301, $url);
        }

        $this->client->request('GET', '/gallery/thumbimg');
        $this->assertResponseStatusCodeSame(404);
    }

    public function testTheMembersGallery(): void
    {
        $owner = $this->member('member-3');
        $picture = $this->picture($owner, 300, 200);
        $album = static::getContainer()->get(MemberGallery::class)->createAlbum($owner, 'Zz test trip');
        static::getContainer()->get(MemberGallery::class)->moveToAlbum([$picture], $album);
        $this->client->loginUser($this->member('member-2'));

        $crawler = $this->client->request('GET', '/members/member-3/gallery');
        $this->assertResponseIsSuccessful();
        self::assertCount(1, $crawler->filter('#picture-' . $picture->getId()));
        self::assertCount(1, $crawler->filter('a[href="/members/member-3/gallery/albums/' . $album->getId() . '"]'));

        $crawler = $this->client->request('GET', '/members/member-3/gallery/albums/' . $album->getId());
        $this->assertResponseIsSuccessful();
        self::assertCount(1, $crawler->filter('#picture-' . $picture->getId()));

        $this->client->request('GET', '/members/member-3/gallery/albums/999999999');
        $this->assertResponseStatusCodeSame(404);
    }

    public function testTheOldPageUrlsLeadToTheNewOnes(): void
    {
        $owner = $this->member('member-3');
        $picture = $this->picture($owner, 300, 200);
        $album = static::getContainer()->get(MemberGallery::class)->createAlbum($owner, 'Zz test trip');
        $this->client->loginUser($this->member('member-2'));

        $old = [
            '/gallery/show/user/member-3/pictures' => '/members/member-3/gallery',
            '/gallery/show/user/member-3/pictures?album=' . $album->getId() => '/members/member-3/gallery/albums/' . $album->getId(),
            '/gallery/show/user/member-3' => '/members/member-3/gallery',
            '/gallery/show/user/member-3/sets' => '/members/member-3/gallery',
            '/gallery/show/user/member-3/images/=page2' => '/members/member-3/gallery',
            '/gallery/show/image/' . $picture->getId() => '/members/member-3/gallery#picture-' . $picture->getId(),
            '/gallery/show/sets/' . $album->getId() => '/members/member-3/gallery/albums/' . $album->getId(),
            '/gallery' => '/members/member-2/gallery',
            '/gallery/show/sets' => '/members/member-2/gallery',
            '/gallery/upload' => '/gallery/manage',
            '/gallery/manage/=page3' => '/gallery/manage',
            '/gallery/show/image/' . $picture->getId() . '/delete' => '/gallery/manage',
            '/gallery/show/sets/' . $album->getId() . '/delete/true' => '/gallery/manage',
        ];
        foreach ($old as $url => $new) {
            $this->client->request('GET', $url);
            $this->assertResponseRedirects($new, 301, $url);
        }
    }

    /**
     * @return array{int, int}
     */
    private function servedSize(): array
    {
        $response = $this->client->getResponse();
        self::assertInstanceOf(BinaryFileResponse::class, $response);
        $size = getimagesize($response->getFile()->getPathname());

        return [$size[0], $size[1]];
    }

    private function member(string $username): Member
    {
        $member = $this->entityManager->getRepository(Member::class)->findOneBy(['username' => $username]);
        self::assertInstanceOf(Member::class, $member);

        return $member;
    }

    private function directory(Member $owner): string
    {
        return static::getContainer()->getParameter('gallery_directory') . '/member' . $owner->getId() . '/';
    }

    /**
     * A picture with a JPEG of the size (0 x 0: no file).
     */
    private function picture(Member $owner, int $width, int $height): GalleryImage
    {
        $file = 'zz-test-' . bin2hex(random_bytes(4)) . '.jpg';
        if ($width > 0) {
            $directory = $this->directory($owner);
            new Filesystem()->mkdir($directory);
            $image = imagecreatetruecolor($width, $height);
            imagejpeg($image, $directory . $file);
            foreach (['', 'thumb', 'thumb1', 'thumb2', 'thumb3'] as $prefix) {
                $this->files[] = $directory . $prefix . $file;
            }
        }

        $picture = new GalleryImage();
        $picture->setOwner($owner);
        $picture->setFile($file);
        $picture->setOriginal('holiday.jpg');
        $picture->setFlags('');
        $picture->setMimetype('image/jpeg');
        $picture->setWidth($width);
        $picture->setHeight($height);
        $picture->setTitle('Zz test');
        $picture->setDescription('');
        $this->entityManager->persist($picture);
        $this->entityManager->flush();

        return $picture;
    }
}
