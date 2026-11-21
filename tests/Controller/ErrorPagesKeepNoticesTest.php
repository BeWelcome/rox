<?php

namespace App\Tests\Controller;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;

/**
 * An error page doesn't take the member's notices. A page asks for pictures and other things next to it; one that
 * isn't there answers with the 404 page, and that page showing the notices used them up: the member never saw the
 * notice of what they had just done. (The test environment shows Symfony's own error pages, so the site's error
 * pages are rendered here as the error controller renders them.)
 */
#[Group('integration')]
class ErrorPagesKeepNoticesTest extends KernelTestCase
{
    /**
     * @return iterable<string, array{string}>
     */
    public static function errorPages(): iterable
    {
        yield 'not found' => ['error404'];
        yield 'access denied' => ['error403'];
        yield 'server error' => ['error500'];
    }

    #[DataProvider('errorPages')]
    public function testAnErrorPageLeavesTheNoticesForTheNextPage(string $page): void
    {
        $session = $this->sessionWithNotices();

        $html = static::getContainer()->get('twig')->render('bundles/TwigBundle/Exception/' . $page . '.html.twig', [
            'status_code' => (int) substr($page, 5),
            'status_text' => 'Error',
        ]);

        self::assertStringNotContainsString('Saved, thank you.', $html);
        self::assertSame(['Saved, thank you.'], $session->getFlashBag()->peek('notice'));
        self::assertSame(['Review Written'], $session->getFlashBag()->peek('plausible_event'));
    }

    public function testAnOrdinaryPageShowsThem(): void
    {
        $session = $this->sessionWithNotices();

        $html = static::getContainer()->get('twig')->render('about/about.html.twig', []);

        self::assertStringContainsString('Saved, thank you.', $html);
        self::assertSame([], $session->getFlashBag()->peek('notice'));
    }

    private function sessionWithNotices(): Session
    {
        self::bootKernel();
        $session = new Session(new MockArraySessionStorage());
        $session->getFlashBag()->add('notice', 'Saved, thank you.');
        $session->getFlashBag()->add('plausible_event', 'Review Written');
        $request = Request::create('/zztest/no/such/page');
        $request->setSession($session);
        // the analytics events are read for members who came with a session
        $request->cookies->set($session->getName(), 'zztest');
        static::getContainer()->get('request_stack')->push($request);

        return $session;
    }
}
