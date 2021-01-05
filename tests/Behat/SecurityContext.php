<?php

declare(strict_types=1);

namespace App\Tests\Behat;

use Behat\MinkExtension\Context\RawMinkContext;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\Security\Core\User\UserProviderInterface;

final class SecurityContext extends RawMinkContext
{
    private $userProvider;
    private $tokenManager;

    public function __construct(UserProviderInterface $userProvider, JWTTokenManagerInterface $tokenManager)
    {
        $this->userProvider = $userProvider;
        $this->tokenManager = $tokenManager;
    }

    /**
     * @AfterScenario
     */
    public function clearCookies(): void
    {
        $this->getSession()->getDriver()->getClient()->getCookieJar()->clear();
    }

    /**
     * @Given I am authenticated as :username
     */
    public function login(string $username): void
    {
        $this->getSession()->getDriver()->getClient()->getCookieJar()->set(new Cookie(
            'bewelcome',
            $this->tokenManager->create($this->userProvider->loadUserByUsername($username))
        ));
    }

    /**
     * @Given I am authenticated as :username with an expired access token
     */
    public function loginWithExpiredAccessToken(string $username): void
    {
        $this->getSession()->getDriver()->getClient()->getCookieJar()->set(new Cookie(
            'bewelcome',
            $this->tokenManager->create($this->userProvider->loadUserByUsername($username)),
            (string) (time() - 10)
        ));
    }
}
