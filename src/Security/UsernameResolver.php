<?php

declare(strict_types=1);

namespace App\Security;

use Anyx\LoginGateBundle\Service\UsernameResolverInterface;
use Symfony\Component\HttpFoundation\Request;

final class UsernameResolver implements UsernameResolverInterface
{
    /**
     * {@inheritdoc}
     */
    public function resolve(Request $request): ?string
    {
        $requestData = json_decode($request->getContent(), true);

        return \is_array($requestData) && \array_key_exists('username', $requestData) ? $requestData['username'] : null;
    }
}
