<?php

namespace App\Tests\Controller;

use App\Entity\Member;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

/**
 * Plausible analytics (#540): with PLAUSIBLE_SCRIPT_URL set, every layout loads the script and sends the events
 * queued as plausible_event flashes; without it nothing is loaded.
 */
#[Group('integration')]
class AnalyticsTest extends WebTestCase
{
    private const string SCRIPT = 'https://plausible.example/js/script.js';

    private KernelBrowser $client;

    protected function setUp(): void
    {
        $_SERVER['PLAUSIBLE_SCRIPT_URL'] = $_ENV['PLAUSIBLE_SCRIPT_URL'] = self::SCRIPT;
        $this->client = static::createClient();
    }

    protected function tearDown(): void
    {
        unset($_SERVER['PLAUSIBLE_SCRIPT_URL'], $_ENV['PLAUSIBLE_SCRIPT_URL']);

        parent::tearDown();
    }

    public function testAnonymousPagesLoadTheScriptWithoutStartingASession(): void
    {
        foreach (['/', '/login', '/signup'] as $path) {
            $this->client->request('GET', $path);
            $this->assertResponseIsSuccessful();
            $this->assertSelectorExists('script[src="' . self::SCRIPT . '"]', $path . ' has no Plausible script');
        }

        self::assertNull($this->client->getCookieJar()->get('PHPSESSID'), 'the analytics started a session');
    }

    public function testALoginIsCountedOnTheNextPage(): void
    {
        $entityManager = static::getContainer()->get(EntityManagerInterface::class);
        $member = $entityManager->getRepository(Member::class)->findOneBy(['username' => 'member-2']);
        $member->setPassword(static::getContainer()->get(UserPasswordHasherInterface::class)->hashPassword($member, 'a-password-1'));
        $entityManager->flush();
        $this->client->request('GET', '/login');

        $this->client->request('POST', '/login_check', ['_username' => 'member-2', '_password' => 'a-password-1']);
        $this->client->followRedirect();

        self::assertStringContainsString('plausible("Login")', (string) $this->client->getResponse()->getContent());
    }

    public function testAMissingPageIsReported(): void
    {
        $this->client->loginUser($this->member('member-2'));

        $this->client->request('GET', '/no-such-page-' . bin2hex(random_bytes(4)));

        $this->assertResponseStatusCodeSame(404);
        self::assertStringContainsString("plausible('404'", (string) $this->client->getResponse()->getContent());
    }

    private function member(string $username): Member
    {
        $member = static::getContainer()->get(EntityManagerInterface::class)
            ->getRepository(Member::class)->findOneBy(['username' => $username]);
        self::assertInstanceOf(Member::class, $member);

        return $member;
    }
}
