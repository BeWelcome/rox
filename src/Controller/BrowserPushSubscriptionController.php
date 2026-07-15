<?php

namespace App\Controller;

use App\Entity\BrowserPushSubscription;
use App\Entity\Member;
use App\Service\BrowserPushConfig;
use App\Service\BrowserPushEndpointValidator;
use App\Service\BrowserPushPreferenceService;
use Doctrine\ORM\EntityManagerInterface;
use Error;
use JsonException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_USER')]
class BrowserPushSubscriptionController extends AbstractController
{
    public const string CSRF_TOKEN_ID = 'browser_push_subscription';
    private const int MAX_ENDPOINT_LENGTH = 2048;
    private const int PUBLIC_KEY_LENGTH = 65;
    private const int AUTH_TOKEN_LENGTH = 16;
    private const int MAX_SUBSCRIPTIONS_PER_MEMBER = 10;
    private const array SUPPORTED_CONTENT_ENCODINGS = ['aes128gcm', 'aesgcm'];

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly BrowserPushConfig $browserPushConfig,
        private readonly BrowserPushEndpointValidator $endpointValidator,
        private readonly CsrfTokenManagerInterface $csrfTokenManager,
        private readonly BrowserPushPreferenceService $browserPushPreferenceService,
    ) {
    }

    #[Route(path: '/notifications/browser/subscriptions', name: 'browser_push_subscription_upsert', methods: ['POST'])]
    public function subscribe(Request $request): JsonResponse
    {
        $this->denyInvalidCsrf($request);
        if (!$this->browserPushConfig->isConfigured()) {
            return new JsonResponse(['error' => 'not_configured'], JsonResponse::HTTP_SERVICE_UNAVAILABLE);
        }

        $payload = $this->decodePayload($request);
        if (null === $payload) {
            return new JsonResponse(['error' => 'invalid_json'], JsonResponse::HTTP_BAD_REQUEST);
        }

        $endpoint = $payload['endpoint'] ?? null;
        $publicKey = $payload['keys']['p256dh'] ?? null;
        $authToken = $payload['keys']['auth'] ?? null;
        $contentEncoding = $this->getContentEncoding($payload);
        if (
            !\is_string($endpoint)
            || '' === $endpoint
            || !\is_string($publicKey)
            || '' === $publicKey
            || !\is_string($authToken)
            || '' === $authToken
        ) {
            return new JsonResponse(['error' => 'invalid_subscription'], JsonResponse::HTTP_BAD_REQUEST);
        }
        if (!$this->hasValidSubscriptionShape($endpoint, $publicKey, $authToken, $contentEncoding)) {
            return new JsonResponse(['error' => 'invalid_subscription'], JsonResponse::HTTP_BAD_REQUEST);
        }
        $validatedEndpoint = $this->endpointValidator->getValidatedEndpoint($endpoint);
        if (null === $validatedEndpoint) {
            return new JsonResponse(['error' => 'invalid_endpoint'], JsonResponse::HTTP_BAD_REQUEST);
        }
        $endpoint = $validatedEndpoint->getCanonicalEndpoint();

        /** @var Member $member */
        $member = $this->getUser();
        if (!$this->browserPushPreferenceService->isAlways($member)) {
            return new JsonResponse(['error' => 'preference_disabled'], JsonResponse::HTTP_CONFLICT);
        }

        $repository = $this->entityManager->getRepository(BrowserPushSubscription::class);
        $subscription = $repository->findOneBy(['endpointHash' => BrowserPushSubscription::hashEndpoint($endpoint)]);
        $created = false;
        if (
            $subscription instanceof BrowserPushSubscription
            && !$this->isSameMember($subscription->getMember(), $member)
        ) {
            if (!$this->hasMatchingKeyMaterial($subscription, $publicKey, $authToken)) {
                return new JsonResponse(['error' => 'endpoint_owned'], JsonResponse::HTTP_CONFLICT);
            }
        }
        if (!$subscription instanceof BrowserPushSubscription) {
            $subscription = new BrowserPushSubscription();
            $created = true;
        }

        $subscription
            ->setMember($member)
            ->setEndpoint($endpoint)
            ->setPublicKey($publicKey)
            ->setAuthToken($authToken)
            ->setContentEncoding($contentEncoding)
            ->setUserAgent($request->headers->get('User-Agent'))
            ->setLastError(null)
            ->touchLastSeen()
        ;

        $this->entityManager->persist($subscription);
        $this->entityManager->flush();
        $this->pruneMemberSubscriptions($member);

        return new JsonResponse(
            ['status' => $created ? 'created' : 'updated'],
            $created ? JsonResponse::HTTP_CREATED : JsonResponse::HTTP_OK
        );
    }

    #[Route(path: '/notifications/browser/subscriptions', name: 'browser_push_subscription_delete', methods: ['DELETE'])]
    public function unsubscribe(Request $request): JsonResponse
    {
        $this->denyInvalidCsrf($request);
        $payload = $this->decodePayload($request);
        if (null === $payload) {
            return new JsonResponse(['error' => 'invalid_json'], JsonResponse::HTTP_BAD_REQUEST);
        }

        $endpoint = $payload['endpoint'] ?? null;
        $publicKey = $payload['keys']['p256dh'] ?? null;
        $authToken = $payload['keys']['auth'] ?? null;
        if (
            !\is_string($endpoint)
            || '' === $endpoint
            || self::MAX_ENDPOINT_LENGTH < mb_strlen($endpoint)
            || !\is_string($publicKey)
            || self::PUBLIC_KEY_LENGTH !== $this->decodedBase64UrlLength($publicKey)
            || !\is_string($authToken)
            || self::AUTH_TOKEN_LENGTH !== $this->decodedBase64UrlLength($authToken)
        ) {
            return new JsonResponse(['error' => 'invalid_subscription'], JsonResponse::HTTP_BAD_REQUEST);
        }
        $validatedEndpoint = $this->endpointValidator->getValidatedEndpoint($endpoint);
        if (null === $validatedEndpoint) {
            return new JsonResponse(['error' => 'invalid_endpoint'], JsonResponse::HTTP_BAD_REQUEST);
        }
        $endpoint = $validatedEndpoint->getCanonicalEndpoint();

        $subscription = $this->entityManager->getRepository(BrowserPushSubscription::class)->findOneBy([
            'endpointHash' => BrowserPushSubscription::hashEndpoint($endpoint),
        ]);
        if (!$subscription instanceof BrowserPushSubscription) {
            return new JsonResponse(status: JsonResponse::HTTP_NO_CONTENT);
        }
        if (!$this->hasMatchingKeyMaterial($subscription, $publicKey, $authToken)) {
            return new JsonResponse(['error' => 'endpoint_owned'], JsonResponse::HTTP_CONFLICT);
        }

        $this->entityManager->remove($subscription);
        $this->entityManager->flush();

        return new JsonResponse(status: JsonResponse::HTTP_NO_CONTENT);
    }

    private function isSameMember(Member $first, Member $second): bool
    {
        if ($first === $second) {
            return true;
        }

        try {
            return $first->getId() === $second->getId();
        } catch (Error) {
            return false;
        }
    }

    private function hasMatchingKeyMaterial(
        BrowserPushSubscription $subscription,
        string $publicKey,
        string $authToken,
    ): bool {
        return hash_equals($subscription->getPublicKey(), $publicKey)
            && hash_equals($subscription->getAuthToken(), $authToken);
    }

    private function pruneMemberSubscriptions(Member $member): void
    {
        $subscriptions = $this->entityManager->getRepository(BrowserPushSubscription::class)->findBy(
            ['member' => $member],
            ['lastSeen' => 'DESC', 'id' => 'DESC']
        );
        if (\count($subscriptions) <= self::MAX_SUBSCRIPTIONS_PER_MEMBER) {
            return;
        }

        $subscriptionsToRemove = \array_slice($subscriptions, self::MAX_SUBSCRIPTIONS_PER_MEMBER);
        foreach ($subscriptionsToRemove as $subscription) {
            $this->entityManager->remove($subscription);
        }
        $this->entityManager->flush();
    }

    private function denyInvalidCsrf(Request $request): void
    {
        $token = $request->headers->get('X-CSRF-Token', '');
        if (!$this->csrfTokenManager->isTokenValid(new CsrfToken(self::CSRF_TOKEN_ID, $token))) {
            throw new AccessDeniedException('Invalid CSRF token.');
        }
    }

    private function decodePayload(Request $request): ?array
    {
        try {
            $payload = json_decode($request->getContent(), true, 512, \JSON_THROW_ON_ERROR);
        } catch (JsonException) {
            return null;
        }

        return \is_array($payload) ? $payload : null;
    }

    private function getContentEncoding(array $payload): ?string
    {
        $contentEncoding = $payload['contentEncoding'] ?? 'aes128gcm';

        return \is_string($contentEncoding) && '' !== $contentEncoding ? $contentEncoding : null;
    }

    private function hasValidSubscriptionShape(
        string $endpoint,
        string $publicKey,
        string $authToken,
        ?string $contentEncoding,
    ): bool {
        return mb_strlen($endpoint) <= self::MAX_ENDPOINT_LENGTH
            && \in_array($contentEncoding, self::SUPPORTED_CONTENT_ENCODINGS, true)
            && self::PUBLIC_KEY_LENGTH === $this->decodedBase64UrlLength($publicKey)
            && self::AUTH_TOKEN_LENGTH === $this->decodedBase64UrlLength($authToken);
    }

    private function decodedBase64UrlLength(string $value): ?int
    {
        if (!preg_match('/^[A-Za-z0-9_-]+={0,2}$/', $value)) {
            return null;
        }

        $remainder = \strlen($value) % 4;
        if (1 === $remainder) {
            return null;
        }

        $decoded = base64_decode(
            strtr($value, '-_', '+/') . str_repeat('=', (4 - $remainder) % 4),
            true
        );

        return false === $decoded ? null : \strlen($decoded);
    }
}
