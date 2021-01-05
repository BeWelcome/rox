<?php

declare(strict_types=1);

namespace App\Tests\Behat;

use App\Repository\RefreshTokenRepository;
use Behat\Behat\Context\Context;
use RuntimeException;
use Symfony\Component\Security\Core\User\UserProviderInterface;

final class RefreshTokenContext implements Context
{
    private $repository;
    private $userProvider;

    public function __construct(RefreshTokenRepository $repository, UserProviderInterface $userProvider)
    {
        $this->repository = $repository;
        $this->userProvider = $userProvider;
    }

    /**
     * @Then a refresh-token has been created on user :username
     */
    public function checkExistingRefreshToken(string $username): void
    {
        $user = $this->userProvider->loadUserByUsername($username);
        if (null === $this->repository->findOneByUser($user)) {
            throw new RuntimeException("No refresh-token exist for user $username.");
        }
    }
}
