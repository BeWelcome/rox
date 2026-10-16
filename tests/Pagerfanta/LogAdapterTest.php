<?php

namespace App\Tests\Pagerfanta;

use App\Entity\Member;
use App\Pagerfanta\LogAdapter;
use Doctrine\DBAL\ArrayParameterType;
use Doctrine\DBAL\ParameterType;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;

/**
 * The admin log filters by type and member. DBAL 4 needs parameter names without ':' and an array type for
 * IN (), otherwise the query fails and the filtered list stays empty.
 */
class LogAdapterTest extends TestCase
{
    public function testFilterParametersSuitDbal4(): void
    {
        $member = $this->createStub(Member::class);
        $member->method('getId')->willReturn(42);
        $adapter = new LogAdapter($this->createStub(EntityManagerInterface::class), ['Login', 'Signup'], $member);

        [$sql, $params, $types] = (new ReflectionMethod($adapter, 'getSqlAndParameters'))->invoke($adapter, false);

        $this->assertStringContainsString('`type` IN (:types)', $sql);
        $this->assertStringContainsString('`l`.`IdMember` = :memberId', $sql);
        $this->assertSame(['types' => ['Login', 'Signup'], 'memberId' => 42], $params);
        $this->assertSame(['types' => ArrayParameterType::STRING, 'memberId' => ParameterType::INTEGER], $types);
    }
}
