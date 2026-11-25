<?php

namespace App\Tests\Controller;

use App\EventListener\ReleaseSessionListener;
use App\Repository\MemberRepository;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * The pictures, widgets and counters of a page give the session back before they run (#[ReleasesSession]) and
 * don't open it again when they end: a logout in between stays a logout. The member stays logged in all the same.
 */
#[Group('integration')]
final class ReleasedSessionTest extends WebTestCase
{
    private KernelBrowser $client;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->client->loginUser(static::getContainer()->get(MemberRepository::class)->loadUserByIdentifier('member-2'));
    }

    /**
     * ReleaseSessionListener relies on how Symfony's firewall marks a request it will write back: this fails when
     * an update of Symfony names it otherwise.
     */
    public function testTheFirewallMarksARequestItWritesBack(): void
    {
        $this->client->request('GET', '/about');

        self::assertResponseIsSuccessful();
        self::assertSame('_security_main', $this->client->getRequest()->attributes->get(ReleaseSessionListener::FIREWALL_RAN));
    }

    public function testAReleasedRequestIsNotWrittenBackAndTheMemberStaysLoggedIn(): void
    {
        foreach (['/widget/threads', '/widget/conversations?all=0&unread=0'] as $widget) {
            $this->client->request('GET', $widget);
            self::assertResponseIsSuccessful();
            self::assertFalse($this->client->getRequest()->attributes->has(ReleaseSessionListener::FIREWALL_RAN), $widget);
        }

        // a page for members only: the session still has the member
        $this->client->request('GET', '/conversations/');
        self::assertResponseIsSuccessful();
    }
}
