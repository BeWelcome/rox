<?php

namespace Rox\CommunityNews\Model;

use Rox\Core\Kernel\Application;
use Rox\Member\Model\Member;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Client;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

class CommunityNewsControllerTest extends WebTestCase
{
    /** @var Client */
    private $client = null;

    protected static function getKernelClass()
    {
        return new Application('testing', true);
    }

    public function setUp()
    {
        $this->client = static::createClient();
    }

    public function tearDown()
    {
    }

    private function logInUser()
    {
        $session = $this->client->getContainer()->get('session');

        $firewall = 'default';
        $memberRepository = new Member();
        $user = $memberRepository->getByUsername('member-1');
        $token = new UsernamePasswordToken($user, null, $firewall, ['ROLE_USER']);
        $session->set('_security_' . $firewall, serialize($token));
        $session->set('IdMember', 1);
        $session->set('Status', 'Active');
        $session->set('MemberStatus', 'Active');
        $session->set('lang', 'en');
        $session->save();

        $cookie = new Cookie($session->getName(), $session->getId());
        $this->client->getCookieJar()->set($cookie);
    }

    public function testListActionLoggedIn()
    {
        $this->logInUser();

        $crawler = $this->client->request('GET', '/communitynews');

        $response = $this->client->getResponse();
        $this->assertTrue($response->isSuccessful());
        $this->assertGreaterThan(0, $crawler->filter('html:contains("Community News")')->count());
    }

    public function testListActionRedirect()
    {
        $crawler = $this->client->request('GET', '/communitynews');

        $response = $this->client->getResponse();
        $this->assertEquals(RedirectResponse::class, get_class($response));
        $this->assertGreaterThan(0, $crawler->filter('html:contains("/login")')->count());
    }

    public function testShowActionLoggedInFull()
    {
        $this->logInUser();

        $crawler = $this->client->request('GET', '/communitynews/1/Created by member-1/');

        $response = $this->client->getResponse();
        $this->assertTrue($response->isSuccessful());
        $this->assertGreaterThan(0, $crawler->filter('html:contains("Community News")')->count());
    }

    public function testShowActionLoggedInPart()
    {
        $this->logInUser();

        $crawler = $this->client->request('GET', '/communitynews/1');

        $response = $this->client->getResponse();
        $this->assertTrue($response->isSuccessful());
        $this->assertGreaterThan(0, $crawler->filter('html:contains("Community News")')->count());
    }

    public function testShowActionRedirectFull()
    {
        $crawler = $this->client->request('GET', '/communitynews/1/Created by member-1');

        $response = $this->client->getResponse();
        $this->assertEquals(RedirectResponse::class, get_class($response));
        $this->assertGreaterThan(0, $crawler->filter('html:contains("/login")')->count());
    }

    public function testShowActionRedirectPart()
    {
        $crawler = $this->client->request('GET', '/communitynews/1');

        $response = $this->client->getResponse();
        $this->assertEquals(RedirectResponse::class, get_class($response));
        $this->assertGreaterThan(0, $crawler->filter('html:contains("/login")')->count());
    }

    public function testShowActionSameResultLoggedIn()
    {
        $this->logInUser();

        $this->client->request('GET', '/communitynews/1');
        $responsePart = $this->client->getResponse();

        $this->client->request('GET', '/communitynews/1');
        $responseFull = $this->client->getResponse();

        $this->assertEquals($responsePart, $responseFull);
    }

    public function testShowActionSameResult()
    {
        $this->client->request('GET', '/communitynews/1');
        $responsePart = $this->client->getResponse();

        $this->client->request('GET', '/communitynews/1');
        $responseFull = $this->client->getResponse();

        $this->assertEquals($responsePart, $responseFull);
    }
}
