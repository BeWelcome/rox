<?php

namespace App\Tests\Controller;

use App\Entity\Member;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DomCrawler\Crawler;

/**
 * The preferences, as a page and in the side panel of the edit page: each setting above its control. For browser
 * notifications, what this browser does sits under the choice, with Enable hidden until the page's script finds it
 * would turn the notifications on here (assets/js/profile/preference.js).
 */
#[Group('integration')]
class PreferencesPageTest extends WebTestCase
{
    private KernelBrowser $client;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $member = static::getContainer()->get(EntityManagerInterface::class)
            ->getRepository(Member::class)->findOneBy(['username' => 'member-2']);
        $this->client->loginUser($member);
    }

    public function testThePageLooksLikeTheSidePanel(): void
    {
        $crawler = $this->client->request('GET', '/members/member-2/preferences');

        $this->assertResponseIsSuccessful();
        self::assertStringContainsString('fa-cog', (string) $crawler->filter('.p-edit-subpage__eyebrow')->html());
        $this->assertBrowserNotifications($crawler);
    }

    public function testTheSidePanelShowsTheSameSettings(): void
    {
        $page = $this->client->request('GET', '/members/member-2/preferences');
        $panel = $this->client->request('GET', '/members/member-2/preferences?embed=1');

        $this->assertResponseIsSuccessful();
        self::assertSame(
            $page->filter('.p-preference-card__title')->each(static fn (Crawler $title): string => $title->text()),
            $panel->filter('.p-preference-card__title')->each(static fn (Crawler $title): string => $title->text()),
        );
        $this->assertBrowserNotifications($panel);
    }

    private function assertBrowserNotifications(Crawler $crawler): void
    {
        $card = $crawler->filter('.p-preference-card')->reduce(
            static fn (Crawler $card): bool => $card->filter('[data-browser-push-preference]')->count() > 0
        );
        self::assertCount(1, $card);
        // under the choice, not next to it
        self::assertCount(0, $card->filter('.p-preference-card__control [data-browser-push-device-controls]'));
        $controls = $card->filter('.p-preference-card > .p-preference-push[data-browser-push-device-controls]');
        self::assertCount(1, $controls);
        self::assertCount(1, $controls->filter('[data-browser-push-status][role=status]'));
        self::assertNotNull($controls->filter('button[data-browser-push-enable]')->attr('hidden'));
    }
}
