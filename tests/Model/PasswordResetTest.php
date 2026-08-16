<?php

namespace App\Tests\Model;

use App\Entity\Member;
use App\Entity\PasswordReset;
use App\Model\PasswordModel;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Mockery;
use PHPUnit\Framework\TestCase;
use Symfony\Component\PasswordHasher\Hasher\PasswordHasherFactoryInterface;
use Symfony\Component\PasswordHasher\PasswordHasherInterface;

class PasswordResetTest extends TestCase
{
    private EntityManagerInterface $entityManager;
    private PasswordHasherFactoryInterface $passwordHasherFactory;
    private PasswordModel $passwordModel;

    public function setUp(): void
    {
        $this->entityManager = Mockery::mock(EntityManagerInterface::class);
        $this->passwordHasherFactory = Mockery::mock(PasswordHasherFactoryInterface::class);
        $this->passwordModel = new PasswordModel($this->entityManager, $this->passwordHasherFactory);
    }

    public function tearDown(): void
    {
        Mockery::close();
    }

    public function testGenerateTokenReturnsHexString(): void
    {
        $member = new Member();
        $repo = Mockery::mock(EntityRepository::class);
        $repo->shouldReceive('findBy')->with(['member' => $member])->andReturn([]);
        $this->entityManager->shouldReceive('getRepository')->with(PasswordReset::class)->andReturn($repo);
        $this->entityManager->shouldReceive('persist')->once();
        $this->entityManager->shouldReceive('flush')->twice();

        $token = $this->passwordModel->generatePasswordResetToken($member);

        $this->assertSame(64, strlen($token));
        $this->assertMatchesRegularExpression('/^[0-9a-f]{64}$/', $token);
    }

    public function testGenerateTokenIsUniquePerCall(): void
    {
        $member = new Member();
        $repo = Mockery::mock(EntityRepository::class);
        $repo->shouldReceive('findBy')->with(['member' => $member])->twice()->andReturn([]);
        $this->entityManager->shouldReceive('getRepository')->with(PasswordReset::class)->twice()->andReturn($repo);
        $this->entityManager->shouldReceive('persist')->twice();
        $this->entityManager->shouldReceive('flush')->times(4);

        $token1 = $this->passwordModel->generatePasswordResetToken($member);
        $token2 = $this->passwordModel->generatePasswordResetToken($member);

        $this->assertNotSame($token1, $token2);
    }

    public function testGenerateTokenRemovesExistingTokensFirst(): void
    {
        $member = new Member();
        $existing = new PasswordReset();
        $repo = Mockery::mock(EntityRepository::class);
        $repo->shouldReceive('findBy')->with(['member' => $member])->andReturn([$existing]);
        $this->entityManager->shouldReceive('getRepository')->with(PasswordReset::class)->andReturn($repo);
        $this->entityManager->shouldReceive('remove')->once()->with($existing);
        $this->entityManager->shouldReceive('persist')->once();
        $this->entityManager->shouldReceive('flush')->twice();

        $this->passwordModel->generatePasswordResetToken($member);
    }

    public function testRemoveTokensDeletesAll(): void
    {
        $member = new Member();
        $token1 = new PasswordReset();
        $token2 = new PasswordReset();
        $repo = Mockery::mock(EntityRepository::class);
        $repo->shouldReceive('findBy')->with(['member' => $member])->andReturn([$token1, $token2]);
        $this->entityManager->shouldReceive('getRepository')->with(PasswordReset::class)->andReturn($repo);
        $this->entityManager->shouldReceive('remove')->once()->with($token1);
        $this->entityManager->shouldReceive('remove')->once()->with($token2);
        $this->entityManager->shouldReceive('flush')->once();

        $this->passwordModel->removePasswordResetTokens($member);
    }

    public function testRemoveTokensFlushesEvenWhenNoneExist(): void
    {
        $member = new Member();
        $repo = Mockery::mock(EntityRepository::class);
        $repo->shouldReceive('findBy')->with(['member' => $member])->andReturn([]);
        $this->entityManager->shouldReceive('getRepository')->with(PasswordReset::class)->andReturn($repo);
        $this->entityManager->shouldReceive('remove')->never();
        $this->entityManager->shouldReceive('flush')->once();

        $this->passwordModel->removePasswordResetTokens($member);
    }

    public function testGetPasswordHashDelegatesToHasher(): void
    {
        $member = new Member();
        $hasher = Mockery::mock(PasswordHasherInterface::class);
        $hasher->shouldReceive('hash')->with('secret')->andReturn('$2y$13$hashed');
        $this->passwordHasherFactory->shouldReceive('getPasswordHasher')->with($member)->andReturn($hasher);

        $hash = $this->passwordModel->getPasswordHash($member, 'secret');

        $this->assertSame('$2y$13$hashed', $hash);
    }
}
