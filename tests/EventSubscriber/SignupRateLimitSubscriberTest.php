<?php

namespace App\Tests\EventSubscriber;

use App\EventSubscriber\SignupRateLimitSubscriber;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\RateLimiter\RateLimiterFactory;
use Symfony\Component\RateLimiter\Storage\InMemoryStorage;
use Twig\Environment;

/**
 * Submissions of the legacy sign-up's first and last step are limited per address (here to 2).
 */
class SignupRateLimitSubscriberTest extends TestCase
{
    private SignupRateLimitSubscriber $subscriber;

    protected function setUp(): void
    {
        $twig = $this->createMock(Environment::class);
        $twig->method('render')->with('signup/too_many.html.twig')->willReturn('too many sign-ups');

        $this->subscriber = new SignupRateLimitSubscriber(
            new RateLimiterFactory(['id' => 'signup_ip', 'policy' => 'sliding_window', 'limit' => 2, 'interval' => '1 hour'], new InMemoryStorage()),
            $twig
        );
    }

    public function testTooManyFirstStepsFromOneAddressAreRefused(): void
    {
        self::assertNull($this->submit('signup', '10.0.6.1'), 'the first submission is processed');
        self::assertNull($this->submit('signup_1', '10.0.6.1'), 'both routes of the first step count');

        $response = $this->submit('signup', '10.0.6.1');

        self::assertNotNull($response, 'the third submission is answered by the limit');
        self::assertSame(Response::HTTP_TOO_MANY_REQUESTS, $response->getStatusCode());
        self::assertSame('too many sign-ups', $response->getContent());
        self::assertNull($this->submit('signup', '10.0.6.2'), 'other addresses can still sign up');
    }

    public function testTheLastStepCountsSoItCantBeSentAlone(): void
    {
        // the last step creates the account from all fields in its own submission
        self::assertNull($this->submit('signup_4', '10.0.8.1'));
        self::assertNull($this->submit('signup_4', '10.0.8.1'));

        $response = $this->submit('signup_4', '10.0.8.1');

        self::assertNotNull($response, 'the third submission is answered by the limit');
        self::assertSame(Response::HTTP_TOO_MANY_REQUESTS, $response->getStatusCode());
    }

    public function testOnlyFirstAndLastStepSubmissionsCount(): void
    {
        for ($i = 1; $i <= 5; ++$i) {
            self::assertNull($this->submit('signup_1', '10.0.7.1', 'GET'), 'showing the form does not count');
            self::assertNull($this->submit('signup_4', '10.0.7.1', 'GET'));
            self::assertNull($this->submit('signup_2', '10.0.7.1'), 'the steps in between do not count');
            self::assertNull($this->submit('signup_3', '10.0.7.1'));
            self::assertNull($this->submit('members_profile', '10.0.7.1'));
        }

        self::assertNull($this->submit('signup_1', '10.0.7.1'));
    }

    private function submit(string $route, string $address, string $method = 'POST'): ?Response
    {
        $request = new Request([], [], [], [], [], ['REQUEST_METHOD' => $method, 'REQUEST_URI' => '/signup/1', 'REMOTE_ADDR' => $address]);
        $request->attributes->set('_route', $route);
        $event = new RequestEvent($this->createMock(HttpKernelInterface::class), $request, HttpKernelInterface::MAIN_REQUEST);

        $this->subscriber->onKernelRequest($event);

        return $event->getResponse();
    }
}
