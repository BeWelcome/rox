<?php

namespace App\Tests\Routing;

use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Routing\RouterInterface;

class LegacyRoutesTest extends KernelTestCase
{
    /**
     * Old URLs that the Symfony controllers answer; they have no legacy route any longer.
     */
    public static function symfonyPaths(): iterable
    {
        yield ['/editmyprofile', 'legacy_edit_profile_redirect'];
        yield ['/editmyprofile/de', 'legacy_edit_profile_redirect'];
        yield ['/editmyprofile/finish', 'legacy_edit_profile_redirect'];
        yield ['/editmyprofile/de/finish', 'legacy_edit_profile_redirect'];
        yield ['/editmyprofile/de/add', 'legacy_edit_profile_redirect'];
        yield ['/editmyprofile/de/delete', 'legacy_edit_profile_redirect'];
        yield ['/resetpassword', 'member_request_reset_password'];
        yield ['/members/member-2/note/add', 'add_note'];
        yield ['/members/member-2/note/edit', 'edit_note'];
        yield ['/rox/in/de', 'language'];
        yield ['/mypreferences', 'mypreferences_redirect'];
        yield ['/gallery/show/user/member-2/pictures', 'gallery_member_old'];
        yield ['/members/member-2/gallery', 'member_gallery'];
        yield ['/gallery/manage', 'gallery_manage_page'];
        yield ['/members/member-2/old', 'members_profile'];
    }

    #[DataProvider('symfonyPaths')]
    public function testTheOldUrlIsAnsweredBySymfony(string $path, string $route): void
    {
        $parameters = $this->router()->match($path);

        $this->assertSame($route, $parameters['_route']);
        $this->assertStringNotContainsString('legacy_controller', $parameters['_controller']);
    }

    /**
     * Legacy routes whose old code no longer exists.
     */
    public static function removedPaths(): iterable
    {
        yield ['/setprofileactive'];
        yield ['/setprofileinactive'];
        yield ['/close/12'];
        yield ['/terms/old/fr'];
        yield ['/messages/with/member-2'];
    }

    #[DataProvider('removedPaths')]
    public function testTheDeadUrlHasNoRoute(string $path): void
    {
        $this->expectException(ResourceNotFoundException::class);

        $this->router()->match($path);
    }

    public function testTheRemovedNamesAreGone(): void
    {
        $routes = $this->router()->getRouteCollection();

        $removed = [
            'members_profile_old', 'members_profile_notification', 'members_reset_password_finish',
            'gallery_show_user_images', 'gallery_manage', 'mypreferences', 'messages_with', 'rox_in_language',
        ];
        foreach ($removed as $name) {
            $this->assertNull($routes->get($name), $name);
        }
    }

    private function router(): RouterInterface
    {
        self::bootKernel();

        return self::getContainer()->get(RouterInterface::class);
    }
}
