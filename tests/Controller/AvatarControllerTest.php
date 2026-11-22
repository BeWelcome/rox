<?php

namespace App\Tests\Controller;

use App\Entity\Member;
use App\Service\MailLinkSigner;
use Doctrine\DBAL\Connection;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

#[Group('integration')]
/**
 * Regression tests for BeWelcome/rox#533: the avatar route answered 500 instead of the empty avatar
 * when the avatar file of a member was not a readable regular file.
 * The pictures in mails are signed links: mail programs load them without logging in.
 *
 * @infection-ignore-all
 */
class AvatarControllerTest extends WebTestCase
{
    private const AVATAR_DIRECTORY = '../data/user/avatars/';

    private string $originalWorkingDirectory;
    private int $memberId;
    private Filesystem $filesystem;

    protected function setUp(): void
    {
        // The controller resolves its avatar path relative to the web root, as in production.
        $this->originalWorkingDirectory = getcwd();
        chdir(__DIR__ . '/../../public');

        $this->filesystem = new Filesystem();
        $this->filesystem->mkdir(self::AVATAR_DIRECTORY);
    }

    protected function tearDown(): void
    {
        if (isset($this->memberId)) {
            $this->removeAvatarFiles();
        }
        chdir($this->originalWorkingDirectory);

        parent::tearDown();
    }

    public function testNonImageOriginalFallsBackToEmptyAvatar(): void
    {
        $client = $this->createLoggedInClient();
        file_put_contents($this->avatarFile('original'), 'this is not an image');

        $client->request('GET', '/members/avatar/member-5/48');

        $this->assertResponseIsSuccessful();
        $this->assertServedFileStartsWith($client, 'empty_avatar_');
    }

    public function testDirectoryAsThumbnailFallsBackToEmptyAvatar(): void
    {
        $client = $this->createLoggedInClient();
        mkdir($this->avatarFile($this->siteThumbnail(48)));

        $client->request('GET', '/members/avatar/member-5/48');

        $this->assertResponseIsSuccessful();
        $this->assertServedFileStartsWith($client, 'empty_avatar_');
    }

    public function testExistingThumbnailIsServed(): void
    {
        $client = $this->createLoggedInClient();
        imagepng(imagecreatetruecolor(48, 48), $this->avatarFile($this->siteThumbnail(48)));

        $client->request('GET', '/members/avatar/member-5/48');

        $this->assertResponseIsSuccessful();
        $this->assertServedFile($client, $this->siteThumbnail(48));
    }

    public function testWithoutLoginTheEmptyAvatarIsServed(): void
    {
        $client = $this->createAnonymousClient();
        imagepng(imagecreatetruecolor(96, 96), $this->avatarFile('96_96'));

        $client->request('GET', '/members/avatar/member-5/96');

        $this->assertResponseIsSuccessful();
        $this->assertServedFileStartsWith($client, 'empty_avatar_');
    }

    /**
     * The placeholder of a size is made once and served from then on. It was looked up under another name than it
     * is written under, so every request made it again, and one could read it while another wrote it.
     */
    public function testTheEmptyAvatarOfASizeIsMadeOnce(): void
    {
        // without a login every avatar is the placeholder
        $client = $this->createAnonymousClient();
        $placeholder = self::AVATAR_DIRECTORY . 'empty_avatar_48_48';
        $client->request('GET', '/members/avatar/member-2/48');
        $this->assertResponseIsSuccessful();
        self::assertFileExists($placeholder);
        $made = strtotime('-1 day');
        touch($placeholder, $made);
        clearstatcache();

        $client->request('GET', '/members/avatar/member-2/48');

        $this->assertResponseIsSuccessful();
        self::assertInstanceOf(BinaryFileResponse::class, $client->getResponse());
        self::assertSame('empty_avatar_48_48', $client->getResponse()->getFile()->getFilename());
        clearstatcache();
        self::assertSame($made, filemtime($placeholder), 'the placeholder was written again');
    }

    public function testASignedLinkFromAMailServesThePictureWithoutLogin(): void
    {
        $client = $this->createAnonymousClient();
        imagepng(imagecreatetruecolor(96, 96), $this->avatarFile('96_96'));
        $url = $this->mailAvatarUrl('member-5');

        $client->request('GET', $url);
        $this->assertResponseIsSuccessful();
        $this->assertServedFile($client, '96_96');

        // a changed signature, or the link of another member's picture
        $client->request('GET', preg_replace('/_hash=[^&]+/', '_hash=zztest', $url));
        $this->assertServedFileStartsWith($client, 'empty_avatar_');
        $client->request('GET', str_replace('/member-5/', '/member-6/', $url));
        $this->assertServedFileStartsWith($client, 'empty_avatar_');
    }

    public function testASignedLinkShowsNoPictureOfAMemberOthersCantSee(): void
    {
        $client = $this->createAnonymousClient();
        imagepng(imagecreatetruecolor(96, 96), $this->avatarFile('96_96'));
        $url = $this->mailAvatarUrl('member-5');
        static::getContainer()->get(Connection::class)
            ->executeStatement("UPDATE member SET Status = 'Banned' WHERE id = ?", [$this->memberId]);
        static::getContainer()->get('doctrine.orm.entity_manager')->clear();

        $client->request('GET', $url);

        $this->assertServedFileStartsWith($client, 'empty_avatar_');
    }

    public function testAnUploadedPictureIsStoredAndServedResized(): void
    {
        $client = static::createClient();
        $member = static::getContainer()->get('doctrine.orm.entity_manager')->getRepository(Member::class)
            ->findOneBy(['username' => 'member-5']);
        $this->assertInstanceOf(Member::class, $member);
        $this->memberId = $member->getId();
        $this->removeAvatarFiles();
        $client->loginUser($member);

        // as the browser sends it: the cropped picture of the canvas as a data URI
        ob_start();
        imagepng(imagecreatetruecolor(120, 80));
        $picture = 'data:image/png;base64,' . base64_encode((string) ob_get_clean());
        $client->request('POST', '/members/uploadavatar', ['avatar' => $picture]);

        $this->assertResponseIsSuccessful();
        self::assertSame("\x89PNG", substr((string) file_get_contents($this->avatarFile('original')), 0, 4));

        $client->request('GET', '/members/avatar/member-5/48');

        $this->assertResponseIsSuccessful();
        $this->assertServedFile($client, $this->siteThumbnail(48));
        self::assertSame([48, 48], \array_slice(getimagesize($this->avatarFile($this->siteThumbnail(48))) ?: [], 0, 2));
    }

    /**
     * The site gets WebP, the link in a mail JPEG: mail programs like Outlook show no WebP.
     */
    public function testTheSiteGetsWebpAndAMailJpeg(): void
    {
        if (!(gd_info()['WebP Support'] ?? false)) {
            self::markTestSkipped('GD without WebP');
        }
        $client = $this->createLoggedInClient();
        imagepng(imagecreatetruecolor(200, 200), $this->avatarFile('original'));

        $client->request('GET', '/members/avatar/member-5/96');
        $this->assertServedFile($client, '96_96.webp');
        self::assertSame('image/webp', getimagesize($this->avatarFile('96_96.webp'))['mime'] ?? null);

        $client->request('GET', $this->mailAvatarUrl('member-5'));
        $this->assertServedFile($client, '96_96');
        self::assertSame('image/jpeg', getimagesize($this->avatarFile('96_96'))['mime'] ?? null);
    }

    /**
     * The thumbnail the site is served: WebP where PHP can write it.
     */
    private function siteThumbnail(int $size): string
    {
        return $size . '_' . $size . ((gd_info()['WebP Support'] ?? false) ? '.webp' : '');
    }

    private function assertServedFile(KernelBrowser $client, string $suffix): void
    {
        $response = $client->getResponse();
        $this->assertInstanceOf(BinaryFileResponse::class, $response);
        self::assertSame($this->memberId . '_' . $suffix, $response->getFile()->getFilename());
    }

    private function createAnonymousClient(): KernelBrowser
    {
        $client = static::createClient();
        $member = static::getContainer()->get('doctrine.orm.entity_manager')->getRepository(Member::class)
            ->findOneBy(['username' => 'member-5']);
        $this->assertInstanceOf(Member::class, $member);
        $this->memberId = $member->getId();
        $this->removeAvatarFiles();

        return $client;
    }

    private function mailAvatarUrl(string $username): string
    {
        $member = static::getContainer()->get('doctrine.orm.entity_manager')->getRepository(Member::class)
            ->findOneBy(['username' => $username]);

        return static::getContainer()->get(MailLinkSigner::class)->mailAvatarUrl($member, 96);
    }

    private function createLoggedInClient(): KernelBrowser
    {
        $client = static::createClient();
        $repository = static::getContainer()->get('doctrine.orm.entity_manager')->getRepository(Member::class);
        $viewer = $repository->findOneBy(['username' => 'member-2']);
        $member = $repository->findOneBy(['username' => 'member-5']);
        $this->assertInstanceOf(Member::class, $viewer);
        $this->assertInstanceOf(Member::class, $member);

        $this->memberId = $member->getId();
        $this->removeAvatarFiles();
        $client->loginUser($viewer);

        return $client;
    }

    private function avatarFile(string $suffix): string
    {
        return self::AVATAR_DIRECTORY . $this->memberId . '_' . $suffix;
    }

    private function removeAvatarFiles(): void
    {
        foreach (glob(self::AVATAR_DIRECTORY . $this->memberId . '_*') ?: [] as $file) {
            $this->filesystem->remove($file);
        }
    }

    private function assertServedFileStartsWith(KernelBrowser $client, string $prefix): void
    {
        $response = $client->getResponse();
        $this->assertInstanceOf(BinaryFileResponse::class, $response);
        self::assertStringStartsWith($prefix, $response->getFile()->getFilename());
    }
}
