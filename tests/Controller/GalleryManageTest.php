<?php

namespace App\Tests\Controller;

use App\Entity\Gallery;
use App\Entity\GalleryImage;
use App\Entity\Member;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * A member manages their own photos on /gallery/manage: the page lists them, a photo gets a title and an album,
 * albums are created, renamed and deleted (their photos stay), photos are moved and deleted. Another member's
 * photos are never touched, and the calls need the page's token.
 */
#[Group('integration')]
class GalleryManageTest extends WebTestCase
{
    private KernelBrowser $client;
    private EntityManagerInterface $entityManager;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->entityManager = static::getContainer()->get(EntityManagerInterface::class);
    }

    public function testTheOwnerManagesPhotosAndAlbums(): void
    {
        $owner = $this->member('member-3');
        $photo = $this->photo($owner);
        $this->client->loginUser($owner);

        $crawler = $this->client->request('GET', '/gallery/manage');
        $this->assertResponseIsSuccessful();
        self::assertCount(1, $crawler->filter('.p-gm-grid .js-gm-tile[data-id="' . $photo . '"]'));
        $token = $crawler->filter('.js-gm')->attr('data-token');

        $album = $this->call('/gallery/manage/albums', ['title' => 'Zz test trip'], $token)['id'];
        $this->call('/gallery/manage/pictures/' . $photo, ['title' => 'Our street', 'description' => 'Sunny', 'album' => $album], $token);
        $this->entityManager->clear();
        $picture = $this->entityManager->find(GalleryImage::class, $photo);
        self::assertSame('Our street', $picture->getTitle());
        self::assertSame($album, $picture->getGalleries()->first()->getId());

        $crawler = $this->client->request('GET', '/gallery/manage?album=' . $album);
        self::assertCount(1, $crawler->filter('.js-gm-tile[data-id="' . $photo . '"]'));
        self::assertSame('Zz test trip', trim($crawler->filter('.js-gm-album-name')->text()));

        $this->call('/gallery/manage/albums/' . $album, ['title' => 'Zz test summer'], $token);
        $this->entityManager->clear();
        self::assertSame('Zz test summer', $this->entityManager->find(Gallery::class, $album)->getTitle());

        $this->call('/gallery/manage/pictures/move', ['pictures' => [$photo], 'album' => null], $token);
        $this->entityManager->clear();
        self::assertCount(0, $this->entityManager->find(GalleryImage::class, $photo)->getGalleries());

        $this->call('/gallery/manage/albums/' . $album . '/delete', [], $token);
        $this->entityManager->clear();
        self::assertNull($this->entityManager->find(Gallery::class, $album));
        self::assertNotNull($this->entityManager->find(GalleryImage::class, $photo));

        $deleted = $this->call('/gallery/manage/pictures/delete', ['pictures' => [$photo]], $token)['deleted'];
        self::assertSame([$photo], $deleted);
        $this->entityManager->clear();
        self::assertNull($this->entityManager->find(GalleryImage::class, $photo));
    }

    public function testAnotherMembersPhotosAreLeftAlone(): void
    {
        $other = $this->photo($this->member('member-2'));
        $this->client->loginUser($this->member('member-3'));
        $token = $this->client->request('GET', '/gallery/manage')->filter('.js-gm')->attr('data-token');

        self::assertSame([], $this->call('/gallery/manage/pictures/delete', ['pictures' => [$other]], $token)['deleted']);
        $this->entityManager->clear();
        self::assertNotNull($this->entityManager->find(GalleryImage::class, $other));

        $this->client->jsonRequest('POST', '/gallery/manage/pictures/' . $other, ['title' => 'Mine'], ['HTTP_X_CSRF_TOKEN' => $token]);
        $this->assertResponseStatusCodeSame(404);

        $this->entityManager->remove($this->entityManager->find(GalleryImage::class, $other));
        $this->entityManager->flush();
    }

    public function testCallsNeedThePageToken(): void
    {
        $this->client->loginUser($this->member('member-3'));

        $this->client->jsonRequest('POST', '/gallery/manage/albums', ['title' => 'Zz test'], ['HTTP_X_CSRF_TOKEN' => 'wrong']);

        $this->assertResponseStatusCodeSame(403);
    }

    /**
     * @param array<string, mixed> $body
     *
     * @return array<string, mixed>
     */
    private function call(string $url, array $body, string $token): array
    {
        $this->client->jsonRequest('POST', $url, $body, ['HTTP_X_CSRF_TOKEN' => $token]);
        $this->assertResponseIsSuccessful();

        return json_decode((string) $this->client->getResponse()->getContent(), true, 512, \JSON_THROW_ON_ERROR);
    }

    private function member(string $username): Member
    {
        $member = $this->entityManager->getRepository(Member::class)->findOneBy(['username' => $username]);
        self::assertInstanceOf(Member::class, $member);

        return $member;
    }

    /**
     * A photo row without a file (deleting it removes no file).
     */
    private function photo(Member $owner): int
    {
        $photo = new GalleryImage();
        $photo->setOwner($owner);
        $photo->setFile('zz-test-' . bin2hex(random_bytes(4)) . '.jpg');
        $photo->setOriginal('test.jpg');
        $photo->setFlags('');
        $photo->setMimetype('image/jpeg');
        $photo->setWidth(800);
        $photo->setHeight(600);
        $photo->setTitle('');
        $photo->setDescription('');
        $this->entityManager->persist($photo);
        $this->entityManager->flush();

        return $photo->getId();
    }
}
