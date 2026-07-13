<?php

namespace App\Tests\Legacy;

// php-cs-fixer and PHPCS disagree on anonymous-class spacing.
// phpcs:disable PSR12.Classes.AnonClassDeclaration.SpaceAfterKeyword

use App\Utilities\SessionSingleton;
use GroupDeletePage;
use GroupsController;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;

final class GroupsControllerTest extends TestCase
{
    public function testDeleteFailureIsSurfacedWithoutLoggingOrRedirecting(): void
    {
        [$group, $model, $session, $words, $controller] = $this->createDeleteContext(false);

        $page = $controller->delete();

        self::assertTrue($model->deleteCalled);
        self::assertInstanceOf(GroupDeletePage::class, $page);
        self::assertSame($group, $page->group);
        self::assertSame('Translated group deletion error', $session->get('flash_error'));
        self::assertSame(['flash.group.delete.error'], $words->requestedCodes);
        self::assertSame([], $controller->logs);
        self::assertSame([], $controller->redirects);
    }

    public function testDeleteSuccessLogsCapturedGroupIdBeforeRedirecting(): void
    {
        [$group, $model, $session, $words, $controller] = $this->createDeleteContext(true);

        try {
            $controller->delete();
            self::fail('The successful deletion did not redirect.');
        } catch (RuntimeException $caught) {
            self::assertSame('Redirect intercepted', $caught->getMessage());
        }

        self::assertTrue($model->deleteCalled);
        self::assertNull($group->id);
        self::assertSame([['Group #1 was deleted by member #2', 'Log']], $controller->logs);
        self::assertSame([['/groups', '']], $controller->redirects);
        self::assertSame([], $words->requestedCodes);
        self::assertFalse($session->has('flash_error'));
    }

    private function createDeleteContext(bool $deleteResult): array
    {
        $group = new class {
            public ?int $id = 1;

            public function getPKValue(): ?int
            {
                return $this->id;
            }
        };
        $member = new class {
            public function getPKValue(): int
            {
                return 2;
            }
        };
        $model = new class($group, $member, $deleteResult) {
            public bool $deleteCalled = false;

            public function __construct(
                private readonly object $group,
                private readonly object $member,
                private readonly bool $deleteResult,
            ) {
            }

            public function findGroup(int $groupId): object
            {
                return $this->group;
            }

            public function canAccessGroupAdmin(object $group): bool
            {
                return true;
            }

            public function deleteGroup(object $group): bool
            {
                $this->deleteCalled = true;
                if ($this->deleteResult) {
                    $this->group->id = null;
                }

                return $this->deleteResult;
            }

            public function getLoggedInMember(): object
            {
                return $this->member;
            }
        };
        $session = new Session(new MockArraySessionStorage());
        try {
            SessionSingleton::getSession();
        } catch (InvalidArgumentException) {
            SessionSingleton::createInstance($session);
        }
        $words = new class {
            public array $requestedCodes = [];

            public function getSilent(string $code): string
            {
                $this->requestedCodes[] = $code;

                return 'Translated group deletion error';
            }
        };
        $controller = new class($model, $session, $words) extends GroupsController {
            public array $logs = [];
            public array $redirects = [];

            public function __construct(
                object $model,
                Session $session,
                private readonly object $words,
            ) {
                $this->_model = $model;
                $this->session = $session;
            }

            protected function logWrite($string, $type = 'Log'): void
            {
                $this->logs[] = [$string, $type];
            }

            protected function redirectAbsolute($url, $getArgs = ''): void
            {
                $this->redirects[] = [$url, $getArgs];

                throw new RuntimeException('Redirect intercepted');
            }

            protected function getWords(): object
            {
                return $this->words;
            }
        };
        $controller->route_vars = ['group_id' => $group->id];
        $controller->request_vars = [3 => 'true'];

        return [$group, $model, $session, $words, $controller];
    }
}
