<?php

namespace App\Tests\Controller;

use App\Controller\SearchController;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;
use Symfony\Component\HttpFoundation\Request;

/**
 * Search URLs saved on prod (v3.8.5) use the old field names; they keep their filters on route C.
 */
class SearchLegacyParametersTest extends TestCase
{
    /**
     * @SuppressWarnings("PHPMD.StaticAccess")
     */
    public function testOldFieldNamesAreMappedToTheNewOnes(): void
    {
        $request = Request::create('/search/locations', 'GET', [
            'search' => ['location' => 'Paris', 'offerdinner' => '1', 'offertour' => '1', 'profile_picture' => '1', 'about_me' => '1'],
            'map' => ['offers_dinner' => '1', 'offerdinner' => '0'],
        ]);

        $controller = new SearchController($this->createStub(EntityManagerInterface::class));
        (new ReflectionMethod($controller, 'renameLegacySearchParameters'))->invoke($controller, $request);

        $this->assertSame(
            ['location' => 'Paris', 'offers_dinner' => '1', 'offers_tour' => '1', 'has_profile_picture' => '1', 'has_about_me' => '1'],
            $request->query->all('search'),
        );
        // a new name already present wins; the old one is left as it was
        $this->assertSame(['offers_dinner' => '1', 'offerdinner' => '0'], $request->query->all('map'));
    }
}
