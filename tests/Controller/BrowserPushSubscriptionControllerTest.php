<?php

namespace App\Tests\Controller;

use App\Entity\BrowserPushNotification;
use App\Entity\BrowserPushSubscription;
use App\Entity\Member;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

#[Group('integration')]
/**
 * @infection-ignore-all
 */
class BrowserPushSubscriptionControllerTest extends WebTestCase
{
    private const string CSRF_TOKEN_VALUE = 'csrf-token';

    public function testSubscribeRequiresAuthentication(): void
    {
        $client = static::createClient();

        $this->requestJson($client, 'POST', '/notifications/browser/subscriptions', $this->validSubscription('auth'));

        $this->assertResponseRedirects('/login');
    }

    public function testSubscribeRejectsMissingCsrfToken(): void
    {
        $client = static::createClient();
        $this->loginMember($client, 'member-2');

        $this->requestJson($client, 'POST', '/notifications/browser/subscriptions', $this->validSubscription('csrf'));

        $this->assertResponseStatusCodeSame(403);
    }

    public function testSubscribeRejectsMalformedJson(): void
    {
        $client = static::createClient();
        $this->loginMember($client, 'member-2');

        $this->requestJson($client, 'POST', '/notifications/browser/subscriptions', '{', $this->csrfToken());

        $this->assertResponseStatusCodeSame(400);
    }

    public function testSubscribeRejectsInvalidEndpoint(): void
    {
        $client = static::createClient();
        $this->loginMember($client, 'member-2');

        $this->requestJson(
            $client,
            'POST',
            '/notifications/browser/subscriptions',
            $this->validSubscription('invalid-endpoint', 'https://127.0.0.1/push'),
            $this->csrfToken()
        );

        $this->assertResponseStatusCodeSame(400);
        $this->assertJsonResponseSame(['error' => 'invalid_endpoint'], $client);
    }

    public function testSubscribeCreatesAndUpdatesCurrentMemberEndpoint(): void
    {
        $client = static::createClient();
        $member = $this->loginMember($client, 'member-2');
        $endpoint = $this->endpoint('upsert');
        $payload = $this->validSubscription('upsert', $endpoint);

        $this->requestJson($client, 'POST', '/notifications/browser/subscriptions', $payload, $this->csrfToken());

        $this->assertResponseStatusCodeSame(201);
        $subscription = $this->findSubscription($endpoint);
        self::assertNotNull($subscription);
        self::assertSame($member->getId(), $subscription->getMember()->getId());
        self::assertSame($payload['keys']['p256dh'], $subscription->getPublicKey());

        $payload = $this->validSubscription('upsert-updated', $endpoint);
        $this->requestJson($client, 'POST', '/notifications/browser/subscriptions', $payload, $this->csrfToken());

        $this->assertResponseIsSuccessful();
        $subscriptions = $this->findSubscriptions($endpoint);
        self::assertCount(1, $subscriptions);
        self::assertSame($payload['keys']['auth'], $subscriptions[0]->getAuthToken());
    }

    public function testSubscribeStoresCanonicalEndpoint(): void
    {
        $client = static::createClient();
        $this->loginMember($client, 'member-2');

        $this->requestJson(
            $client,
            'POST',
            '/notifications/browser/subscriptions',
            $this->validSubscription('canonical', 'https://FCM.googleapis.com./canonical'),
            $this->csrfToken()
        );

        $this->assertResponseStatusCodeSame(201);
        self::assertNotNull($this->findSubscription('https://fcm.googleapis.com/canonical'));
        self::assertNull($this->findSubscription('https://FCM.googleapis.com./canonical'));
    }

    public function testSubscribeRejectsInvalidKeyMaterial(): void
    {
        $client = static::createClient();
        $this->loginMember($client, 'member-2');

        $payload = $this->validSubscription('invalid-public-key');
        $payload['keys']['p256dh'] = $this->base64Url(str_repeat('p', 64));
        $this->requestJson(
            $client,
            'POST',
            '/notifications/browser/subscriptions',
            $payload,
            $this->csrfToken()
        );

        $this->assertResponseStatusCodeSame(400);
        $this->assertJsonResponseSame(['error' => 'invalid_subscription'], $client);

        $payload = $this->validSubscription('invalid-auth-token');
        $payload['keys']['auth'] = $this->base64Url(str_repeat('a', 15));
        $this->requestJson(
            $client,
            'POST',
            '/notifications/browser/subscriptions',
            $payload,
            $this->csrfToken()
        );

        $this->assertResponseStatusCodeSame(400);
        $this->assertJsonResponseSame(['error' => 'invalid_subscription'], $client);
    }

    public function testSubscribeRejectsUnsupportedContentEncoding(): void
    {
        $client = static::createClient();
        $this->loginMember($client, 'member-2');

        $payload = $this->validSubscription('invalid-content-encoding');
        $payload['contentEncoding'] = 'br';
        $this->requestJson(
            $client,
            'POST',
            '/notifications/browser/subscriptions',
            $payload,
            $this->csrfToken()
        );

        $this->assertResponseStatusCodeSame(400);
        $this->assertJsonResponseSame(['error' => 'invalid_subscription'], $client);
    }

    public function testSubscribeRejectsOverlongEndpoint(): void
    {
        $client = static::createClient();
        $this->loginMember($client, 'member-2');

        $this->requestJson(
            $client,
            'POST',
            '/notifications/browser/subscriptions',
            $this->validSubscription(
                'overlong-endpoint',
                'https://fcm.googleapis.com/' . str_repeat('a', 2048)
            ),
            $this->csrfToken()
        );

        $this->assertResponseStatusCodeSame(400);
        $this->assertJsonResponseSame(['error' => 'invalid_subscription'], $client);
    }

    public function testSubscribeTransfersEndpointToCurrentMember(): void
    {
        $client = static::createClient();
        $endpoint = $this->endpoint('transfer');
        $payload = $this->validSubscription('transfer', $endpoint);

        $this->loginMember($client, 'member-2');
        $this->requestJson(
            $client,
            'POST',
            '/notifications/browser/subscriptions',
            $payload,
            $this->csrfToken()
        );
        $this->assertResponseStatusCodeSame(201);
        $previousSubscription = $this->findSubscription($endpoint);
        self::assertNotNull($previousSubscription);
        $previousSubscriptionId = $previousSubscription->getId();

        $newOwner = $this->loginMember($client, 'member-5');
        $this->requestJson(
            $client,
            'POST',
            '/notifications/browser/subscriptions',
            $payload,
            $this->csrfToken()
        );

        $this->assertResponseIsSuccessful();
        $subscriptions = $this->findSubscriptions($endpoint);
        self::assertCount(1, $subscriptions);
        self::assertSame($newOwner->getId(), $subscriptions[0]->getMember()->getId());
        self::assertSame($previousSubscriptionId, $subscriptions[0]->getId());
    }

    public function testSubscribeDoesNotTransferEndpointWithoutMatchingKeyMaterial(): void
    {
        $client = static::createClient();
        $endpoint = $this->endpoint('protected-transfer');
        $originalOwner = $this->loginMember($client, 'member-2');
        $this->requestJson(
            $client,
            'POST',
            '/notifications/browser/subscriptions',
            $this->validSubscription('protected-transfer', $endpoint),
            $this->csrfToken()
        );
        $this->assertResponseStatusCodeSame(201);

        $this->loginMember($client, 'member-5');
        $this->requestJson(
            $client,
            'POST',
            '/notifications/browser/subscriptions',
            $this->validSubscription('different-keys', $endpoint),
            $this->csrfToken()
        );

        $this->assertResponseStatusCodeSame(409);
        $this->assertJsonResponseSame(['error' => 'endpoint_owned'], $client);
        self::assertSame($originalOwner->getId(), $this->findSubscription($endpoint)?->getMember()->getId());
    }

    public function testUnsubscribeRemovesCurrentBrowserEndpointAcrossAccountSwitch(): void
    {
        $client = static::createClient();
        $endpoint = $this->endpoint('unsubscribe');
        $payload = $this->validSubscription('unsubscribe', $endpoint);
        $this->loginMember($client, 'member-2');
        $this->requestJson($client, 'POST', '/notifications/browser/subscriptions', $payload, $this->csrfToken());
        $this->assertResponseStatusCodeSame(201);

        $this->loginMember($client, 'member-5');
        $this->requestJson($client, 'DELETE', '/notifications/browser/subscriptions', $payload, $this->csrfToken());

        $this->assertResponseStatusCodeSame(204);
        self::assertNull($this->findSubscription($endpoint));
    }

    public function testUnsubscribeRejectsMismatchedKeyMaterial(): void
    {
        $client = static::createClient();
        $endpoint = $this->endpoint('unsubscribe-protected');
        $this->loginMember($client, 'member-2');
        $this->requestJson(
            $client,
            'POST',
            '/notifications/browser/subscriptions',
            $this->validSubscription('unsubscribe-protected', $endpoint),
            $this->csrfToken()
        );

        $this->requestJson(
            $client,
            'DELETE',
            '/notifications/browser/subscriptions',
            $this->validSubscription('different-keys', $endpoint),
            $this->csrfToken()
        );

        $this->assertResponseStatusCodeSame(409);
        self::assertNotNull($this->findSubscription($endpoint));
    }

    public function testUnsubscribeUsesCanonicalEndpoint(): void
    {
        $client = static::createClient();
        $endpoint = 'https://FCM.googleapis.com./canonical-unsubscribe';
        $canonicalEndpoint = 'https://fcm.googleapis.com/canonical-unsubscribe';
        $payload = $this->validSubscription('canonical-unsubscribe', $endpoint);
        $this->loginMember($client, 'member-2');
        $this->requestJson($client, 'POST', '/notifications/browser/subscriptions', $payload, $this->csrfToken());
        $this->assertResponseStatusCodeSame(201);

        $this->requestJson($client, 'DELETE', '/notifications/browser/subscriptions', $payload, $this->csrfToken());

        $this->assertResponseStatusCodeSame(204);
        self::assertNull($this->findSubscription($canonicalEndpoint));
    }

    public function testSubscribePrunesOldestMemberSubscriptions(): void
    {
        $client = static::createClient();
        $member = $this->loginMember($client, 'member-2');

        for ($index = 0; $index < 11; ++$index) {
            $endpoint = $this->endpoint('subscription-cap-' . $index);
            $this->requestJson(
                $client,
                'POST',
                '/notifications/browser/subscriptions',
                $this->validSubscription('subscription-cap-' . $index, $endpoint),
                $this->csrfToken()
            );
            $this->assertResponseStatusCodeSame(201);
        }

        $subscriptions = $this->getEntityManager()
            ->getRepository(BrowserPushSubscription::class)
            ->findBy(['member' => $member])
        ;
        self::assertCount(10, $subscriptions);
        self::assertNull($this->findSubscription($this->endpoint('subscription-cap-0')));
        self::assertNotNull($this->findSubscription($this->endpoint('subscription-cap-10')));
    }

    public function testBrowserPushPreferenceNoDeletesAllCurrentMemberSubscriptions(): void
    {
        $client = static::createClient();
        $this->ensureBrowserPushPreferenceExists();
        $endpoint = $this->endpoint('preference-no-delete-all');
        $member = $this->loginMember($client, 'member-2');

        $this->requestJson(
            $client,
            'POST',
            '/notifications/browser/subscriptions',
            $this->validSubscription('preference-no-delete-all', $endpoint),
            $this->csrfToken()
        );
        $this->assertResponseStatusCodeSame(201);

        $this->requestPreferenceUpdate($client, [
            'member' => $member->getUsername(),
            'preference' => 'PreferenceBrowserNotifications',
            'value' => 'No',
        ]);

        $this->assertResponseIsSuccessful();
        self::assertCount(
            0,
            $this->getEntityManager()
                ->getRepository(BrowserPushSubscription::class)
                ->findBy(['member' => $member])
        );
    }

    public function testBrowserPushPreferenceOpenOnlyDeletesAllCurrentMemberSubscriptions(): void
    {
        $client = static::createClient();
        $this->ensureBrowserPushPreferenceExists();
        $endpoint = $this->endpoint('preference-open-only-delete-all');
        $member = $this->loginMember($client, 'member-2');

        $this->requestJson(
            $client,
            'POST',
            '/notifications/browser/subscriptions',
            $this->validSubscription('preference-open-only-delete-all', $endpoint),
            $this->csrfToken()
        );
        $this->assertResponseStatusCodeSame(201);

        $this->requestPreferenceUpdate($client, [
            'member' => $member->getUsername(),
            'preference' => 'PreferenceBrowserNotifications',
            'value' => 'OpenOnly',
        ]);

        $this->assertResponseIsSuccessful();
        self::assertCount(
            0,
            $this->getEntityManager()
                ->getRepository(BrowserPushSubscription::class)
                ->findBy(['member' => $member])
        );
    }

    public function testPreferenceUpdateRequiresCsrfToken(): void
    {
        $client = static::createClient();
        $member = $this->loginMember($client, 'member-2');

        $client->request('POST', '/members/update/preference', [
            'member' => $member->getUsername(),
            'preference' => 'PreferenceBrowserNotifications',
            'value' => 'No',
        ]);

        $this->assertResponseStatusCodeSame(403);
    }

    public function testSubscribeRejectsWhenBrowserPushPreferenceIsNo(): void
    {
        $client = static::createClient();
        $preferenceId = $this->ensureBrowserPushPreferenceExists();
        $member = $this->loginMember($client, 'member-2');
        $this->setBrowserPushPreference($member, $preferenceId, 'No');

        $this->requestJson(
            $client,
            'POST',
            '/notifications/browser/subscriptions',
            $this->validSubscription('preference-no-reject'),
            $this->csrfToken()
        );

        $this->assertResponseStatusCodeSame(409);
        self::assertSame(
            ['error' => 'preference_disabled'],
            json_decode($client->getResponse()->getContent(), true, 512, \JSON_THROW_ON_ERROR)
        );
    }

    public function testSubscribeRejectsWhenBrowserPushPreferenceIsOpenOnly(): void
    {
        $client = static::createClient();
        $preferenceId = $this->ensureBrowserPushPreferenceExists();
        $member = $this->loginMember($client, 'member-2');
        $this->setBrowserPushPreference($member, $preferenceId, 'OpenOnly');

        $this->requestJson(
            $client,
            'POST',
            '/notifications/browser/subscriptions',
            $this->validSubscription('preference-open-only-reject'),
            $this->csrfToken()
        );

        $this->assertResponseStatusCodeSame(409);
        self::assertSame(
            ['error' => 'preference_disabled'],
            json_decode($client->getResponse()->getContent(), true, 512, \JSON_THROW_ON_ERROR)
        );
    }

    public function testUnreadCountReturnsOpenOnlyBrowserNotificationForCurrentMember(): void
    {
        $client = static::createClient();
        $preferenceId = $this->ensureBrowserPushPreferenceExists();
        $member = $this->loginMember($client, 'member-2');
        $this->setBrowserPushPreference($member, $preferenceId, 'OpenOnly');
        $member = $this->reloadMember('member-2');
        $otherMember = $this->reloadMember('member-5');

        $notification = $this->storeOpenOnlyNotification($member, 'sender', '/conversation/123');
        $this->storeOpenOnlyNotification($otherMember, 'other-sender', '/conversation/999');

        $client->request('POST', '/count/conversations/unread?browserNotificationSince=0');

        $this->assertResponseIsSuccessful();
        $response = json_decode($client->getResponse()->getContent(), true, 512, \JSON_THROW_ON_ERROR);
        self::assertSame($member->getId(), $response['browserNotification']['memberId']);
        self::assertSame($notification->getId(), $response['browserNotification']['latestId']);
        self::assertCount(1, $response['browserNotification']['notifications']);
        self::assertSame($notification->getId(), $response['browserNotification']['notifications'][0]['id']);
        self::assertSame('message', $response['browserNotification']['notifications'][0]['type']);
        self::assertSame('/conversation/123', $response['browserNotification']['notifications'][0]['url']);
        self::assertArrayHasKey('title', $response['browserNotification']['notifications'][0]);
        self::assertArrayHasKey('body', $response['browserNotification']['notifications'][0]);
        self::assertStringNotContainsString('private', json_encode(
            $response['browserNotification']['notifications'][0],
            \JSON_THROW_ON_ERROR
        ));
    }

    public function testUnreadCountInitializesOpenOnlyCursorWithoutReturningClosedTabBacklog(): void
    {
        $client = static::createClient();
        $preferenceId = $this->ensureBrowserPushPreferenceExists();
        $member = $this->loginMember($client, 'member-2');
        $this->setBrowserPushPreference($member, $preferenceId, 'OpenOnly');
        $member = $this->reloadMember('member-2');
        $latestNotification = null;
        for ($index = 0; $index < 6; ++$index) {
            $latestNotification = $this->storeOpenOnlyNotification(
                $member,
                'sender-' . $index,
                '/conversation/' . $index
            );
        }

        $client->request('POST', '/count/conversations/unread');

        $this->assertResponseIsSuccessful();
        $response = json_decode($client->getResponse()->getContent(), true, 512, \JSON_THROW_ON_ERROR);
        self::assertSame($latestNotification?->getId(), $response['browserNotification']['latestId']);
        self::assertSame([], $response['browserNotification']['notifications']);
    }

    private function requestJson(
        KernelBrowser $client,
        string $method,
        string $uri,
        array|string $payload,
        ?string $csrfToken = null,
    ): void {
        $server = [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_ACCEPT' => 'application/json',
        ];
        if (null !== $csrfToken) {
            $server['HTTP_X_CSRF_TOKEN'] = $csrfToken;
            $server['HTTP_SEC_FETCH_SITE'] = 'same-origin';
        }

        $client->request(
            $method,
            $uri,
            [],
            [],
            $server,
            \is_array($payload) ? json_encode($payload, \JSON_THROW_ON_ERROR) : $payload
        );
    }

    private function assertJsonResponseSame(array $expected, KernelBrowser $client): void
    {
        self::assertSame(
            $expected,
            json_decode($client->getResponse()->getContent(), true, 512, \JSON_THROW_ON_ERROR)
        );
    }

    private function requestPreferenceUpdate(KernelBrowser $client, array $parameters): void
    {
        $client->request('POST', '/members/update/preference', $parameters, [], [
            'HTTP_X_CSRF_TOKEN' => $this->csrfToken(),
            'HTTP_SEC_FETCH_SITE' => 'same-origin',
        ]);
    }

    private function validSubscription(string $id, ?string $endpoint = null): array
    {
        return [
            'endpoint' => $endpoint ?? $this->endpoint($id),
            'expirationTime' => null,
            'keys' => [
                'p256dh' => $this->base64Url(str_pad(substr($id, 0, 65), 65, 'p')),
                'auth' => $this->base64Url(str_pad(substr($id, 0, 16), 16, 'a')),
            ],
            'contentEncoding' => 'aes128gcm',
        ];
    }

    private function base64Url(string $value): string
    {
        return rtrim(strtr(base64_encode($value), '+/', '-_'), '=');
    }

    private function endpoint(string $id): string
    {
        return 'https://fcm.googleapis.com/fcm/send/browser-push-test-' . $id;
    }

    private function csrfToken(): string
    {
        return self::CSRF_TOKEN_VALUE;
    }

    private function loginMember(KernelBrowser $client, string $username): Member
    {
        $member = $this->reloadMember($username);
        $this->setBrowserPushPreference($member, $this->ensureBrowserPushPreferenceExists(), 'Always');
        $member = $this->reloadMember($username);
        $client->loginUser($member);

        return $member;
    }

    private function reloadMember(string $username): Member
    {
        $entityManager = $this->getEntityManager();
        $member = $entityManager->getRepository(Member::class)->findOneBy(['username' => $username]);
        $this->assertInstanceOf(Member::class, $member);

        return $member;
    }

    private function findSubscription(string $endpoint): ?BrowserPushSubscription
    {
        return $this->getEntityManager()
            ->getRepository(BrowserPushSubscription::class)
            ->findOneBy(['endpointHash' => BrowserPushSubscription::hashEndpoint($endpoint)])
        ;
    }

    /**
     * @return BrowserPushSubscription[]
     */
    private function findSubscriptions(string $endpoint): array
    {
        return $this->getEntityManager()
            ->getRepository(BrowserPushSubscription::class)
            ->findBy(['endpointHash' => BrowserPushSubscription::hashEndpoint($endpoint)])
        ;
    }

    private function getEntityManager(): EntityManagerInterface
    {
        return static::getContainer()->get(EntityManagerInterface::class);
    }

    private function ensureBrowserPushPreferenceExists(): int
    {
        $connection = $this->getEntityManager()->getConnection();
        $preferenceId = $connection->fetchOne(
            'SELECT id FROM preferences WHERE codeName = ?',
            ['PreferenceBrowserNotifications']
        );
        if (false !== $preferenceId) {
            $connection->update('preferences', [
                'DefaultValue' => 'No',
                'PossibleValues' => 'No;OpenOnly;Always',
            ], ['id' => $preferenceId]);

            return (int) $preferenceId;
        }

        $now = new DateTimeImmutable()->format('Y-m-d H:i:s');
        $connection->insert('preferences', [
            'position' => 56,
            'codeName' => 'PreferenceBrowserNotifications',
            'codeDescription' => 'BrowserNotificationsDesc',
            'Description' => 'This preference stores if the member wants browser push notifications.',
            'created' => $now,
            'DefaultValue' => 'No',
            'PossibleValues' => 'No;OpenOnly;Always',
            'Status' => 'Normal',
        ]);

        return (int) $connection->lastInsertId();
    }

    private function setBrowserPushPreference(Member $member, int $preferenceId, string $value): void
    {
        $connection = $this->getEntityManager()->getConnection();
        $connection->delete('memberspreferences', [
            'IdMember' => $member->getId(),
            'IdPreference' => $preferenceId,
        ]);
        $now = new DateTimeImmutable()->format('Y-m-d H:i:s');
        $connection->insert('memberspreferences', [
            'IdMember' => $member->getId(),
            'IdPreference' => $preferenceId,
            'Value' => $value,
            'created' => $now,
            'updated' => $now,
        ]);
        $this->getEntityManager()->clear();
        $clientMember = $this->reloadMember($member->getUsername());
        self::assertSame($member->getId(), $clientMember->getId());
    }

    private function storeOpenOnlyNotification(Member $member, string $sender, string $url): BrowserPushNotification
    {
        $notification = new BrowserPushNotification()
            ->setReceiver($member)
            ->setStatus(BrowserPushNotification::STATUS_OPEN_ONLY)
            ->setType('message')
            ->setSenderUsername($sender)
            ->setUrl($url)
            ->setLastError('private text must not leak')
        ;
        $entityManager = $this->getEntityManager();
        $entityManager->persist($notification);
        $entityManager->flush();

        return $notification;
    }
}
