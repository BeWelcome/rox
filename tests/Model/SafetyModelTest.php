<?php

namespace App\Tests\Model;

use App\Entity\Member;
use App\Model\SafetyModel;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\NativeQuery;
use PHPUnit\Framework\TestCase;

class SafetyModelTest extends TestCase
{
    public function testGetSafetyTeamMembersReturnsList(): void
    {
        // Loose mocking: we don't check exact SQL string or exact parameter types unless necessary.
        // We fundamentally test: "If the DB returns X, does the method return X?"

        $expectedMembers = [new Member(), new Member()];

        $query = $this->createStub(NativeQuery::class);
        $query->method('getResult')->willReturn($expectedMembers);

        $entityManager = $this->createStub(EntityManagerInterface::class);
        $entityManager->method('createNativeQuery')->willReturn($query);

        $safetyModel = new SafetyModel($entityManager);
        $result = $safetyModel->getSafetyTeamMembers();

        $this->assertCount(2, $result);
        $this->assertEquals($expectedMembers, $result);
    }
}
