<?php

namespace App\Service;

final readonly class BrowserPushEndpointValidator
{
    private const array SUPPORTED_PROVIDER_HOSTS = [
        'fcm.googleapis.com',
        'updates.push.services.mozilla.com',
        'web.push.apple.com',
    ];
    private const array SUPPORTED_PROVIDER_SUFFIXES = [
        '.notify.windows.com',
    ];

    public function __construct(private BrowserPushEndpointResolverInterface $resolver)
    {
    }

    public function getValidatedEndpoint(string $endpoint): ?ValidatedBrowserPushEndpoint
    {
        $parts = parse_url($endpoint);
        if (
            false === $parts
            || !isset($parts['scheme'], $parts['host'])
            || 'https' !== strtolower((string) $parts['scheme'])
            || isset($parts['user'])
            || isset($parts['pass'])
        ) {
            return null;
        }

        $host = $this->normalizeHost((string) $parts['host']);
        if (null === $host) {
            return null;
        }

        if ('localhost' === $host || str_ends_with($host, '.localhost')) {
            return null;
        }

        $port = $this->getPort($parts);
        if (443 !== $port) {
            return null;
        }

        if (filter_var($host, \FILTER_VALIDATE_IP)) {
            return null;
        }

        if (!str_contains($host, '.') || !filter_var($host, \FILTER_VALIDATE_DOMAIN, \FILTER_FLAG_HOSTNAME)) {
            return null;
        }
        if (!$this->isSupportedProviderHost($host)) {
            return null;
        }

        $addresses = $this->resolver->resolve($host);
        if ([] === $addresses) {
            return null;
        }

        foreach ($addresses as $address) {
            if (!$this->isPublicIp($address)) {
                return null;
            }
        }

        return new ValidatedBrowserPushEndpoint(
            $host,
            $port,
            $addresses[0],
            $this->canonicalizeEndpoint($parts, $host, $port)
        );
    }

    private function normalizeHost(string $host): ?string
    {
        $host = strtolower(trim($host, " \t\n\r\0\x0B"));
        $host = rtrim($host, '.');
        if ('' === $host) {
            return null;
        }

        if (filter_var($host, \FILTER_VALIDATE_IP)) {
            return $host;
        }

        if (\function_exists('idn_to_ascii')) {
            $asciiHost = idn_to_ascii($host, \IDNA_DEFAULT, \INTL_IDNA_VARIANT_UTS46);
            if (false === $asciiHost) {
                return null;
            }

            return strtolower($asciiHost);
        }

        return $host;
    }

    private function isPublicIp(string $address): bool
    {
        return false !== filter_var(
            $address,
            \FILTER_VALIDATE_IP,
            \FILTER_FLAG_NO_PRIV_RANGE | \FILTER_FLAG_NO_RES_RANGE
        );
    }

    private function getPort(array $parts): int
    {
        return isset($parts['port']) && \is_int($parts['port']) ? $parts['port'] : 443;
    }

    private function isSupportedProviderHost(string $host): bool
    {
        if (\in_array($host, self::SUPPORTED_PROVIDER_HOSTS, true)) {
            return true;
        }

        foreach (self::SUPPORTED_PROVIDER_SUFFIXES as $suffix) {
            if ($host === substr($suffix, 1) || str_ends_with($host, $suffix)) {
                return true;
            }
        }

        return false;
    }

    private function canonicalizeEndpoint(array $parts, string $host, int $port): string
    {
        $path = $parts['path'] ?? '/';
        $query = isset($parts['query']) ? '?' . $parts['query'] : '';
        $fragment = isset($parts['fragment']) ? '#' . $parts['fragment'] : '';

        return 'https://' . $host . (443 === $port ? '' : ':' . $port) . $path . $query . $fragment;
    }
}
