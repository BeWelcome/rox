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
        mkdir($this->avatarFile('48_48'));

        $client->request('GET', '/members/avatar/member-5/48');

        $this->assertResponseIsSuccessful();
        $this->assertServedFileStartsWith($client, 'empty_avatar_');
    }

    public function testExistingThumbnailIsServed(): void
    {
        $client = $this->createLoggedInClient();
        imagepng(imagecreatetruecolor(48, 48), $this->avatarFile('48_48'));

        $client->request('GET', '/members/avatar/member-5/48');

        $this->assertResponseIsSuccessful();
        $this->assertServedFileStartsWith($client, $this->memberId . '_48_48');
    }

    public function testWithoutLoginTheEmptyAvatarIsServed(): void
    {
        $client = $this->createAnonymousClient();
        imagepng(imagecreatetruecolor(96, 96), $this->avatarFile('96_96'));

        $client->request('GET', '/members/avatar/member-5/96');

        $this->assertResponseIsSuccessful();
        $this->assertServedFileStartsWith($client, 'empty_avatar_');
    }

    public function testASignedLinkFromAMailServesThePictureWithoutLogin(): void
    {
        $client = $this->createAnonymousClient();
        imagepng(imagecreatetruecolor(96, 96), $this->avatarFile('96_96'));
        $url = $this->mailAvatarUrl('member-5');

        $client->request('GET', $url);
        $this->assertResponseIsSuccessful();
        $this->assertServedFileStartsWith($client, $this->memberId . '_96_96');

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
