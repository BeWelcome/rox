<?php

namespace App\Security\RefreshToken;

use App\Entity\Security\RefreshToken;
use App\Repository\RefreshTokenRepository;
use DateTimeImmutable;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityNotFoundException;
use Doctrine\Persistence\ManagerRegistry;
use InvalidArgumentException;
use Symfony\Component\Security\Core\User\UserInterface;

final readonly class DoctrineRefreshTokenStorage implements RefreshTokenStorageInterface
{
    public function __construct(private ManagerRegistry $registry, private int $ttl)
    {
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
        $em->flush();
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

        $em->flush();
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
