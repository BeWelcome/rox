<?php

namespace App\Service;

interface BrowserPushEndpointResolverInterface
{
    /**
     * @return string[]
     */
    public function resolve(string $host): array;
}
