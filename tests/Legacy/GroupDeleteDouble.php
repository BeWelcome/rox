<?php

namespace App\Tests\Legacy;

// php-cs-fixer and PHPCS disagree on anonymous-class spacing.
// phpcs:disable PSR12.Classes.AnonClassDeclaration.SpaceAfterKeyword
// Legacy entity accessors use snake_case.
// phpcs:disable PSR1.Methods.CamelCapsMethodName

use Group;
use Throwable;

final class GroupDeleteDouble extends Group
{
    public bool $groupDeleteCalled = false;
    private readonly object $transactionDao;

    public function __construct(
        private readonly bool $roleExists = true,
        private readonly array $scopeDeleteResults = [],
        private readonly array $membershipDeleteResults = [],
    ) {
        $this->transactionDao = new class {
            public array $statements = [];

            public function exec(string $statement): bool
            {
                $this->statements[] = $statement;

                return true;
            }
        };
    }

    public function isLoaded()
    {
        return true;
    }

    public function getPKValue()
    {
        return 1;
    }

    public function delete()
    {
        $this->groupDeleteCalled = true;

        return true;
    }

    public function getTransactionStatements(): array
    {
        return $this->transactionDao->statements;
    }

    protected function createEntity()
    {
        return match (func_get_arg(0)) {
            'Role' => new class($this->roleExists) {
                public function __construct(private readonly bool $roleExists)
                {
                }

                public function findByName(string $name): object|false
                {
                    return $this->roleExists ? new class {
                        public function getPKValue(): int
                        {
                            return 2;
                        }
                    } : false;
                }
            },
            'PrivilegeScope' => $this->createCollection($this->scopeDeleteResults),
            'GroupMembership' => $this->createCollection($this->membershipDeleteResults),
        };
    }

    protected function get_dao(): object
    {
        return $this->transactionDao;
    }

    private function createCollection(array $deleteResults): object
    {
        return new class($deleteResults) {
            public function __construct(private readonly array $deleteResults)
            {
            }

            public function findByWhereMany(string $where): array
            {
                return array_map(
                    static fn (bool|Throwable $result) => new class($result) {
                        public function __construct(private readonly bool|Throwable $result)
                        {
                        }

                        public function delete(): bool
                        {
                            if ($this->result instanceof Throwable) {
                                throw $this->result;
                            }

                            return $this->result;
                        }
                    },
                    $this->deleteResults,
                );
            }
        };
    }
}
