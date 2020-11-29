<?php

declare(strict_types=1);

namespace App\Security\RefreshToken;

use App\Entity\Security\RefreshToken;
use Symfony\Component\Security\Core\User\UserInterface;

interface RefreshTokenStorageInterface
{
    public function findOneByUser(UserInterface $user): ?RefreshToken;

    public function create(UserInterface $user): void;

    public function expireAll(?UserInterface $user = null): void;
}
