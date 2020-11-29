<?php

declare(strict_types=1);

namespace App\Security\RefreshToken;

use App\Entity\User;
use Lexik\Bundle\JWTAuthenticationBundle\Exception\InvalidPayloadException;
use Lexik\Bundle\JWTAuthenticationBundle\Exception\JWTDecodeFailureException;
use Lexik\Bundle\JWTAuthenticationBundle\Exception\UserNotFoundException;
use Lexik\Bundle\JWTAuthenticationBundle\Security\Authentication\Token\PreAuthenticationJWTUserToken;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

final class JWTTokenManager implements JWTTokenManagerInterface
{
    private $decorated;
    private $userProvider;
    private $storage;

    public function __construct(JWTTokenManagerInterface $decorated, UserProviderInterface $userProvider, RefreshTokenStorageInterface $storage)
    {
        $this->decorated = $decorated;
        $this->userProvider = $userProvider;
        $this->storage = $storage;
    }

    /**
     * {@inheritdoc}
     *
     * @param User $user
     */
    public function create(UserInterface $user): string
    {
        $this->storage->expireAll($user);
        $this->storage->create($user);

        return $this->decorated->create($user);
    }

    /**
     * {@inheritdoc}
     *
     * @return string[]|false
     */
    public function decode(TokenInterface $token)
    {
        try {
            return $this->decorated->decode($token);
        } catch (JWTDecodeFailureException $exception) {
            if (JWTDecodeFailureException::EXPIRED_TOKEN !== $exception->getReason()) {
                throw $exception;
            }

            $payload = $exception->getPayload();
            $idClaim = $this->getUserIdClaim();

            if (!isset($payload[$idClaim])) {
                throw new InvalidPayloadException($idClaim);
            }

            $identity = $payload[$idClaim];

            try {
                /** @var User $user */
                $user = $this->userProvider->loadUserByUsername($identity);
            } catch (UsernameNotFoundException $e) {
                throw new UserNotFoundException($idClaim, $identity);
            }

            $refreshToken = $this->storage->findOneByUser($user);
            if (!$refreshToken || $refreshToken->isExpired()) {
                throw $exception;
            }

            return $this->decorated->decode(new PreAuthenticationJWTUserToken($this->create($user)));
        }
    }

    /**
     * {@inheritdoc}
     *
     * @param string $field
     */
    public function setUserIdentityField($field): void
    {
        $this->decorated->setUserIdentityField($field);
    }

    /**
     * {@inheritdoc}
     */
    public function getUserIdentityField(): string
    {
        return $this->decorated->getUserIdentityField();
    }

    /**
     * {@inheritdoc}
     */
    public function getUserIdClaim(): string
    {
        return $this->decorated->getUserIdClaim();
    }
}
