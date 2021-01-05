<?php

declare(strict_types=1);

namespace App\Security\RefreshToken;

use App\Entity\Security\RefreshToken;
use App\Repository\RefreshTokenRepository;
use DateTimeImmutable;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityNotFoundException;
use Doctrine\Persistence\ManagerRegistry;
use InvalidArgumentException;
use Symfony\Component\Security\Core\User\UserInterface;

final class DoctrineRefreshTokenStorage implements RefreshTokenStorageInterface
{
    private $registry;
    private $ttl;

    public function __construct(ManagerRegistry $registry, int $refreshTokenTtl)
    {
        $this->registry = $registry;
        $this->ttl = $refreshTokenTtl;
    }

    public function findOneByUser(UserInterface $user): ?RefreshToken
    {
        $repository = $this->getEntityManager()->getRepository(RefreshToken::class);
        if (!$repository instanceof RefreshTokenRepository) {
            throw new InvalidArgumentException('RefreshToken entity repository must be instance of ' . RefreshTokenRepository::class);
        }

        return $repository->findOneByUser($user);
    }

    public function create(UserInterface $user): void
    {
        $refreshToken = new RefreshToken();
        $refreshToken->setCreatedAt(new DateTimeImmutable());
        $refreshToken->setExpiresAt(new DateTimeImmutable("$this->ttl seconds"));
        $refreshToken->setUser($user);

        $em = $this->getEntityManager();
        $em->persist($refreshToken);
        $em->flush($refreshToken);
    }

    public function expireAll(?UserInterface $user = null): void
    {
        $em = $this->getEntityManager();
        $repository = $em->getRepository(RefreshToken::class);
        /** @var RefreshToken[] $refreshTokens */
        $refreshTokens = $user ? $repository->findBy(['user' => $user]) : $repository->findAll();

        foreach ($refreshTokens as $refreshToken) {
            $refreshToken->setExpiresAt(new DateTimeImmutable());
            $em->persist($refreshToken);
        }

        $em->flush($refreshTokens);
    }

    private function getEntityManager(): EntityManager
    {
        $em = $this->registry->getManagerForClass(RefreshToken::class);
        if (!$em instanceof EntityManager) {
            throw new EntityNotFoundException('No entity found for class RefreshToken::class.');
        }

        return $em;
    }
}
