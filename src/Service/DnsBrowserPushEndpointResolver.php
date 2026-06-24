<?php

namespace App\Service;

final readonly class DnsBrowserPushEndpointResolver implements BrowserPushEndpointResolverInterface
{
    public function resolve(string $host): array
    {
        $addresses = [];
        $records = dns_get_record($host, \DNS_A | \DNS_AAAA) ?: [];
        foreach ($records as $record) {
            if (isset($record['ip'])) {
                $addresses[] = $record['ip'];
            }
            if (isset($record['ipv6'])) {
                $addresses[] = $record['ipv6'];
            }
        }

        foreach (gethostbynamel($host) ?: [] as $address) {
            $addresses[] = $address;
        }

        return array_values(array_unique($addresses));
    }
}
