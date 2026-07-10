<?php

namespace App\Tests\Service;

use App\Service\BrowserPushEndpointResolverInterface;
use App\Service\BrowserPushEndpointValidator;
use PHPUnit\Framework\TestCase;

class BrowserPushEndpointValidatorTest extends TestCase
{
    public function testRejectsRawPublicIpEndpoint(): void
    {
        self::assertNull($this->validator()->getValidatedEndpoint('https://93.184.216.34/push'));
    }

    public function testAcceptsAllowedProviderEndpoint(): void
    {
        $validator = $this->validator([
            'fcm.googleapis.com' => ['142.250.185.10'],
        ]);

        self::assertNotNull($validator->getValidatedEndpoint('https://fcm.googleapis.com/fcm/send/push-token'));
    }

    public function testAcceptsAppleProviderEndpoint(): void
    {
        $validator = $this->validator([
            'web.push.apple.com' => ['17.253.144.10'],
        ]);

        self::assertNotNull($validator->getValidatedEndpoint('https://web.push.apple.com/QH0/push-token'));
    }

    public function testRejectsArbitraryPublicDomainEndpoint(): void
    {
        $validator = $this->validator([
            'push.example.org' => ['93.184.216.34'],
        ]);

        self::assertNull($validator->getValidatedEndpoint('https://push.example.org/push'));
    }

    public function testCanonicalizesAcceptedEndpoint(): void
    {
        $validator = $this->validator([
            'fcm.googleapis.com' => ['142.250.185.10'],
        ]);

        $endpoint = $validator->getValidatedEndpoint('https://FCM.googleapis.com./send?token=123');

        self::assertNotNull($endpoint);
        self::assertSame('fcm.googleapis.com', $endpoint->getHost());
        self::assertSame('https://fcm.googleapis.com/send?token=123', $endpoint->getCanonicalEndpoint());
    }

    public function testRejectsMalformedEndpoint(): void
    {
        self::assertNull($this->validator()->getValidatedEndpoint('not-a-url'));
    }

    public function testRejectsNonHttpsEndpoint(): void
    {
        self::assertNull($this->validator()->getValidatedEndpoint('http://push.example.org/push'));
    }

    public function testRejectsEndpointWithUserInfo(): void
    {
        self::assertNull($this->validator()->getValidatedEndpoint('https://user:password@push.example.org/push'));
    }

    public function testRejectsEndpointWithFragment(): void
    {
        self::assertNull($this->validator()->getValidatedEndpoint('https://fcm.googleapis.com/push#duplicate'));
    }

    public function testRejectsNonDefaultHttpsPort(): void
    {
        self::assertNull($this->validator()->getValidatedEndpoint('https://push.example.org:8443/push'));
    }

    public function testRejectsLocalhostEndpoint(): void
    {
        self::assertNull($this->validator()->getValidatedEndpoint('https://localhost/push'));
    }

    public function testRejectsPrivateIpEndpoint(): void
    {
        self::assertNull($this->validator()->getValidatedEndpoint('https://10.0.0.1/push'));
    }

    public function testRejectsDomainResolvingToPrivateIp(): void
    {
        $validator = $this->validator([
            'fcm.googleapis.com' => ['142.250.185.10', '10.0.0.1'],
        ]);

        self::assertNull($validator->getValidatedEndpoint('https://fcm.googleapis.com/push'));
    }

    public function testRejectsUnresolvableDomain(): void
    {
        $validator = $this->validator([
            'fcm.googleapis.com' => [],
        ]);

        self::assertNull($validator->getValidatedEndpoint('https://fcm.googleapis.com/push'));
        self::assertTrue($validator->isSupportedEndpoint('https://fcm.googleapis.com/push'));
    }

    private function validator(array $records = []): BrowserPushEndpointValidator
    {
        $resolver = $this->createStub(BrowserPushEndpointResolverInterface::class);
        $resolver->method('resolve')->willReturnCallback(static fn (string $host): array => $records[$host] ?? []);

        return new BrowserPushEndpointValidator($resolver);
    }
}
